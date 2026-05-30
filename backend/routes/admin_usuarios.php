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
                u.nombre,
                u.apellidos,
                u.alias,
                u.email,
                u.activo,
                GROUP_CONCAT(r.nombre SEPARATOR ', ') AS roles
            FROM usuario u
            LEFT JOIN usuario_rol ur
                ON u.id_usuario = ur.id_usuario
            LEFT JOIN rol r
                ON ur.id_rol = r.id_rol
            GROUP BY
                u.id_usuario,
                u.nombre,
                u.apellidos,
                u.alias,
                u.email,
                u.activo
            ORDER BY u.id_usuario";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "usuarios" => $usuarios
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al obtener usuarios"
    ]);
}