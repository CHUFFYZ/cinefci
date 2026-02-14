<?php
// Asegúrate de que la carpeta 'db' exista en tu proyecto
if (!file_exists('db')) {
    mkdir('db', 0777, true);
}

$db = new PDO('sqlite:db/peliculas.db');
// Configurar para que PDO muestre errores si algo falla
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ═══════════════════════════════════════════════════════════════════════════
// CREAR TABLAS
// ═══════════════════════════════════════════════════════════════════════════

// Tabla de películas (ahora con veces_ganadora)
$db->exec("CREATE TABLE IF NOT EXISTS peliculas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    poster TEXT,
    poster_large TEXT,
    resumen TEXT,
    votos INTEGER DEFAULT 0,
    veces_ganadora INTEGER DEFAULT 0
)");

// Tabla de votos
$db->exec("CREATE TABLE IF NOT EXISTS votos (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    pelicula_id   INTEGER NOT NULL,
    browser_id    TEXT NOT NULL,
    fecha         DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pelicula_id, browser_id),
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE
)");

// Tabla de calificaciones
$db->exec("CREATE TABLE IF NOT EXISTS calificaciones (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    pelicula_id   INTEGER NOT NULL,
    browser_id    TEXT NOT NULL,
    calificacion  INTEGER NOT NULL CHECK(calificacion BETWEEN 1 AND 5),
    fecha         DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pelicula_id, browser_id),
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE
)");

// Tabla de categorías
$db->exec("CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL UNIQUE
)");

// Tabla intermedia película-categoría (muchos a muchos)
$db->exec("CREATE TABLE IF NOT EXISTS pelicula_categorias (
    pelicula_id INTEGER NOT NULL,
    categoria_id INTEGER NOT NULL,
    PRIMARY KEY (pelicula_id, categoria_id),
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
)");

// Tabla de suspensiones
$db->exec("CREATE TABLE IF NOT EXISTS suspensiones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pelicula_id INTEGER NOT NULL UNIQUE,
    fecha_suspension DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_finalizacion DATETIME NOT NULL,
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE
)");

// ═══════════════════════════════════════════════════════════════════════════
// TABLA PARA TEXTOS CONFIGURABLES
// ═══════════════════════════════════════════════════════════════════════════
$db->exec("CREATE TABLE IF NOT EXISTS configuracion_texto (
    clave TEXT PRIMARY KEY,
    valor TEXT NOT NULL DEFAULT '',
    descripcion TEXT,
    ultima_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Insertar textos iniciales (solo si no existen)
$db->exec("INSERT OR IGNORE INTO configuracion_texto (clave, valor, descripcion) VALUES
    ('logo_cargando', 'CINE-FCI', 'Palabras que aparecen al Cargar la pagina'),
    ('texto_cargando', 'CINE-FCI', 'Palabras que aparecen al Cargar la pagina'),
    ('texto_logo', 'CINE-FCI', 'Nombre del sistema que funciona como logo')
");

// ═══════════════════════════════════════════════════════════════════════════
// VERIFICAR Y AGREGAR COLUMNA veces_ganadora SI NO EXISTE
// ═══════════════════════════════════════════════════════════════════════════
try {
    // Intentamos consultar la columna
    $db->query("SELECT veces_ganadora FROM peliculas LIMIT 1");
} catch (PDOException $e) {
    // Si no existe, la agregamos
    $db->exec("ALTER TABLE peliculas ADD COLUMN veces_ganadora INTEGER DEFAULT 0");
    echo "Columna 'veces_ganadora' agregada a la tabla peliculas.<br>";
}

// ═══════════════════════════════════════════════════════════════════════════
// INSERTAR CATEGORÍAS PREDEFINIDAS (si no existen)
// ═══════════════════════════════════════════════════════════════════════════
$categorias_default = [
    'Acción',
    'Romance',
    'Comedia',
    'Drama',
    'Terror',
    'Fantasía',
    'Ciencia Ficción',
    'Animación',
    'Aventura',
    'Suspenso',
    'basura',
    'Preferidas por el desarrollador'
];

$stmt = $db->prepare("INSERT OR IGNORE INTO categorias (nombre) VALUES (?)");
foreach ($categorias_default as $cat) {
    $stmt->execute([$cat]);
}

echo "Base de datos actualizada correctamente. Todas las tablas creadas.";
