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

$name = trim($_POST['name'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$image = trim($_POST['image'] ?? '');
$category = trim($_POST['category'] ?? '');
if ($name==='') { http_response_code(400); echo json_encode(['error'=>'missing_fields']); exit; }

$sql = "INSERT INTO productos (name, price_cop, image_url, category) VALUES (?, ?, ?, ?)";
$stmt = sqlsrv_prepare($conn, $sql, [$name, $price, $image, $category]);
if ($stmt && sqlsrv_execute($stmt)) { echo json_encode(['ok'=>true]); exit; }
http_response_code(500); echo json_encode(['error'=>'sql']);
?>

