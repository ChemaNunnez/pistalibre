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
$id_pista = $datos["id_pista"] ?? null;
$fecha = $datos["fecha"] ?? null;
$hora_inicio = $datos["hora_inicio"] ?? null;
$hora_fin = $datos["hora_fin"] ?? null;
$observaciones = trim($datos["observaciones"] ?? "");

if (
    empty($id_reserva_fija) ||
    empty($id_profesor) ||
    empty($id_pista) ||
    empty($fecha) ||
    empty($hora_inicio) ||
    empty($hora_fin)
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Todos los campos obligatorios deben estar completos"
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

    // Comprobar que la reserva fija existe y pertenece al profesor
    $sql = "SELECT id_reserva_fija, id_profesor, activa
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
            "error" => "No tienes permiso para crear una reserva extra para esta reserva fija"
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

    // Comprobar que la pista existe
    $sql = "SELECT id_pista
            FROM pista
            WHERE id_pista = :id_pista
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista
    ]);

    if (!$stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "La pista no existe"
        ]);
        exit;
    }

    // Comprobar conflicto con reserva normal activa
    $sql = "SELECT id_reserva
            FROM reserva
            WHERE id_pista = :id_pista
            AND fecha = :fecha
            AND estado = 'activa'
            AND (:hora_inicio < hora_fin AND :hora_fin > hora_inicio)
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista,
        ":fecha" => $fecha,
        ":hora_inicio" => $hora_inicio,
        ":hora_fin" => $hora_fin
    ]);

    if ($stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "Ya existe una reserva normal en ese horario"
        ]);
        exit;
    }

    // Comprobar conflicto con otra reserva extra
    $sql = "SELECT id_reserva_extra
            FROM reserva_extra_clase
            WHERE id_pista = :id_pista
            AND fecha = :fecha
            AND (:hora_inicio < hora_fin AND :hora_fin > hora_inicio)
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista,
        ":fecha" => $fecha,
        ":hora_inicio" => $hora_inicio,
        ":hora_fin" => $hora_fin
    ]);

    if ($stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "Ya existe una reserva extra en ese horario"
        ]);
        exit;
    }

    // Crear reserva extra
    $sql = "INSERT INTO reserva_extra_clase (
                id_reserva_fija,
                id_pista,
                fecha,
                hora_inicio,
                hora_fin,
                observaciones
            ) VALUES (
                :id_reserva_fija,
                :id_pista,
                :fecha,
                :hora_inicio,
                :hora_fin,
                :observaciones
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva_fija" => $id_reserva_fija,
        ":id_pista" => $id_pista,
        ":fecha" => $fecha,
        ":hora_inicio" => $hora_inicio,
        ":hora_fin" => $hora_fin,
        ":observaciones" => $observaciones
    ]);

    $id_reserva_extra = $conexion->lastInsertId();

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Reserva extra de clase creada correctamente",
        "id_reserva_extra" => $id_reserva_extra
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al crear la reserva extra"
    ]);
}