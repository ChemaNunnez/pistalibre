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

$id_profesor = $datos["id_profesor"] ?? null;
$id_pista = $datos["id_pista"] ?? null;
$id_deporte = $datos["id_deporte"] ?? null;
$dia_semana = trim($datos["dia_semana"] ?? "");
$hora_inicio = $datos["hora_inicio"] ?? null;
$hora_fin = $datos["hora_fin"] ?? null;
$fecha_inicio = $datos["fecha_inicio"] ?? null;
$fecha_fin = $datos["fecha_fin"] ?? null;
$observaciones = trim($datos["observaciones"] ?? "");

if (
    empty($id_profesor) ||
    empty($id_pista) ||
    empty($id_deporte) ||
    empty($dia_semana) ||
    empty($hora_inicio) ||
    empty($hora_fin) ||
    empty($fecha_inicio) ||
    empty($fecha_fin)
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

    // Comprobar que el usuario existe y está activo
    $sql = "SELECT u.id_usuario
            FROM usuario u
            WHERE u.id_usuario = :id_profesor
            AND u.activo = 1
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_profesor" => $id_profesor
    ]);

    if (!$stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "Profesor no válido o inactivo"
        ]);
        exit;
    }

    // Comprobar que el usuario tiene rol profesor o admin
    $sql = "SELECT r.nombre
            FROM usuario_rol ur
            INNER JOIN rol r
                ON ur.id_rol = r.id_rol
            WHERE ur.id_usuario = :id_profesor
            AND r.nombre IN ('profesor', 'admin')";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_profesor" => $id_profesor
    ]);

    if (!$stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "El usuario no tiene rol de profesor o administrador"
        ]);
        exit;
    }

    // Comprobar que la pista permite ese deporte
    $sql = "SELECT id_pista
            FROM pista_deporte
            WHERE id_pista = :id_pista
            AND id_deporte = :id_deporte
            LIMIT 1";

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

    // Comprobar fechas
    if ($fecha_inicio > $fecha_fin) {
        $conexion->rollBack();
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "La fecha de inicio no puede ser posterior a la fecha de fin"
        ]);
        exit;
    }

    // Comprobar conflicto con otra reserva fija activa
    $sql = "SELECT id_reserva_fija
            FROM reserva_fija
            WHERE id_pista = :id_pista
            AND dia_semana = :dia_semana
            AND activa = 1
            AND (
                (:hora_inicio < hora_fin AND :hora_fin > hora_inicio)
            )
            AND (
                (:fecha_inicio <= fecha_fin AND :fecha_fin >= fecha_inicio)
            )
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_pista" => $id_pista,
        ":dia_semana" => $dia_semana,
        ":hora_inicio" => $hora_inicio,
        ":hora_fin" => $hora_fin,
        ":fecha_inicio" => $fecha_inicio,
        ":fecha_fin" => $fecha_fin
    ]);

    if ($stmt->fetch()) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "Ya existe una reserva fija activa en esa pista, día y horario"
        ]);
        exit;
    }

    // Crear reserva fija
    $sql = "INSERT INTO reserva_fija (
                id_profesor,
                id_pista,
                id_deporte,
                dia_semana,
                hora_inicio,
                hora_fin,
                fecha_inicio,
                fecha_fin,
                activa,
                observaciones
            ) VALUES (
                :id_profesor,
                :id_pista,
                :id_deporte,
                :dia_semana,
                :hora_inicio,
                :hora_fin,
                :fecha_inicio,
                :fecha_fin,
                1,
                :observaciones
            )";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_profesor" => $id_profesor,
        ":id_pista" => $id_pista,
        ":id_deporte" => $id_deporte,
        ":dia_semana" => $dia_semana,
        ":hora_inicio" => $hora_inicio,
        ":hora_fin" => $hora_fin,
        ":fecha_inicio" => $fecha_inicio,
        ":fecha_fin" => $fecha_fin,
        ":observaciones" => $observaciones
    ]);

    $id_reserva_fija = $conexion->lastInsertId();

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Reserva fija creada correctamente",
        "id_reserva_fija" => $id_reserva_fija
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al crear la reserva fija"
    ]);
}