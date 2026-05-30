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

$id_reserva_fija = $datos["id_reserva_fija"] ?? null;
$id_profesor = $datos["id_profesor"] ?? null;
$fecha = $datos["fecha"] ?? null;
$motivo = trim($datos["motivo"] ?? "");

if (empty($id_reserva_fija) || empty($id_profesor) || empty($fecha)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_reserva_fija, id_profesor y fecha son obligatorios"
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

    $sql = "SELECT id_reserva_fija, id_profesor, activa, fecha_inicio, fecha_fin
            FROM reserva_fija
            WHERE id_reserva_fija = :id_reserva_fija
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija
    ]);

    $reservaFija = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservaFija) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "La reserva fija no existe"
        ]);
        exit;
    }

    if ($reservaFija["id_profesor"] != $id_profesor) {
        $conexion->rollBack();
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "No tienes permiso para liberar esta reserva fija"
        ]);
        exit;
    }

    if (!$reservaFija["activa"]) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "La reserva fija no está activa"
        ]);
        exit;
    }

    if ($fecha < $reservaFija["fecha_inicio"] || $fecha > $reservaFija["fecha_fin"]) {
        $conexion->rollBack();
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "La fecha no pertenece al periodo de la reserva fija"
        ]);
        exit;
    }

    $sql = "SELECT id_excepcion
            FROM excepcion_reserva_fija
            WHERE id_reserva_fija = :id_reserva_fija
            AND fecha = :fecha
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija,
        ":fecha" => $fecha
    ]);

    if ($stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "Esta fecha ya tiene una excepción registrada"
        ]);
        exit;
    }

    $sql = "INSERT INTO excepcion_reserva_fija (
                id_reserva_fija,
                fecha,
                motivo,
                liberada
            ) VALUES (
                :id_reserva_fija,
                :fecha,
                :motivo,
                1
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija,
        ":fecha" => $fecha,
        ":motivo" => $motivo
    ]);

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Reserva fija liberada correctamente"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al liberar la reserva fija"
    ]);
}