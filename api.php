<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── Cargar clase Turso (reemplaza PDO SQLite) ────────────────────────────
require_once __DIR__ . '/turso.php';

try {
    $db = new TursoDB(TURSO_URL, TURSO_TOKEN);
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => 'No se pudo conectar a Turso: ' . $e->getMessage()]));
}

// Asegurar que la tabla de ocultación existe
try {
    $db->exec("CREATE TABLE IF NOT EXISTS peliculas_ocultas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pelicula_id INTEGER NOT NULL UNIQUE,
        fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_fin DATETIME NULL
    )");
} catch (Exception $e) { /* ya existe, ignorar */ }

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {

    // ═══════════════════════════════════════════════════════════════════════════
    // LISTAR PELÍCULAS CON CATEGORÍAS Y SUSPENSIONES
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'list') {
        $browser_id      = $_GET['browser_id'] ?? '';
        $categoria_filtro = $_GET['categoria'] ?? '';

        $sql = "
            SELECT 
                p.*,
                (SELECT COUNT(*) FROM calificaciones WHERE pelicula_id = p.id) AS total_calificaciones,
                (SELECT ROUND(AVG(calificacion), 1) FROM calificaciones WHERE pelicula_id = p.id) AS promedio,
                (SELECT 1 FROM votos WHERE pelicula_id = p.id AND browser_id = ? LIMIT 1) AS ya_voto,
                (SELECT calificacion FROM calificaciones WHERE pelicula_id = p.id AND browser_id = ? LIMIT 1) AS user_rating,
                (SELECT fecha_finalizacion FROM suspensiones WHERE pelicula_id = p.id LIMIT 1) AS fecha_suspension
            FROM peliculas p
            LEFT JOIN peliculas_ocultas po ON po.pelicula_id = p.id
            WHERE (
                po.pelicula_id IS NULL
                OR (po.fecha_fin IS NOT NULL AND datetime(po.fecha_fin) <= datetime('now'))
            )
        ";

        $params = [$browser_id, $browser_id];

        if (!empty($categoria_filtro)) {
            $sql .= "
                AND p.id IN (
                    SELECT pc.pelicula_id 
                    FROM pelicula_categorias pc
                    INNER JOIN categorias c ON pc.categoria_id = c.id
                    WHERE c.nombre = ?
                )
            ";
            $params[] = $categoria_filtro;
        }

        $sql .= " ORDER BY p.id";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener TODAS las categorías de una sola vez
        $stmt_cat = $db->query("
            SELECT pc.pelicula_id, c.nombre 
            FROM categorias c
            INNER JOIN pelicula_categorias pc ON c.id = pc.categoria_id
        ");
        $all_cats = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

        // Indexar categorías por pelicula_id
        $cats_por_pelicula = [];
        foreach ($all_cats as $cat) {
            $cats_por_pelicula[$cat['pelicula_id']][] = $cat['nombre'];
        }

        foreach ($peliculas as &$pelicula) {
            $pelicula['categorias'] = $cats_por_pelicula[$pelicula['id']] ?? [];
            $pelicula['suspendida'] = !empty($pelicula['fecha_suspension']);
        }

        echo json_encode($peliculas);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // OBTENER CATEGORÍAS
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'categorias') {
        $stmt = $db->query("SELECT * FROM categorias ORDER BY nombre");
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($categorias);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // ESTADÍSTICAS
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'stats') {
        $sql = "
            SELECT 
                p.id, 
                p.titulo, 
                p.poster,
                p.veces_ganadora,
                (SELECT COUNT(*) FROM votos WHERE pelicula_id = p.id) AS votos,
                (SELECT COUNT(*) FROM calificaciones WHERE pelicula_id = p.id) AS total_calificaciones,
                (SELECT ROUND(AVG(calificacion), 1) FROM calificaciones WHERE pelicula_id = p.id) AS promedio
            FROM peliculas p
            ORDER BY votos DESC, promedio DESC
        ";

        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ganador = (!empty($data) && $data[0]['votos'] > 0) ? $data[0] : null;

        $mas_votada = null;
        $max_veces  = 0;
        foreach ($data as $pelicula) {
            if ($pelicula['veces_ganadora'] > $max_veces) {
                $max_veces  = $pelicula['veces_ganadora'];
                $mas_votada = $pelicula;
            }
        }

        echo json_encode([
            'peliculas'  => $data,
            'ganador'    => $ganador,
            'mas_votada' => $mas_votada,
        ]);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // LISTAR PELÍCULAS SIMPLES (para selectores)
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'movies_list') {
        $stmt = $db->query("SELECT id, titulo FROM peliculas ORDER BY titulo");
        $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($peliculas);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // OBTENER UNA PELÍCULA POR ID
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'get_movie') {
        $id = (int)($_GET['id'] ?? 0);

        if ($id < 1) {
            http_response_code(400);
            die(json_encode(['error' => 'ID inválido']));
        }

        $stmt = $db->prepare("SELECT * FROM peliculas WHERE id = ?");
        $stmt->execute([$id]);
        $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pelicula) {
            http_response_code(404);
            die(json_encode(['error' => 'Película no encontrada']));
        }

        $stmt_cat = $db->prepare("
            SELECT c.nombre 
            FROM categorias c
            INNER JOIN pelicula_categorias pc ON c.id = pc.categoria_id
            WHERE pc.pelicula_id = ?
        ");
        $stmt_cat->execute([$id]);
        $pelicula['categorias'] = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($pelicula);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // LISTAR PELÍCULAS CON SUSPENSIONES
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'movies_with_suspensions') {
        $sql = "
            SELECT 
                p.*,
                (SELECT fecha_finalizacion FROM suspensiones 
                 WHERE pelicula_id = p.id 
                 AND datetime(fecha_finalizacion) > datetime('now')
                 LIMIT 1) AS fecha_suspension
            FROM peliculas p
            ORDER BY p.titulo
        ";

        $stmt = $db->query($sql);
        $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($peliculas as &$pelicula) {
            $pelicula['suspendida'] = !empty($pelicula['fecha_suspension']);
        }

        echo json_encode($peliculas);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // LISTAR PELÍCULAS CON ESTADO DE OCULTACIÓN (para admin-ocultar.php)
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'movies_with_hidden') {
        try {
            // Crear tabla si no existe (exec para DDL)
            $db->exec("
                CREATE TABLE IF NOT EXISTS peliculas_ocultas (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    pelicula_id INTEGER NOT NULL UNIQUE,
                    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
                    fecha_fin DATETIME NULL
                )
            ");
        } catch (Exception $e) {
            // Ignorar error si la tabla ya existe
        }

        try {
            $sql = "
                SELECT 
                    p.id,
                    p.titulo,
                    o.fecha_fin AS fecha_fin_oculta,
                    CASE WHEN o.pelicula_id IS NOT NULL THEN 1 ELSE 0 END AS oculta
                FROM peliculas p
                LEFT JOIN peliculas_ocultas o ON o.pelicula_id = p.id
                ORDER BY p.titulo
            ";

            $stmt = $db->query($sql);
            $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($peliculas as &$pelicula) {
                // Turso devuelve todo como string — forzar tipos correctos
                $pelicula['oculta'] = (int)$pelicula['oculta'];
                $pelicula['fecha_fin_oculta'] = $pelicula['fecha_fin_oculta']; // puede ser null

                // Si ya venció la ocultación temporal, marcar como visible
                if ($pelicula['oculta'] === 1 && $pelicula['fecha_fin_oculta'] !== null) {
                    if (strtotime($pelicula['fecha_fin_oculta']) < time()) {
                        $pelicula['oculta'] = 0;
                    }
                }
            }

            echo json_encode($peliculas);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al cargar películas: ' . $e->getMessage()]);
        }
        exit;
    }

} elseif ($method === 'POST') {

    // ═══════════════════════════════════════════════════════════════════════════
    // OCULTAR PELÍCULA DE CARTELERA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'hide_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $pelicula_id = (int)($data['pelicula_id'] ?? 0);
        $fecha_fin   = (isset($data['fecha_fin']) && $data['fecha_fin'] !== null && $data['fecha_fin'] !== '')
                       ? $data['fecha_fin']
                       : null;

        if ($pelicula_id < 1) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'ID inválido']));
        }

        try {
            // Crear tabla si no existe
            try {
                $db->exec("
                    CREATE TABLE IF NOT EXISTS peliculas_ocultas (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        pelicula_id INTEGER NOT NULL UNIQUE,
                        fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
                        fecha_fin DATETIME NULL
                    )
                ");
            } catch (Exception $e) { /* tabla ya existe */ }

            // Verificar si ya existe
            $stmt_check = $db->prepare("SELECT id FROM peliculas_ocultas WHERE pelicula_id = ?");
            $stmt_check->execute([$pelicula_id]);

            if ($stmt_check->fetch()) {
                $stmt = $db->prepare("UPDATE peliculas_ocultas SET fecha_fin = ?, fecha_inicio = CURRENT_TIMESTAMP WHERE pelicula_id = ?");
                $stmt->execute([$fecha_fin, $pelicula_id]);
            } else {
                $stmt = $db->prepare("INSERT INTO peliculas_ocultas (pelicula_id, fecha_fin) VALUES (?, ?)");
                $stmt->execute([$pelicula_id, $fecha_fin]);
            }

            echo json_encode(['success' => true, 'message' => 'Película ocultada de la cartelera']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DESOCULTAR PELÍCULA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'unhide_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $pelicula_id = (int)($data['pelicula_id'] ?? 0);

        if ($pelicula_id < 1) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'ID inválido']));
        }

        try {
            $stmt = $db->prepare("DELETE FROM peliculas_ocultas WHERE pelicula_id = ?");
            $stmt->execute([$pelicula_id]);
            echo json_encode(['success' => true, 'message' => 'Película visible en cartelera nuevamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // RESETEAR VOTOS
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'reset_votes') {
        try {
            $db->exec("DELETE FROM votos");
            $db->exec("UPDATE peliculas SET votos = 0");
            echo json_encode(['success' => true, 'message' => 'Votación reiniciada correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al resetear: ' . $e->getMessage()]);
        }
        exit;
    }

    $data       = json_decode(file_get_contents('php://input'), true) ?: [];
    $browser_id = trim($data['browser_id'] ?? '');

    if (empty($browser_id) && !in_array($action, ['suspend', 'unsuspend', 'add_movie', 'update_movie', 'suspend_movie', 'unsuspend_movie', 'delete_movie'])) {
        http_response_code(400);
        die(json_encode(['error' => 'browser_id requerido']));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // VOTAR (solo si no está suspendida)
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'vote') {
        $pelicula_id = (int)($data['pelicula_id'] ?? 0);

        if ($pelicula_id < 1) {
            http_response_code(400);
            die(json_encode(['error' => 'ID inválido']));
        }

        $stmt_check = $db->prepare("
            SELECT fecha_finalizacion FROM suspensiones 
            WHERE pelicula_id = ? AND datetime(fecha_finalizacion) > datetime('now')
        ");
        $stmt_check->execute([$pelicula_id]);

        if ($stmt_check->fetch()) {
            http_response_code(400);
            die(json_encode(['error' => 'Esta película está suspendida y no se puede votar']));
        }

        try {
            // ── SELECT fuera de transacción para obtener resultado real ──
            $stmt = $db->prepare("SELECT pelicula_id FROM votos WHERE browser_id = ? LIMIT 1");
            $stmt->execute([$browser_id]);
            $votoPrevio = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($votoPrevio) {
                $idAnterior = (int)$votoPrevio['pelicula_id'];

                if ($idAnterior === $pelicula_id) {
                    echo json_encode(['success' => true, 'message' => 'Ya habías votado por esta']);
                    exit;
                }

                // Quitar voto anterior
                $db->prepare("DELETE FROM votos WHERE browser_id = ?")->execute([$browser_id]);
                $db->prepare("UPDATE peliculas SET votos = MAX(0, votos - 1) WHERE id = ?")->execute([$idAnterior]);
            }

            // Insertar nuevo voto
            $db->prepare("INSERT INTO votos (pelicula_id, browser_id) VALUES (?, ?)")->execute([$pelicula_id, $browser_id]);
            $db->prepare("UPDATE peliculas SET votos = votos + 1 WHERE id = ?")->execute([$pelicula_id]);

            echo json_encode(['success' => true, 'message' => 'Voto registrado correctamente']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CALIFICAR
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'rate') {
        $pelicula_id = (int)($data['pelicula_id'] ?? 0);
        $rating      = (int)($data['rating']      ?? 0);
        $browser_id  = trim($data['browser_id']   ?? '');

        if ($pelicula_id < 1 || $rating < 1 || $rating > 5 || empty($browser_id)) {
            http_response_code(400);
            die(json_encode(['error' => 'Datos inválidos']));
        }

        $stmt = $db->prepare("
            DELETE FROM calificaciones 
            WHERE pelicula_id = ? AND browser_id = ? AND calificacion = 0
        ");
        $stmt->execute([$pelicula_id, $browser_id]);

        $stmt = $db->prepare("
            UPDATE calificaciones 
            SET calificacion = ?, fecha = CURRENT_TIMESTAMP 
            WHERE pelicula_id = ? AND browser_id = ?
        ");
        $stmt->execute([$rating, $pelicula_id, $browser_id]);
        $updated = $stmt->rowCount() > 0;

        if (!$updated) {
            $stmt = $db->prepare("
                INSERT INTO calificaciones (pelicula_id, browser_id, calificacion, fecha)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $ok = $stmt->execute([$pelicula_id, $browser_id, $rating]);

            if (!$ok) {
                http_response_code(500);
                die(json_encode(['success' => false, 'message' => 'Error al insertar calificación']));
            }
        }

        echo json_encode(['success' => true, 'message' => 'Calificación guardada correctamente']);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // AGREGAR PELÍCULA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'add_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $titulo      = trim($data['titulo']      ?? '');
        $poster      = trim($data['poster']      ?? '');
        $poster_large = trim($data['poster_large'] ?? '');
        $trailer     = trim($data['trailer']     ?? '');
        $resumen     = trim($data['resumen']     ?? '');
        $categorias  = $data['categorias']       ?? [];

        if (empty($titulo) || empty($poster) || empty($poster_large) || empty($trailer) || empty($resumen)) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']));
        }

        if (empty($categorias) || count($categorias) > 10) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Debes seleccionar entre 1 y 10 categorías']));
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO peliculas (titulo, poster, poster_large, trailer, resumen, votos, veces_ganadora)
                VALUES (?, ?, ?, ?, ?, 0, 0)
            ");
            $stmt->execute([$titulo, $poster, $poster_large, $trailer, $resumen]);

            $pelicula_id = $db->lastInsertId();

            $stmt_cat = $db->prepare("INSERT INTO pelicula_categorias (pelicula_id, categoria_id) VALUES (?, ?)");
            foreach ($categorias as $cat_id) {
                $stmt_cat->execute([$pelicula_id, $cat_id]);
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Película agregada exitosamente', 'id' => $pelicula_id]);

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // MODIFICAR PELÍCULA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'update_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $id           = (int)($data['id']            ?? 0);
        $titulo       = trim($data['titulo']         ?? '');
        $poster       = trim($data['poster']         ?? '');
        $poster_large = trim($data['poster_large']   ?? '');
        $trailer      = trim($data['trailer']        ?? '');
        $resumen      = trim($data['resumen']        ?? '');
        $veces_ganadora = (int)($data['veces_ganadora'] ?? 0);
        $categorias   = $data['categorias']          ?? [];

        if ($id < 1 || empty($titulo) || empty($poster) || empty($poster_large) || empty($trailer) || empty($resumen)) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']));
        }

        if (empty($categorias) || count($categorias) > 10) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Debes seleccionar entre 1 y 10 categorías']));
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                UPDATE peliculas 
                SET titulo = ?, poster = ?, poster_large = ?, trailer = ?, resumen = ?, veces_ganadora = ?
                WHERE id = ?
            ");
            $stmt->execute([$titulo, $poster, $poster_large, $trailer, $resumen, $veces_ganadora, $id]);

            $db->prepare("DELETE FROM pelicula_categorias WHERE pelicula_id = ?")->execute([$id]);

            $stmt_cat = $db->prepare("INSERT INTO pelicula_categorias (pelicula_id, categoria_id) VALUES (?, ?)");
            foreach ($categorias as $cat_id) {
                $stmt_cat->execute([$id, $cat_id]);
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Película actualizada exitosamente']);

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SUSPENDER PELÍCULA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'suspend_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $pelicula_id      = (int)($data['pelicula_id']      ?? 0);
        $fecha_finalizacion = $data['fecha_finalizacion'] ?? '';

        if ($pelicula_id < 1 || empty($fecha_finalizacion)) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Datos incompletos']));
        }

        try {
            $stmt_check = $db->prepare("SELECT id FROM suspensiones WHERE pelicula_id = ?");
            $stmt_check->execute([$pelicula_id]);

            if ($stmt_check->fetch()) {
                $stmt = $db->prepare("
                    UPDATE suspensiones 
                    SET fecha_finalizacion = ?, fecha_suspension = CURRENT_TIMESTAMP
                    WHERE pelicula_id = ?
                ");
                $stmt->execute([$fecha_finalizacion, $pelicula_id]);
            } else {
                $stmt = $db->prepare("INSERT INTO suspensiones (pelicula_id, fecha_finalizacion) VALUES (?, ?)");
                $stmt->execute([$pelicula_id, $fecha_finalizacion]);
            }

            echo json_encode(['success' => true, 'message' => 'Película suspendida exitosamente']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // REACTIVAR PELÍCULA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'unsuspend_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $pelicula_id = (int)($data['pelicula_id'] ?? 0);

        if ($pelicula_id < 1) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'ID inválido']));
        }

        try {
            $stmt = $db->prepare("DELETE FROM suspensiones WHERE pelicula_id = ?");
            $stmt->execute([$pelicula_id]);
            echo json_encode(['success' => true, 'message' => 'Película reactivada exitosamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // ELIMINAR PELÍCULA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'delete_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $pelicula_id = (int)($data['pelicula_id'] ?? 0);

        if ($pelicula_id < 1) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'ID inválido']));
        }

        try {
            $stmt = $db->prepare("DELETE FROM peliculas WHERE id = ?");
            $stmt->execute([$pelicula_id]);
            echo json_encode(['success' => true, 'message' => 'Película eliminada exitosamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida']);
