<?php
// ═══════════════════════════════════════════════════════════════════════════
// AUTH.PHP — Sistema de autenticación para el panel de CineFCI
// ═══════════════════════════════════════════════════════════════════════════

// ── Cargar variables de entorno ──────────────────────────────────────────
function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Forzar en las tres formas posibles para máxima compatibilidad
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
        putenv("$key=$value");
    }
}

loadEnv(__DIR__ . '/.env');

// ── Configurar sesión segura ─────────────────────────────────────────────
$sessionName     = $_ENV['SESSION_NAME']     ?? getenv('SESSION_NAME')     ?: 'cinefci_admin_session';
$sessionLifetime = (int)($_ENV['SESSION_LIFETIME'] ?? getenv('SESSION_LIFETIME') ?: 86400);

ini_set('session.cookie_httponly', '1');   // No accesible desde JS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', $sessionLifetime);

session_name($sessionName);
session_start();

// ── Leer admins del .env ─────────────────────────────────────────────────
function getAdmins(): array {
    $admins = [];
    $i = 1;
    while (true) {
        $user = $_ENV["ADMIN_USER_$i"] ?? getenv("ADMIN_USER_$i") ?: null;
        $pass = $_ENV["ADMIN_PASS_$i"] ?? getenv("ADMIN_PASS_$i") ?: null;
        if (!$user || !$pass) break;
        $admins[$user] = $pass;
        $i++;
    }
    return $admins;
}

// ── Verificar si hay sesión activa ───────────────────────────────────────
function isLoggedIn(): bool {
    if (empty($_SESSION['admin_logged_in'])) return false;
    if (empty($_SESSION['admin_user']))      return false;

    // Verificar que la sesión no haya expirado
    $lifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 86400);
    if (isset($_SESSION['admin_last_activity'])) {
        if (time() - $_SESSION['admin_last_activity'] > $lifetime) {
            session_destroy();
            return false;
        }
    }

    $_SESSION['admin_last_activity'] = time();
    return true;
}

// ── Intentar login ───────────────────────────────────────────────────────
function attemptLogin(string $user, string $pass): bool {
    $admins = getAdmins();

    if (!isset($admins[$user])) return false;

    // Comparación segura de contraseña
    if (!hash_equals($admins[$user], $pass)) return false;

    // Regenerar ID de sesión para prevenir session fixation
    session_regenerate_id(true);

    $_SESSION['admin_logged_in']    = true;
    $_SESSION['admin_user']         = $user;
    $_SESSION['admin_last_activity'] = time();

    return true;
}

// ── Cerrar sesión ────────────────────────────────────────────────────────
function logout(): void {
    $_SESSION = [];
    session_destroy();
}

// ── Proteger página: redirige al login si no está autenticado ────────────
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// ── Obtener usuario actual ───────────────────────────────────────────────
function currentAdmin(): string {
    return $_SESSION['admin_user'] ?? '';
}
