<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── Cargar clase Turso ───────────────────────────────────────────────────
require_once __DIR__ . '/../../turso.php';

try {
    $db = new TursoDB(TURSO_URL, TURSO_TOKEN);
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => 'No se pudo conectar a Turso: ' . $e->getMessage()]));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {

    // ═══════════════════════════════════════════════════════════════════════════
    // OBTENER TODOS LOS TEXTOS CONFIGURABLES
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'get_config') {
        $stmt    = $db->query("SELECT clave, valor FROM configuracion_texto ORDER BY clave");
        $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Convertir a { clave: valor } igual que PDO::FETCH_KEY_PAIR
        $configs = [];
        foreach ($rows as $row) {
            $configs[$row['clave']] = $row['valor'];
        }
        echo json_encode($configs ?: []);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // OBTENER UN TEXTO ESPECÍFICO
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'get_config_single' && isset($_GET['clave'])) {
        $clave = $_GET['clave'];
        $stmt  = $db->prepare("SELECT valor FROM configuracion_texto WHERE clave = ?");
        $stmt->execute([$clave]);
        $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        $valor = $row ? $row['valor'] : '';
        echo json_encode(['valor' => $valor]);
        exit;
    }

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // ═══════════════════════════════════════════════════════════════════════════
    // GUARDAR / ACTUALIZAR TEXTO CONFIGURABLE
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'save_config' && isset($input['clave'], $input['valor'])) {
        $clave = trim($input['clave']);
        $valor = trim($input['valor']);

        try {
            // Intentar actualizar primero
            $stmt = $db->prepare("
                UPDATE configuracion_texto 
                SET valor = ?, ultima_modificacion = CURRENT_TIMESTAMP
                WHERE clave = ?
            ");
            $stmt->execute([$valor, $clave]);

            // Si no existía, insertar
            if ($stmt->rowCount() === 0) {
                $stmt = $db->prepare("
                    INSERT INTO configuracion_texto (clave, valor, ultima_modificacion)
                    VALUES (?, ?, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([$clave, $valor]);
            }

            echo json_encode(['success' => true, 'message' => 'Guardado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida']);
