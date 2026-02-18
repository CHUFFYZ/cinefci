<?php
require_once __DIR__ . '/auth.php';

// Si ya está logueado, redirigir al panel
if (isLoggedIn()) {
    header('Location: panel.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (empty($user) || empty($pass)) {
        $error = 'Por favor ingresa usuario y contraseña.';
    } elseif (!attemptLogin($user, $pass)) {
        // Pequeño delay para dificultar ataques de fuerza bruta
        sleep(1);
        $error = 'Usuario o contraseña incorrectos.';
    } else {
        header('Location: panel.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - CineFCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logo/logo.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #e50914;
            --dark:    #141414;
            --darker:  #0a0a0a;
            --gray:    #2f2f2f;
            --gold:    #ffd700;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 50%, #1a0000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .login-box {
            background: #1a1a1a;
            border: 1px solid rgba(229,9,20,0.3);
            border-radius: 20px;
            padding: 50px 45px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-logo img {
            height: 60px;
            margin-bottom: 12px;
        }

        .login-logo h1 {
            font-family: 'Bebas Neue', cursive;
            font-size: 2.2rem;
            color: var(--primary);
            letter-spacing: 0.15em;
        }

        .login-logo p {
            color: #8c8c8c;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #aaa;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            background: #2a2a2a;
            border: 1px solid #3a3a3a;
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: border-color 0.2s;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(229,9,20,0.15);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-top: 10px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-login:hover  { background: #c0070f; }
        .btn-login:active { transform: scale(0.98); }

        .error-msg {
            background: rgba(229,9,20,0.15);
            border: 1px solid rgba(229,9,20,0.4);
            color: #ff6b6b;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #555;
            font-size: 0.82rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover { color: #888; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">
        <img src="image/logo/logo.png" alt="CineFCI" onerror="this.style.display='none'">
        <h1>CINE-FCI</h1>
        <p>Acceso Administrativo</p>
    </div>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="usuario">Usuario</label>
            <input
                type="text"
                id="usuario"
                name="usuario"
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                required
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit" class="btn-login">Ingresar al Panel</button>
    </form>

    <a href="index.php" class="back-link">← Volver al catálogo</a>
</div>
</body>
</html>
