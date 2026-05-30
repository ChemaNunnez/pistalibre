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

if (empty($id_reserva) || empty($id_usuario)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_reserva e id_usuario son obligatorios"
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

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

    if ($reserva["id_usuario"] != $id_usuario) {
        $conexion->rollBack();
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "No tienes permiso para cancelar esta reserva"
        ]);
        exit;
    }

    if ($reserva["estado"] !== "activa") {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "La reserva no está activa"
        ]);
        exit;
    }

    $sql = "UPDATE reserva
            SET estado = 'cancelada'
            WHERE id_reserva = :id_reserva";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva" => $id_reserva
    ]);

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Reserva cancelada correctamente"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al cancelar la reserva"
    ]);
}