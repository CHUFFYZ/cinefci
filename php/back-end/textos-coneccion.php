<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = new PDO('sqlite:../../db/peliculas.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'No se pudo conectar a la base de datos: ' . $e->getMessage()]));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    // ═══════════════════════════════════════════════════════════════════════════
    // OBTENER TODOS LOS TEXTOS CONFIGURABLES (para admin o frontend)
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'get_config') {
        $stmt = $db->query("
            SELECT clave, valor 
            FROM configuracion_texto 
            ORDER BY clave
        ");
        $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // clave => valor
        echo json_encode($configs ?: []);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // OBTENER UN TEXTO ESPECÍFICO (útil para index sin cargar todo)
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'get_config_single' && isset($_GET['clave'])) {
        $clave = $_GET['clave'];
        $stmt = $db->prepare("SELECT valor FROM configuracion_texto WHERE clave = ?");
        $stmt->execute([$clave]);
        $valor = $stmt->fetchColumn() ?: '';
        echo json_encode(['valor' => $valor]);
        exit;
    }

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // ────────────────────────────────────────────────
    // GUARDAR / ACTUALIZAR TEXTO CONFIGURABLE
    // ────────────────────────────────────────────────
    if ($action === 'save_config' && isset($input['clave'], $input['valor'])) {
        $clave = trim($input['clave']);
        $valor = trim($input['valor']);

        $stmt = $db->prepare("
            INSERT INTO configuracion_texto (clave, valor, ultima_modificacion)
            VALUES (:clave, :valor, CURRENT_TIMESTAMP)
            ON CONFLICT(clave) DO UPDATE SET 
                valor = excluded.valor,
                ultima_modificacion = CURRENT_TIMESTAMP
        ");
        $success = $stmt->execute([
            ':clave' => $clave,
            ':valor' => $valor
        ]);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Guardado correctamente' : 'Error al guardar'
        ]);
        exit;
    }
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida']);
