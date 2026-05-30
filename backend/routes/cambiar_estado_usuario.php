<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Método no permitido"
    ]);
    exit;
}

$datos = json_decode(file_get_contents("php://input"), true);

$id_usuario = $datos["id_usuario"] ?? null;
$activo = $datos["activo"] ?? null;

if ($id_usuario === null || $activo === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_usuario y activo son obligatorios"
    ]);
    exit;
}

try {
    $sql = "UPDATE usuario
            SET activo = :activo
            WHERE id_usuario = :id_usuario";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":activo" => $activo,
        ":id_usuario" => $id_usuario
    ]);

    echo json_encode([
        "success" => true,
        "mensaje" => "Estado del usuario actualizado correctamente"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al actualizar el estado del usuario"
    ]);
}