<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../forms/conection.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }
$uid = (int)$_SESSION['user_id'];

$isAdmin = false;
$q = sqlsrv_prepare($conn, "SELECT rol FROM usuarios WHERE userID=?", [$uid]);
if ($q && sqlsrv_execute($q)) { $r = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC); $isAdmin = (strtolower((string)($r['rol'] ?? 'user'))==='admin'); }
if (!$isAdmin) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }

$id       = (int)($_POST['id'] ?? 0);
$fullname = trim($_POST['fullname'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$rol      = strtolower(trim($_POST['rol'] ?? 'user')) === 'admin' ? 'admin' : 'user';

if ($id<=0 || $fullname==='' || $username==='' || $email==='') { http_response_code(400); echo json_encode(['error'=>'missing_fields']); exit; }

$sql = "UPDATE usuarios SET fullname=?, username=?, email=?, rol=? WHERE userID=?";
$stmt = sqlsrv_prepare($conn, $sql, [$fullname, $username, $email, $rol, $id]);
if ($stmt && sqlsrv_execute($stmt)) { echo json_encode(['ok'=>true]); exit; }
http_response_code(500); echo json_encode(['error'=>'sql']);
?>

