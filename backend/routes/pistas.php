<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Método no permitido"
    ]);
    exit;
}

$id_centro = $_GET["id_centro"] ?? null;
$id_deporte = $_GET["id_deporte"] ?? null;

try {
    $sql = "SELECT
                p.id_pista,
                p.nombre AS nombre_pista,
                p.cubierta,
                p.iluminacion,
                p.estado,
                c.id_centro,
                c.nombre AS centro,
                GROUP_CONCAT(d.nombre SEPARATOR ', ') AS deportes
            FROM pista p
            INNER JOIN centro_deportivo c
                ON p.id_centro = c.id_centro
            INNER JOIN pista_deporte pd
                ON p.id_pista = pd.id_pista
            INNER JOIN deporte d
                ON pd.id_deporte = d.id_deporte
            WHERE 1 = 1";

    $params = [];

    if (!empty($id_centro)) {
        $sql .= " AND c.id_centro = :id_centro";
        $params[":id_centro"] = $id_centro;
    }

    if (!empty($id_deporte)) {
        $sql .= " AND d.id_deporte = :id_deporte";
        $params[":id_deporte"] = $id_deporte;
    }

    $sql .= " GROUP BY
                p.id_pista,
                p.nombre,
                p.cubierta,
                p.iluminacion,
                p.estado,
                c.id_centro,
                c.nombre
              ORDER BY p.id_pista";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);

    $pistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "pistas" => $pistas
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error al obtener las pistas"
    ]);
}