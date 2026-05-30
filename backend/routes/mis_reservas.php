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

$id_usuario = $_GET["id_usuario"] ?? null;

if (empty($id_usuario)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_usuario es obligatorio"
    ]);
    exit;
}

try {
    $sql = "SELECT 
                r.id_reserva,
                r.fecha,
                r.hora_inicio,
                r.hora_fin,
                r.estado,
                r.tipo,
                p.nombre AS pista,
                c.nombre AS centro,
                d.nombre AS deporte,
                u.alias AS reservador,
                GROUP_CONCAT(
                    CASE 
                        WHEN ru.es_reservador = 0 THEN ua.alias
                    END
                    SEPARATOR ', '
                ) AS acompanantes,
                a.equipos,
                a.resultado,
                a.comentario_instalacion,
                a.valoracion
            FROM reserva r
            INNER JOIN pista p
                ON r.id_pista = p.id_pista
            INNER JOIN centro_deportivo c
                ON p.id_centro = c.id_centro
            INNER JOIN deporte d
                ON r.id_deporte = d.id_deporte
            INNER JOIN usuario u
                ON r.id_usuario = u.id_usuario
            INNER JOIN reserva_usuario ru_filtro
                ON r.id_reserva = ru_filtro.id_reserva
            LEFT JOIN reserva_usuario ru
                ON r.id_reserva = ru.id_reserva
            LEFT JOIN usuario ua
                ON ru.id_usuario = ua.id_usuario
            LEFT JOIN anotacion a
                ON r.id_reserva = a.id_reserva
            WHERE ru_filtro.id_usuario = :id_usuario
            GROUP BY 
                r.id_reserva,
                r.fecha,
                r.hora_inicio,
                r.hora_fin,
                r.estado,
                r.tipo,
                p.nombre,
                c.nombre,
                d.nombre,
                u.alias,
                a.equipos,
                a.resultado,
                a.comentario_instalacion,
                a.valoracion
            ORDER BY r.fecha DESC, r.hora_inicio DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_usuario" => $id_usuario
    ]);

    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "reservas" => $reservas
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener las reservas"
    ]);
}