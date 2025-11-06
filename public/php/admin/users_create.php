<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../forms/conection.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }
$uid = (int)$_SESSION['user_id'];

// Validar admin (rol)
$isAdmin = false;
$q = sqlsrv_prepare($conn, "SELECT rol FROM usuarios WHERE userID=?", [$uid]);
if ($q && sqlsrv_execute($q)) { $r = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC); $isAdmin = (strtolower((string)($r['rol'] ?? 'user'))==='admin'); }
if (!$isAdmin) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }

$fullname = trim($_POST['fullname'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$rol      = strtolower(trim($_POST['rol'] ?? 'user')) === 'admin' ? 'admin' : 'user';

if ($fullname === '' || $username === '' || $email === '') { http_response_code(400); echo json_encode(['error'=>'missing_fields']); exit; }

// Detectar si la columna 'passwrd' existe para insertar un valor por defecto
$hasPass = false;
$meta = sqlsrv_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='usuarios' AND COLUMN_NAME='passwrd'");
if ($meta && sqlsrv_fetch($meta) !== false) { $hasPass = true; }

if ($hasPass) {
  $defaultPass = 'temporal123';
  $sql = "INSERT INTO usuarios (fullname, username, email, passwrd, rol) VALUES (?, ?, ?, ?, ?)";
  $params = [$fullname, $username, $email, $defaultPass, $rol];
} else {
  $sql = "INSERT INTO usuarios (fullname, username, email, rol) VALUES (?, ?, ?, ?)";
  $params = [$fullname, $username, $email, $rol];
}

$stmt = sqlsrv_prepare($conn, $sql, $params);
if ($stmt && sqlsrv_execute($stmt)) { echo json_encode(['ok'=>true]); exit; }
http_response_code(500); echo json_encode(['error'=>'sql']);
?>
