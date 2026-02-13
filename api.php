<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');  // ← importante para pruebas locales
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = new PDO('sqlite:db/peliculas.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'No se pudo conectar a la base de datos: ' . $e->getMessage()]));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {

    if ($action === 'list') {
        $browser_id = $_GET['browser_id'] ?? '';

        $sql = "
            SELECT 
                p.*,
                -- Contamos calificaciones de la tabla calificaciones
                (SELECT COUNT(*) FROM calificaciones WHERE pelicula_id = p.id) AS total_calificaciones,
                (SELECT ROUND(AVG(calificacion), 1) FROM calificaciones WHERE pelicula_id = p.id) AS promedio,
                -- Verificamos si existe el voto en la tabla votos
                (SELECT 1 FROM votos WHERE pelicula_id = p.id AND browser_id = :bid LIMIT 1) AS ya_voto,
                -- Obtenemos la calificación del usuario si existe
                (SELECT calificacion FROM calificaciones WHERE pelicula_id = p.id AND browser_id = :bid LIMIT 1) AS user_rating
            FROM peliculas p
            ORDER BY p.id
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([':bid' => $browser_id]);
        $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($peliculas);
        exit;
    }

    if ($action === 'stats') {
    // Consultamos la tabla peliculas y contamos cuántas veces aparece su ID en la tabla 'votos'
        $sql = "
            SELECT 
                p.id, 
                p.titulo, 
                p.poster, 
                (SELECT COUNT(*) FROM votos WHERE pelicula_id = p.id) AS votos,
                (SELECT COUNT(*) FROM calificaciones WHERE pelicula_id = p.id) AS total_calificaciones,
                (SELECT ROUND(AVG(calificacion), 1) FROM calificaciones WHERE pelicula_id = p.id) AS promedio
            FROM peliculas p
            ORDER BY votos DESC, promedio DESC
        ";
        
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // El ganador es el primer registro (porque ordenamos por votos DESC)
        $ganador = (!empty($data) && $data[0]['votos'] > 0) ? $data[0] : null;

        echo json_encode([
            'peliculas' => $data,
            'ganador'   => $ganador
        ]);
        exit;
    }

} elseif ($method === 'POST') {
    // ── RESETEAR VOTOS (Vaciar tabla) ──────────────────
    if ($action === 'reset_votes') {
        try {
            // 1. Vaciamos la tabla de votos
            $db->exec("DELETE FROM votos");
            
            // 2. Opcional: Si tienes la columna votos en 'peliculas', la ponemos a 0
            $db->exec("UPDATE peliculas SET votos = 0");

            echo json_encode(['success' => true, 'message' => 'Votación reiniciada correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al resetear: ' . $e->getMessage()]);
        }
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $browser_id = trim($data['browser_id'] ?? '');

    if (empty($browser_id)) {
        http_response_code(400);
        die(json_encode(['error' => 'browser_id requerido']));
    }

    // ── VOTAR (Voto Único Global) ──────────────────
    if ($action === 'vote') {
        $pelicula_id = (int)($data['pelicula_id'] ?? 0);
        
        if ($pelicula_id < 1) {
            http_response_code(400);
            die(json_encode(['error' => 'ID inválido']));
        }

        try {
            $db->beginTransaction();

            // 1. Buscamos si el usuario ya tenía un voto en OTRA película
            $stmt = $db->prepare("SELECT pelicula_id FROM votos WHERE browser_id = ? LIMIT 1");
            $stmt->execute([$browser_id]);
            $votoPrevio = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($votoPrevio) {
                $idAnterior = $votoPrevio['pelicula_id'];
                
                // Si el voto es para la misma película, no hacemos nada
                if ($idAnterior == $pelicula_id) {
                    $db->rollBack();
                    echo json_encode(['success' => true, 'message' => 'Ya habías votado por esta']);
                    exit;
                }

                // 2. Quitamos el voto de la película anterior
                $db->prepare("DELETE FROM votos WHERE browser_id = ?")->execute([$browser_id]);
                $db->prepare("UPDATE peliculas SET votos = MAX(0, votos - 1) WHERE id = ?")->execute([$idAnterior]);
            }

            // 3. Insertamos el nuevo voto
            $stmt = $db->prepare("INSERT INTO votos (pelicula_id, browser_id) VALUES (?, ?)");
            $stmt->execute([$pelicula_id, $browser_id]);

            // 4. Sumamos el voto a la nueva película
            $db->prepare("UPDATE peliculas SET votos = votos + 1 WHERE id = ?")->execute([$pelicula_id]);

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Voto actualizado correctamente']);

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    // ── CALIFICAR (1–5) ───────────────────────────────
    if ($action === 'rate') {
        $pelicula_id = (int)($data['pelicula_id'] ?? 0);
        $rating      = (int)($data['rating'] ?? 0);
        $browser_id  = trim($data['browser_id'] ?? '');

        if ($pelicula_id < 1 || $rating < 1 || $rating > 5 || empty($browser_id)) {
            http_response_code(400);
            die(json_encode(['error' => 'Datos inválidos']));
        }

        // 1. ELIMINAR voto previo (calificacion = 0) si existe
        $stmt = $db->prepare("
            DELETE FROM calificaciones 
            WHERE pelicula_id = ? 
            AND browser_id  = ? 
            AND calificacion = 0
        ");
        $stmt->execute([$pelicula_id, $browser_id]);

        // 2. Intentar actualizar calificación existente (si ya tenía 1–5)
        $stmt = $db->prepare("
            UPDATE calificaciones 
            SET calificacion = ?, 
                fecha = CURRENT_TIMESTAMP 
            WHERE pelicula_id = ? 
            AND browser_id  = ?
        ");
        $stmt->execute([$rating, $pelicula_id, $browser_id]);

        $updated = $stmt->rowCount() > 0;

        // 3. Si no había registro previo → insertar nuevo
        if (!$updated) {
            $stmt = $db->prepare("
                INSERT INTO calificaciones 
                (pelicula_id, browser_id, calificacion, fecha)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $ok = $stmt->execute([$pelicula_id, $browser_id, $rating]);

            if (!$ok) {
                http_response_code(500);
                die(json_encode(['success' => false, 'message' => 'Error al insertar calificación']));
            }
        }

        // Opcional: devolver más información útil al frontend
        echo json_encode([
            'success' => true,
            'message' => 'Calificación guardada correctamente'
        ]);
        exit;
    }
    
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida']);