<?php
header('Content-Type: application/json; charset=utf-8');

$hasSessionCookie = isset($_COOKIE[session_name()]);
if ($hasSessionCookie) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

$logged = isset($_SESSION) && isset($_SESSION['user_id']);
echo json_encode([ 'logged' => $logged ]);
?>

