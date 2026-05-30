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
$id_usuario = $datos["id_usuario"] ?? null;
$equipos = trim($datos["equipos"] ?? "");
$resultado = trim($datos["resultado"] ?? "");
$comentario_instalacion = trim($datos["comentario_instalacion"] ?? "");
$valoracion = $datos["valoracion"] ?? null;

if (empty($id_reserva) || empty($id_usuario)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_reserva e id_usuario son obligatorios"
    ]);
    exit;
}

if ($valoracion !== null && ($valoracion < 1 || $valoracion > 5)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "La valoración debe estar entre 1 y 5"
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

    // Comprobar que la reserva existe
    $sql = "SELECT id_reserva, estado
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

    // Comprobar que el usuario pertenece a la reserva
    $sql = "SELECT id_usuario
            FROM reserva_usuario
            WHERE id_reserva = :id_reserva
            AND id_usuario = :id_usuario
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva" => $id_reserva,
        ":id_usuario" => $id_usuario
    ]);

    if (!$stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "No puedes añadir anotaciones a una reserva en la que no participas"
        ]);
        exit;
    }

    // Comprobar si la reserva ya tiene anotación
    $sql = "SELECT id_anotacion
            FROM anotacion
            WHERE id_reserva = :id_reserva
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva" => $id_reserva
    ]);

    if ($stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "Esta reserva ya tiene una anotación"
        ]);
        exit;
    }

    // Insertar anotación
    $sql = "INSERT INTO anotacion (
                id_reserva,
                equipos,
                resultado,
                comentario_instalacion,
                valoracion
            ) VALUES (
                :id_reserva,
                :equipos,
                :resultado,
                :comentario_instalacion,
                :valoracion
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva" => $id_reserva,
        ":equipos" => $equipos,
        ":resultado" => $resultado,
        ":comentario_instalacion" => $comentario_instalacion,
        ":valoracion" => $valoracion
    ]);

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Anotación creada correctamente"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al crear la anotación"
    ]);
}