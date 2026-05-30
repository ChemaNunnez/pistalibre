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

$id_reserva_extra = $datos["id_reserva_extra"] ?? null;

if (empty($id_reserva_extra)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_reserva_extra es obligatorio"
    ]);
    exit;
}

try {
    $sql = "UPDATE reserva_extra_clase
            SET activa = 0
            WHERE id_reserva_extra = :id_reserva_extra";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva_extra" => $id_reserva_extra
    ]);

    echo json_encode([
        "success" => true,
        "mensaje" => "Recuperación cancelada correctamente"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al cancelar la recuperación"
    ]);
}