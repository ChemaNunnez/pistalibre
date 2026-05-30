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

$nombre = trim($datos["nombre"] ?? "");
$apellidos = trim($datos["apellidos"] ?? "");
$alias = trim($datos["alias"] ?? "");
$email = trim($datos["email"] ?? "");
$password = trim($datos["password"] ?? "");

if (
    empty($nombre) ||
    empty($apellidos) ||
    empty($alias) ||
    empty($email) ||
    empty($password)
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Todos los campos son obligatorios"
    ]);
    exit;
}

try {
    $sql = "SELECT id_usuario FROM usuario WHERE email = :email OR alias = :alias LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":alias", $alias);
    $stmt->execute();

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "El email o alias ya están registrados"
        ]);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $conexion->beginTransaction();

    $sql = "INSERT INTO usuario (
                nombre,
                apellidos,
                alias,
                email,
                password_hash,
                activo
            ) VALUES (
                :nombre,
                :apellidos,
                :alias,
                :email,
                :password_hash,
                1
            )";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":nombre", $nombre);
    $stmt->bindParam(":apellidos", $apellidos);
    $stmt->bindParam(":alias", $alias);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password_hash", $passwordHash);

    $stmt->execute();

    $id_usuario = $conexion->lastInsertId();

    $sql = "INSERT INTO usuario_rol (id_usuario, id_rol)
            VALUES (:id_usuario, 1)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_usuario" => $id_usuario
    ]);

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Usuario registrado correctamente"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error en el servidor"
    ]);
}