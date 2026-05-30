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
                id_centro,
                nombre,
                direccion,
                ciudad,
                telefono,
                email,
                hora_apertura,
                hora_cierre,
                abierto_fines_semana,
                observaciones
            FROM centro_deportivo
            ORDER BY id_centro";

    $stmt = $conexion->query($sql);
    $centros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "centros" => $centros
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener los centros deportivos"
    ]);
}