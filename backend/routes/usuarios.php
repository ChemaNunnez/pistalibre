<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/conexion.php";

try {
    $sql = "SELECT id_usuario, nombre, apellidos, alias, email, fecha_registro, activo FROM usuario";

    $stmt = $conexion->query($sql);

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "usuarios" => $usuarios
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener los usuarios"
    ]);
}