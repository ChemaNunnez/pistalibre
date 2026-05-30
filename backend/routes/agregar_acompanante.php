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

$id_reserva = $datos["id_reserva"] ?? null;
$id_usuario_reservador = $datos["id_usuario_reservador"] ?? null;
$alias_acompanante = trim($datos["alias_acompanante"] ?? "");

if (empty($id_reserva) || empty($id_usuario_reservador) || empty($alias_acompanante)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_reserva, id_usuario_reservador y alias_acompanante son obligatorios"
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

    // Comprobar que la reserva existe, está activa y pertenece al reservador indicado
    $sql = "SELECT id_reserva, id_usuario, estado
            FROM reserva
            WHERE id_reserva = :id_reserva
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva" => $id_reserva
    ]);

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "La reserva no existe"
        ]);
        exit;
    }

    if ($reserva["estado"] !== "activa") {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "Solo se pueden añadir acompañantes a reservas activas"
        ]);
        exit;
    }

    if ($reserva["id_usuario"] != $id_usuario_reservador) {
        $conexion->rollBack();
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "No tienes permiso para modificar esta reserva"
        ]);
        exit;
    }

    // Buscar acompañante por alias
    $sql = "SELECT id_usuario, alias, activo
            FROM usuario
            WHERE alias = :alias
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":alias" => $alias_acompanante
    ]);

    $acompanante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$acompanante) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "No existe ningún usuario con ese alias"
        ]);
        exit;
    }

    if (!$acompanante["activo"]) {
        $conexion->rollBack();
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "El usuario acompañante está desactivado"
        ]);
        exit;
    }

    if ($acompanante["id_usuario"] == $id_usuario_reservador) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "El reservador ya forma parte de la reserva"
        ]);
        exit;
    }

    // Comprobar que no está ya añadido
    $sql = "SELECT id_usuario
            FROM reserva_usuario
            WHERE id_reserva = :id_reserva
            AND id_usuario = :id_usuario
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva" => $id_reserva,
        ":id_usuario" => $acompanante["id_usuario"]
    ]);

    if ($stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "Ese usuario ya está añadido a la reserva"
        ]);
        exit;
    }

    // Insertar acompañante
    $sql = "INSERT INTO reserva_usuario (
                id_reserva,
                id_usuario,
                es_reservador
            ) VALUES (
                :id_reserva,
                :id_usuario,
                0
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva" => $id_reserva,
        ":id_usuario" => $acompanante["id_usuario"]
    ]);

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Acompañante añadido correctamente",
        "acompanante" => [
            "id_usuario" => $acompanante["id_usuario"],
            "alias" => $acompanante["alias"]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al añadir acompañante"
    ]);
}