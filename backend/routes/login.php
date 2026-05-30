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

$email = $datos["email"] ?? "";
$password = $datos["password"] ?? "";

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Email y contraseña son obligatorios"
    ]);
    exit;
}

try {
    $sql = "SELECT id_usuario, nombre, apellidos, alias, email, password_hash, activo
            FROM usuario
            WHERE email = :email
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "Credenciales incorrectas"
        ]);
        exit;
    }

    if (!password_verify($password, $usuario["password_hash"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "Credenciales incorrectas"
        ]);
        exit;
    }

    if (!$usuario["activo"]) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "Usuario desactivado"
        ]);
        exit;
    }

    $sql = "SELECT r.nombre
            FROM usuario_rol ur
            INNER JOIN rol r
                ON ur.id_rol = r.id_rol
            WHERE ur.id_usuario = :id_usuario";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_usuario" => $usuario["id_usuario"]
    ]);

    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    unset($usuario["password_hash"]);

    $usuario["roles"] = $roles;

    echo json_encode([
        "success" => true,
        "mensaje" => "Login correcto",
        "usuario" => $usuario
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error en el servidor"
    ]);
}