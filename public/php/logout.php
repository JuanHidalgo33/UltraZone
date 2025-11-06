<?php
// valida sesión iniciada y solo intenta cerrar sesión si realmente existe una
$hadSession = isset($_COOKIE[session_name()]);
$userWasLogged = false;

if ($hadSession) {
    session_start();
    // Marcar si había un usuario autenticado antes de limpiar
    $userWasLogged = isset($_SESSION['user_id']);

    // Limpiar datos de sesión
    $_SESSION = [];

    // Invalidar cookie de sesión si aplica
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

// Limpiar cookie de "recordar usuario"
setcookie('recordar_usuario', '', time() - 3600, '/');

// Redirigir a login
$qs = ($hadSession && $userWasLogged) ? '?logout=ok' : '';
header('Location: forms/login.php' . $qs);
exit();
?>
