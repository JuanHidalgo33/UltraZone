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

$id = (int)($_POST['id'] ?? 0);
if ($id<=0) { http_response_code(400); echo json_encode(['error'=>'missing_id']); exit; }

// Evitar que el admin actual se borre a sí mismo
if ($id === $uid) { http_response_code(400); echo json_encode(['error'=>'cannot_delete_self']); exit; }

$stmt = sqlsrv_prepare($conn, "DELETE FROM usuarios WHERE userID=?", [$id]);
if ($stmt && sqlsrv_execute($stmt)) { echo json_encode(['ok'=>true]); exit; }
http_response_code(500); echo json_encode(['error'=>'sql']);
?>

