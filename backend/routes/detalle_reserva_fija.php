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

    /* DATOS DE LA RESERVA FIJA */

    $sql = "SELECT
                rf.*,

                u.nombre,
                u.apellidos,
                u.alias,

                p.nombre AS pista,

                d.nombre AS deporte

            FROM reserva_fija rf

            INNER JOIN usuario u
                ON rf.id_profesor = u.id_usuario

            INNER JOIN pista p
                ON rf.id_pista = p.id_pista

            INNER JOIN deporte d
                ON rf.id_deporte = d.id_deporte

            WHERE rf.id_reserva_fija = :id_reserva_fija

            LIMIT 1";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija
    ]);

    $reservaFija = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservaFija) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "error" => "Reserva fija no encontrada"
        ]);

        exit;
    }

    /* EXCEPCIONES */

    $sql = "SELECT
                fecha,
                motivo,
                liberada

            FROM excepcion_reserva_fija

            WHERE id_reserva_fija = :id_reserva_fija

            ORDER BY fecha";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija
    ]);

    $excepciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* RESERVAS EXTRA */

    $sql = "SELECT
                rec.id_reserva_extra,

                rec.fecha,
                rec.hora_inicio,
                rec.hora_fin,

                rec.observaciones,

                p.nombre AS pista

            FROM reserva_extra_clase rec

            INNER JOIN pista p
                ON rec.id_pista = p.id_pista

            WHERE rec.id_reserva_fija = :id_reserva_fija

            ORDER BY rec.fecha,
                     rec.hora_inicio";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija
    ]);

    $reservasExtra = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "reserva_fija" => $reservaFija,
        "excepciones" => $excepciones,
        "reservas_extra" => $reservasExtra
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener detalle de reserva fija"
    ]);
}