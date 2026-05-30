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
                u.id_usuario,
                u.alias,
                u.nombre,
                u.apellidos,
                u.email
            FROM usuario u
            INNER JOIN usuario_rol ur
                ON u.id_usuario = ur.id_usuario
            INNER JOIN rol r
                ON ur.id_rol = r.id_rol
            WHERE r.nombre IN ('profesor', 'admin')
            AND u.activo = 1
            ORDER BY u.alias";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "profesores" => $profesores
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener los profesores"
    ]);
}