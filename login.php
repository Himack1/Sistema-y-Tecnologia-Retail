<?php
// ============================================================
//  login.php — Pantalla de autenticación
//  Sistema de Toma de Decisiones · Cadena Retail
// ============================================================
session_start();

// Si ya hay sesión activa, ir directo al dashboard
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario    = trim($_POST['usuario']   ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Credenciales (en producción usar BD + password_hash)
    if ($usuario === 'admin' && $contrasena === '123456') {
        $_SESSION['usuario']   = $usuario;
        $_SESSION['login_time'] = time();
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos. Inténtalo de nuevo.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — RetailAnalytics</title>
    <meta name="description" content="Acceso seguro al Sistema de Toma de Decisiones Retail.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base:      #0a0e1a;
            --bg-card:      #111827;
            --accent-1:     #6366f1;
            --accent-2:     #22d3ee;
            --text-primary: #f1f5f9;
            --text-muted:   #94a3b8;
            --border:       #1e2d47;
            --danger:       #ef4444;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Fondo animado con gradiente */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 30%, rgba(99,102,241,.18) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 80% 70%, rgba(34,211,238,.12) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Partículas decorativas */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        /* Card de login */
        .login-card {
            position: relative;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px 44px 44px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 64px rgba(0,0,0,.6), 0 0 0 1px rgba(99,102,241,.1);
            animation: slide-up .45s cubic-bezier(.16,1,.3,1);
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Logo / Brand */
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 36px;
        }

        .brand-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 6px 20px rgba(99,102,241,.4);
        }

        .brand-text h2 {
            font-size: 1.3rem;
            font-weight: 800;
            background: linear-gradient(90deg, var(--text-primary), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-text p {
            font-size: .74rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Título del formulario */
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: .84rem;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        /* Grupos de campos */
        .field-group {
            margin-bottom: 18px;
        }

        .field-group label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 7px;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .field-group input {
            width: 100%;
            background: #0a0e1a;
            border: 1px solid var(--border);
            border-radius: 11px;
            color: var(--text-primary);
            padding: 13px 16px;
            font-size: .92rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .field-group input:focus {
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(99,102,241,.2);
        }

        .field-group input::placeholder { color: #374151; }

        /* Alerta de error */
        .alert-error {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.35);
            border-radius: 10px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: .83rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake .35s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-6px); }
            75%      { transform: translateX(6px); }
        }

        /* Botón principal */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent-1), #818cf8);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: .03em;
            transition: transform .15s, box-shadow .2s, opacity .2s;
            box-shadow: 0 6px 20px rgba(99,102,241,.45);
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(99,102,241,.6);
        }

        .btn-login:active { transform: translateY(0); }

        /* Credenciales de demo */
        .demo-hint {
            margin-top: 22px;
            padding: 12px 15px;
            background: rgba(34,211,238,.06);
            border: 1px solid rgba(34,211,238,.15);
            border-radius: 10px;
            font-size: .75rem;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.7;
        }

        .demo-hint strong { color: var(--accent-2); }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 28px;
            font-size: .72rem;
            color: #374151;
        }
    </style>
</head>
<body>
<div class="bg-grid"></div>

<div class="login-card">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-icon">📊</div>
        <div class="brand-text">
            <h2>RetailAnalytics</h2>
            <p>Sistema de Toma de Decisiones</p>
        </div>
    </div>

    <p class="login-title">Bienvenido</p>
    <p class="login-subtitle">Inicia sesión para acceder al dashboard</p>

    <!-- Error -->
    <?php if ($error): ?>
    <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST" action="login.php" autocomplete="on">
        <div class="field-group">
            <label for="usuario">Usuario</label>
            <input
                type="text"
                id="usuario"
                name="usuario"
                placeholder="Ingresa tu usuario"
                value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                required
                autofocus
            >
        </div>

        <div class="field-group">
            <label for="contrasena">Contraseña</label>
            <input
                type="password"
                id="contrasena"
                name="contrasena"
                placeholder="••••••••"
                required
            >
        </div>

        <button type="submit" class="btn-login">🔐 Iniciar Sesión</button>
    </form>

    <!-- Demo hint -->
    <div class="demo-hint">
        🧪 <strong>Demo:</strong> Usuario <strong>admin</strong> · Contraseña <strong>123456</strong>
    </div>

    <p class="login-footer">RetailAnalytics &copy; <?= date('Y') ?> · Cadena Retail</p>
</div>

</body>
</html>
