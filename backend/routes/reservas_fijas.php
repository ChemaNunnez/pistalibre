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

try {

    $sql = "SELECT
            rf.id_reserva_fija,
            rf.id_profesor,
            rf.id_pista,
            rf.id_deporte,
            p.id_centro,

            u.nombre,
            u.apellidos,
            u.alias,

            p.nombre AS pista,

            d.nombre AS deporte,

            rf.dia_semana,
            rf.hora_inicio,
            rf.hora_fin,

            rf.fecha_inicio,
            rf.fecha_fin,

            rf.activa,

            rf.observaciones

            FROM reserva_fija rf

            INNER JOIN usuario u
                ON rf.id_profesor = u.id_usuario

            INNER JOIN pista p
                ON rf.id_pista = p.id_pista

            INNER JOIN deporte d
                ON rf.id_deporte = d.id_deporte

            ORDER BY
                rf.dia_semana,
                rf.hora_inicio";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "reservas_fijas" => $reservas
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener reservas fijas"
    ]);
}