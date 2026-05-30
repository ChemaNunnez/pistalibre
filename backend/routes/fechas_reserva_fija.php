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

$id_reserva_fija = $_GET["id_reserva_fija"] ?? null;

if (empty($id_reserva_fija)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_reserva_fija obligatorio"
    ]);
    exit;
}

try {
    $sql = "SELECT 
                id_reserva_fija,
                dia_semana,
                fecha_inicio,
                fecha_fin,
                hora_inicio,
                hora_fin
            FROM reserva_fija
            WHERE id_reserva_fija = :id_reserva_fija
            AND activa = 1
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija
    ]);

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "Reserva fija no encontrada"
        ]);
        exit;
    }

    $dias = [
        "Lunes" => 1,
        "Martes" => 2,
        "Miercoles" => 3,
        "Jueves" => 4,
        "Viernes" => 5,
        "Sabado" => 6,
        "Domingo" => 7
    ];

    $diaObjetivo = $dias[$reserva["dia_semana"]];

    $inicio = new DateTime($reserva["fecha_inicio"]);
    $fin = new DateTime($reserva["fecha_fin"]);

    $fechas = [];

    while ($inicio <= $fin) {
        if ((int)$inicio->format("N") === $diaObjetivo) {
            $fecha = $inicio->format("Y-m-d");

            $sql = "SELECT id_excepcion
                    FROM excepcion_reserva_fija
                    WHERE id_reserva_fija = :id_reserva_fija
                    AND fecha = :fecha
                    AND liberada = 1
                    LIMIT 1";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ":id_reserva_fija" => $id_reserva_fija,
                ":fecha" => $fecha
            ]);

            if (!$stmt->fetch()) {
                $fechas[] = [
                    "tipo" => "normal",
                    "id_reserva_extra" => null,
                    "fecha" => $fecha,
                    "hora_inicio" => $reserva["hora_inicio"],
                    "hora_fin" => $reserva["hora_fin"]
                ];
            }
        }

        $inicio->modify("+1 day");
    }

    $sql = "SELECT
                id_reserva_extra,
                fecha,
                hora_inicio,
                hora_fin
            FROM reserva_extra_clase
            WHERE id_reserva_fija = :id_reserva_fija
            AND activa = 1
            ORDER BY fecha, hora_inicio";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija
    ]);

    $reservasExtra = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($reservasExtra as $extra) {
        $fechas[] = [
            "tipo" => "recuperacion",
            "id_reserva_extra" => $extra["id_reserva_extra"],
            "fecha" => $extra["fecha"],
            "hora_inicio" => $extra["hora_inicio"],
            "hora_fin" => $extra["hora_fin"]
        ];
    }

    usort($fechas, function ($a, $b) {
        $fechaHoraA = $a["fecha"] . " " . $a["hora_inicio"];
        $fechaHoraB = $b["fecha"] . " " . $b["hora_inicio"];

        return strcmp($fechaHoraA, $fechaHoraB);
    });

    echo json_encode([
        "success" => true,
        "fechas" => $fechas
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al obtener fechas"
    ]);
}