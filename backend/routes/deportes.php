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
                id_deporte,
                nombre,
                duracion_estandar_min,
                color_visualizacion,
                observaciones
            FROM deporte
            ORDER BY id_deporte";

    $stmt = $conexion->query($sql);
    $deportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "deportes" => $deportes
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener los deportes"
    ]);
}