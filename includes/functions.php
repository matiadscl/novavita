<?php
/**
 * Funciones de autenticación y sesión del dashboard Novavita
 */

session_start();

require_once __DIR__ . '/config.php';

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function is_authenticated(): bool {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Redirige al login si no está autenticado
 */
function require_auth(): void {
    if (!is_authenticated()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Valida credenciales de login
 * @param string $user
 * @param string $pass
 * @return bool
 */
function validate_login(string $user, string $pass): bool {
    return $user === DASHBOARD_USER && $pass === DASHBOARD_PASS;
}

/**
 * Genera token CSRF
 * @return string
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF
 * @param string $token
 * @return bool
 */
function validate_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Envía headers de seguridad
 */
function send_security_headers(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}
