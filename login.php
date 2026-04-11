<?php
require_once __DIR__ . '/includes/functions.php';
send_security_headers();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_DEFAULT) ?? '';
    if (!validate_csrf($token)) {
        $error = 'Token de seguridad inválido. Intenta de nuevo.';
    } else {
        $user = filter_input(INPUT_POST, 'username', FILTER_DEFAULT) ?? '';
        $pass = filter_input(INPUT_POST, 'password', FILTER_DEFAULT) ?? '';
        if (validate_login($user, $pass)) {
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            header('Location: index.php');
            exit;
        } else {
            $error = 'Credenciales incorrectas.';
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Novavita Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Novavita</h1>
                <p>Clínica & Spa</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Ingresar</button>
            </form>
        </div>
        <p class="login-footer">Dashboard de rendimiento digital</p>
    </div>
</body>
</html>
