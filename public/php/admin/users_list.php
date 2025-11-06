<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../forms/conection.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }
$userID = (int)$_SESSION['user_id'];

// Validar admin por columna 'rol'
$isAdmin = false;
$q = sqlsrv_prepare($conn, "SELECT rol AS r FROM usuarios WHERE userID=?", [$userID]);
if ($q && sqlsrv_execute($q)) { $r = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC); $isAdmin = (strtolower((string)($r['r'] ?? 'user')) === 'admin'); }
if (!$isAdmin) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }

// List users (incluye 'rol')
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '';
$params = [];
if ($q !== '') {
  $where = "WHERE fullname LIKE ? OR username LIKE ? OR email LIKE ? OR rol LIKE ?";
  $needle = '%'.$q.'%';
  $params = [$needle, $needle, $needle, $needle];
}
$sql = "SELECT userID, fullname, username, email, rol AS role FROM usuarios $where ORDER BY userID DESC";
$stmt = sqlsrv_prepare($conn, $sql, $params);
if ($stmt) sqlsrv_execute($stmt);
$items = [];
if ($stmt) {
  while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $items[] = [
      'id' => (int)$row['userID'],
      'fullname' => $row['fullname'],
      'username' => $row['username'],
      'email' => $row['email'],
      'role' => $row['role']
    ];
  }
}
echo json_encode(['items'=>$items]);
?>
