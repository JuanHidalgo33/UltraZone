<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../forms/conection.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }
$uid = (int)$_SESSION['user_id'];

// Validar admin
$isAdmin = false;
$q = sqlsrv_prepare($conn, "SELECT rol FROM usuarios WHERE userID=?", [$uid]);
if ($q && sqlsrv_execute($q)) { $r = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC); $isAdmin = (strtolower((string)($r['rol'] ?? 'user'))==='admin'); }
if (!$isAdmin) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }

$term = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '';
$params = [];
if ($term !== '') {
  $where = "WHERE name LIKE ? OR category LIKE ?";
  $needle = '%'.$term.'%';
  $params = [$needle, $needle];
}
$sql = "SELECT productID AS id, name, price_cop AS price, image_url AS image, category FROM productos $where ORDER BY productID DESC";
$stmt = sqlsrv_prepare($conn, $sql, $params);
if ($stmt) sqlsrv_execute($stmt);

$items = [];
if ($stmt) {
  while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $items[] = [
      'id' => (int)$row['id'],
      'name' => $row['name'],
      'price' => (float)$row['price'],
      'image' => $row['image'],
      'category' => $row['category']
    ];
  }
}
echo json_encode(['items'=>$items]);
?>

