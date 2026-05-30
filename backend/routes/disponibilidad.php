<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Método no permitido"
    ]);
    exit;
}

$id_pista = $_GET["id_pista"] ?? null;
$fecha = $_GET["fecha"] ?? null;

if (empty($id_pista) || empty($fecha)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_pista y fecha son obligatorios"
    ]);
    exit;
}

try {
    $diasSemana = [
        "Monday" => "Lunes",
        "Tuesday" => "Martes",
        "Wednesday" => "Miercoles",
        "Thursday" => "Jueves",
        "Friday" => "Viernes",
        "Saturday" => "Sabado",
        "Sunday" => "Domingo"
    ];

    $diaIngles = date("l", strtotime($fecha));
    $diaSemana = $diasSemana[$diaIngles];

    // Obtener centro de la pista
    $sql = "SELECT id_centro 
            FROM pista 
            WHERE id_pista = :id_pista 
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista
    ]);

    $pista = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pista) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "La pista no existe"
        ]);
        exit;
    }

    // Obtener plantilla horaria del centro para ese día
    $sql = "SELECT id_plantilla, cerrado
            FROM plantilla_horaria
            WHERE id_centro = :id_centro
            AND dia_semana = :dia_semana
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_centro" => $pista["id_centro"],
        ":dia_semana" => $diaSemana
    ]);

    $plantilla = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plantilla || $plantilla["cerrado"]) {
        echo json_encode([
            "success" => true,
            "fecha" => $fecha,
            "dia_semana" => $diaSemana,
            "cerrado" => true,
            "tramos" => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Obtener tramos horarios del día
    $sql = "SELECT 
                id_tramo,
                hora_inicio,
                hora_fin
            FROM tramo_horario
            WHERE id_plantilla = :id_plantilla
            ORDER BY hora_inicio";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_plantilla" => $plantilla["id_plantilla"]
    ]);

    $tramos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reservas normales activas
    $sql = "SELECT 
                r.id_reserva,
                r.id_usuario,
                r.hora_inicio,
                r.hora_fin,
                u.alias AS reservado_por
            FROM reserva r
            INNER JOIN usuario u
                ON r.id_usuario = u.id_usuario
            WHERE r.id_pista = :id_pista
            AND r.fecha = :fecha
            AND r.estado = 'activa'";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista,
        ":fecha" => $fecha
    ]);

    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reservas fijas activas para ese día y fecha
    $sql = "SELECT 
                rf.id_reserva_fija,
                rf.id_profesor,
                rf.hora_inicio,
                rf.hora_fin,
                u.alias AS profesor
            FROM reserva_fija rf
            INNER JOIN usuario u
                ON rf.id_profesor = u.id_usuario
            WHERE rf.id_pista = :id_pista
            AND rf.dia_semana = :dia_semana
            AND rf.activa = 1
            AND :fecha BETWEEN rf.fecha_inicio AND rf.fecha_fin
            AND NOT EXISTS (
                SELECT 1
                FROM excepcion_reserva_fija erf
                WHERE erf.id_reserva_fija = rf.id_reserva_fija
                AND erf.fecha = :fecha
                AND erf.liberada = 1
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista,
        ":dia_semana" => $diaSemana,
        ":fecha" => $fecha
    ]);

    $reservasFijas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reservas extra de clase
    $sql = "SELECT 
                rec.id_reserva_extra,
                rec.hora_inicio,
                rec.hora_fin,
                rf.id_profesor,
                u.alias AS profesor
            FROM reserva_extra_clase rec
            INNER JOIN reserva_fija rf
                ON rec.id_reserva_fija = rf.id_reserva_fija
            INNER JOIN usuario u
                ON rf.id_profesor = u.id_usuario
            WHERE rec.id_pista = :id_pista
            AND rec.fecha = :fecha
            AND rec.activa = 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista,
        ":fecha" => $fecha
    ]);

    $reservasExtra = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tramos as &$tramo) {
        $tramo["estado"] = "libre";
        $tramo["tipo_ocupacion"] = null;
        $tramo["ocupado_por"] = null;
        $tramo["id_reserva"] = null;
        $tramo["id_reserva_fija"] = null;
        $tramo["id_usuario_reservador"] = null;
        $tramo["id_profesor"] = null;
        $tramo["id_reserva_extra"] = null;

        foreach ($reservas as $reserva) {
            if (
                $tramo["hora_inicio"] == $reserva["hora_inicio"] &&
                $tramo["hora_fin"] == $reserva["hora_fin"]
            ) {
                $tramo["estado"] = "reservado";
                $tramo["tipo_ocupacion"] = "reserva_normal";
                $tramo["ocupado_por"] = $reserva["reservado_por"];
                $tramo["id_reserva"] = $reserva["id_reserva"];
                $tramo["id_usuario_reservador"] = $reserva["id_usuario"];
                break;
            }
        }

        if ($tramo["estado"] === "libre") {
            foreach ($reservasFijas as $reservaFija) {
                if (
                    $tramo["hora_inicio"] == $reservaFija["hora_inicio"] &&
                    $tramo["hora_fin"] == $reservaFija["hora_fin"]
                ) {
                    $tramo["estado"] = "reservado";
                    $tramo["tipo_ocupacion"] = "reserva_fija";
                    $tramo["ocupado_por"] = $reservaFija["profesor"];
                    $tramo["id_reserva_fija"] = $reservaFija["id_reserva_fija"];
                    $tramo["id_profesor"] = $reservaFija["id_profesor"];
                    break;
                }
            }
        }

        if ($tramo["estado"] === "libre") {
            foreach ($reservasExtra as $reservaExtra) {
                if (
                    $tramo["hora_inicio"] == $reservaExtra["hora_inicio"] &&
                    $tramo["hora_fin"] == $reservaExtra["hora_fin"]
                ) {
                    $tramo["estado"] = "reservado";
                    $tramo["tipo_ocupacion"] = "reserva_extra_clase";
                    $tramo["ocupado_por"] = $reservaExtra["profesor"];
                    $tramo["id_profesor"] = $reservaExtra["id_profesor"];
                    $tramo["id_reserva_extra"] = $reservaExtra["id_reserva_extra"];
                    break;
                }
            }
        }
    }

    echo json_encode([
        "success" => true,
        "fecha" => $fecha,
        "dia_semana" => $diaSemana,
        "cerrado" => false,
        "tramos" => $tramos
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener disponibilidad"
    ]);
}