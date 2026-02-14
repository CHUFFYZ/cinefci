<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
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

    // ═══════════════════════════════════════════════════════════════════════════
    // LISTAR PELÍCULAS CON CATEGORÍAS Y SUSPENSIONES
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'list') {
        $browser_id = $_GET['browser_id'] ?? '';
        $categoria_filtro = $_GET['categoria'] ?? '';

        // Consulta base
        $sql = "
            SELECT 
                p.*,
                (SELECT COUNT(*) FROM calificaciones WHERE pelicula_id = p.id) AS total_calificaciones,
                (SELECT ROUND(AVG(calificacion), 1) FROM calificaciones WHERE pelicula_id = p.id) AS promedio,
                (SELECT 1 FROM votos WHERE pelicula_id = p.id AND browser_id = :bid LIMIT 1) AS ya_voto,
                (SELECT calificacion FROM calificaciones WHERE pelicula_id = p.id AND browser_id = :bid LIMIT 1) AS user_rating,
                (SELECT fecha_finalizacion FROM suspensiones WHERE pelicula_id = p.id LIMIT 1) AS fecha_suspension
            FROM peliculas p
        ";

        // Agregar filtro de categoría si existe
        if (!empty($categoria_filtro)) {
            $sql .= "
                WHERE p.id IN (
                    SELECT pc.pelicula_id 
                    FROM pelicula_categorias pc
                    INNER JOIN categorias c ON pc.categoria_id = c.id
                    WHERE c.nombre = :categoria
                )
            ";
        }

        $sql .= " ORDER BY p.id";
        
        $stmt = $db->prepare($sql);
        $params = [':bid' => $browser_id];
        
        if (!empty($categoria_filtro)) {
            $params[':categoria'] = $categoria_filtro;
        }
        
        $stmt->execute($params);
        $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener categorías para cada película
        foreach ($peliculas as &$pelicula) {
            $stmt_cat = $db->prepare("
                SELECT c.nombre 
                FROM categorias c
                INNER JOIN pelicula_categorias pc ON c.id = pc.categoria_id
                WHERE pc.pelicula_id = ?
            ");
            $stmt_cat->execute([$pelicula['id']]);
            $pelicula['categorias'] = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);
            
            // Verificar si está suspendida
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

        // El ganador es el primer registro
        $ganador = (!empty($data) && $data[0]['votos'] > 0) ? $data[0] : null;

        // La más votada históricamente
        $mas_votada = null;
        $max_veces = 0;
        foreach ($data as $pelicula) {
            if ($pelicula['veces_ganadora'] > $max_veces) {
                $max_veces = $pelicula['veces_ganadora'];
                $mas_votada = $pelicula;
            }
        }

        echo json_encode([
            'peliculas' => $data,
            'ganador'   => $ganador,
            'mas_votada' => $mas_votada
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
        
        // Obtener categorías
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

} elseif ($method === 'POST') {
    
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

    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $browser_id = trim($data['browser_id'] ?? '');

    /*if (empty($browser_id) && $action !== 'suspend' && $action !== 'unsuspend') {
        http_response_code(400);
        die(json_encode(['error' => 'browser_id requerido']));
    }*/
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

        // Verificar si está suspendida
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
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT pelicula_id FROM votos WHERE browser_id = ? LIMIT 1");
            $stmt->execute([$browser_id]);
            $votoPrevio = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($votoPrevio) {
                $idAnterior = $votoPrevio['pelicula_id'];
                
                if ($idAnterior == $pelicula_id) {
                    $db->rollBack();
                    echo json_encode(['success' => true, 'message' => 'Ya habías votado por esta']);
                    exit;
                }

                $db->prepare("DELETE FROM votos WHERE browser_id = ?")->execute([$browser_id]);
                $db->prepare("UPDATE peliculas SET votos = MAX(0, votos - 1) WHERE id = ?")->execute([$idAnterior]);
            }

            $stmt = $db->prepare("INSERT INTO votos (pelicula_id, browser_id) VALUES (?, ?)");
            $stmt->execute([$pelicula_id, $browser_id]);

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
    
    // ═══════════════════════════════════════════════════════════════════════════
    // CALIFICAR (se permite incluso si está suspendida)
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'rate') {
        $pelicula_id = (int)($data['pelicula_id'] ?? 0);
        $rating      = (int)($data['rating'] ?? 0);
        $browser_id  = trim($data['browser_id'] ?? '');

        if ($pelicula_id < 1 || $rating < 1 || $rating > 5 || empty($browser_id)) {
            http_response_code(400);
            die(json_encode(['error' => 'Datos inválidos']));
        }

        $stmt = $db->prepare("
            DELETE FROM calificaciones 
            WHERE pelicula_id = ? 
            AND browser_id  = ? 
            AND calificacion = 0
        ");
        $stmt->execute([$pelicula_id, $browser_id]);

        $stmt = $db->prepare("
            UPDATE calificaciones 
            SET calificacion = ?, 
                fecha = CURRENT_TIMESTAMP 
            WHERE pelicula_id = ? 
            AND browser_id  = ?
        ");
        $stmt->execute([$rating, $pelicula_id, $browser_id]);

        $updated = $stmt->rowCount() > 0;

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

        echo json_encode([
            'success' => true,
            'message' => 'Calificación guardada correctamente'
        ]);
        exit;
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // AGREGAR PELÍCULA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'add_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        
        $titulo = trim($data['titulo'] ?? '');
        $poster = trim($data['poster'] ?? '');
        $poster_large = trim($data['poster_large'] ?? '');
        $resumen = trim($data['resumen'] ?? '');
        $categorias = $data['categorias'] ?? [];
        
        if (empty($titulo) || empty($poster) || empty($poster_large) || empty($resumen)) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']));
        }
        
        if (empty($categorias) || count($categorias) > 10) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Debes seleccionar entre 1 y 10 categorías']));
        }
        
        try {
            $db->beginTransaction();
            
            // Insertar película
            $stmt = $db->prepare("
                INSERT INTO peliculas (titulo, poster, poster_large, resumen, votos, veces_ganadora)
                VALUES (?, ?, ?, ?, 0, 0)
            ");
            $stmt->execute([$titulo, $poster, $poster_large, $resumen]);
            
            $pelicula_id = $db->lastInsertId();
            
            // Insertar categorías
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
    // ACTUALIZAR PELÍCULA
    // ═══════════════════════════════════════════════════════════════════════════
    if ($action === 'update_movie') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        
        $id = (int)($data['id'] ?? 0);
        $titulo = trim($data['titulo'] ?? '');
        $poster = trim($data['poster'] ?? '');
        $poster_large = trim($data['poster_large'] ?? '');
        $resumen = trim($data['resumen'] ?? '');
        $veces_ganadora = (int)($data['veces_ganadora'] ?? 0);
        $categorias = $data['categorias'] ?? [];
        
        if ($id < 1 || empty($titulo) || empty($poster) || empty($poster_large) || empty($resumen)) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']));
        }
        
        if (empty($categorias) || count($categorias) > 10) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Debes seleccionar entre 1 y 10 categorías']));
        }
        
        try {
            $db->beginTransaction();
            
            // Actualizar película
            $stmt = $db->prepare("
                UPDATE peliculas 
                SET titulo = ?, poster = ?, poster_large = ?, resumen = ?, veces_ganadora = ?
                WHERE id = ?
            ");
            $stmt->execute([$titulo, $poster, $poster_large, $resumen, $veces_ganadora, $id]);
            
            // Eliminar categorías anteriores
            $db->prepare("DELETE FROM pelicula_categorias WHERE pelicula_id = ?")->execute([$id]);
            
            // Insertar nuevas categorías
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
        
        $pelicula_id = (int)($data['pelicula_id'] ?? 0);
        $fecha_finalizacion = $data['fecha_finalizacion'] ?? '';
        
        if ($pelicula_id < 1 || empty($fecha_finalizacion)) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Datos incompletos']));
        }
        
        try {
            // Verificar si ya existe una suspensión activa
            $stmt_check = $db->prepare("SELECT id FROM suspensiones WHERE pelicula_id = ?");
            $stmt_check->execute([$pelicula_id]);
            
            if ($stmt_check->fetch()) {
                // Actualizar suspensión existente
                $stmt = $db->prepare("
                    UPDATE suspensiones 
                    SET fecha_finalizacion = ?, fecha_suspension = CURRENT_TIMESTAMP
                    WHERE pelicula_id = ?
                ");
                $stmt->execute([$fecha_finalizacion, $pelicula_id]);
            } else {
                // Crear nueva suspensión
                $stmt = $db->prepare("
                    INSERT INTO suspensiones (pelicula_id, fecha_finalizacion)
                    VALUES (?, ?)
                ");
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
    // REACTIVAR PELÍCULA (quitar suspensión)
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
            // Las relaciones se eliminan en cascada gracias a FOREIGN KEY
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
