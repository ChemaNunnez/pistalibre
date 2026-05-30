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
$id_pista = $datos["id_pista"] ?? null;
$id_deporte = $datos["id_deporte"] ?? null;
$fecha = $datos["fecha"] ?? null;
$hora_inicio = $datos["hora_inicio"] ?? null;
$hora_fin = $datos["hora_fin"] ?? null;

if (
    empty($id_usuario) ||
    empty($id_pista) ||
    empty($id_deporte) ||
    empty($fecha) ||
    empty($hora_inicio) ||
    empty($hora_fin)
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Todos los campos son obligatorios"
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

    // Comprobar que el usuario existe y está activo
    $sql = "SELECT id_usuario FROM usuario 
            WHERE id_usuario = :id_usuario AND activo = 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([":id_usuario" => $id_usuario]);

    if (!$stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "Usuario no válido o inactivo"
        ]);
        exit;
    }

    // Comprobar que la pista permite ese deporte
    $sql = "SELECT id_pista 
            FROM pista_deporte
            WHERE id_pista = :id_pista
            AND id_deporte = :id_deporte";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista,
        ":id_deporte" => $id_deporte
    ]);

    if (!$stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "La pista no permite ese deporte"
        ]);
        exit;
    }

    // Comprobar si ya existe una reserva activa en ese tramo
    $sql = "SELECT id_reserva
            FROM reserva
            WHERE id_pista = :id_pista
            AND fecha = :fecha
            AND hora_inicio = :hora_inicio
            AND hora_fin = :hora_fin
            AND estado = 'activa'";

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
            "error" => "El tramo horario ya está reservado"
        ]);
        exit;
    }

    // Evitar que el mismo usuario reserve la misma pista en el tramo inmediatamente anterior o posterior
    $sql = "SELECT id_reserva
            FROM reserva
            WHERE id_usuario = :id_usuario
            AND id_pista = :id_pista
            AND fecha = :fecha
            AND estado = 'activa'
            AND (
                hora_fin = :hora_inicio
                OR hora_inicio = :hora_fin
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_usuario" => $id_usuario,
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
            "error" => "No puedes reservar la misma pista en una franja consecutiva"
        ]);
        exit;
    }

    // Crear reserva
    $sql = "INSERT INTO reserva (
                id_usuario,
                id_pista,
                id_deporte,
                fecha,
                hora_inicio,
                hora_fin,
                estado,
                tipo
            ) VALUES (
                :id_usuario,
                :id_pista,
                :id_deporte,
                :fecha,
                :hora_inicio,
                :hora_fin,
                'activa',
                'normal'
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_usuario" => $id_usuario,
        ":id_pista" => $id_pista,
        ":id_deporte" => $id_deporte,
        ":fecha" => $fecha,
        ":hora_inicio" => $hora_inicio,
        ":hora_fin" => $hora_fin
    ]);

    $id_reserva = $conexion->lastInsertId();

    // Añadir al usuario como reservador en reserva_usuario
    $sql = "INSERT INTO reserva_usuario (
                id_reserva,
                id_usuario,
                es_reservador
            ) VALUES (
                :id_reserva,
                :id_usuario,
                1
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_reserva" => $id_reserva,
        ":id_usuario" => $id_usuario
    ]);

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Reserva creada correctamente",
        "id_reserva" => $id_reserva
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al crear la reserva"
    ]);
}