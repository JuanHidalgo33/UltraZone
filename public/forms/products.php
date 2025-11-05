<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/conection.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$limit = 50;

// Supone tabla [productos] con columnas:
// productID (int), name (nvarchar), price_cop (decimal), image_url (nvarchar), category (nvarchar, opcional)
// Adapta nombres si tu esquema difiere.

$whereParts = [];
$params = [];
if ($q !== '') {
    $whereParts[] = 'name LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($category !== '') {
    $whereParts[] = 'category = ?';
    $params[] = $category;
}
$where = count($whereParts) ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

$sql = "SELECT TOP($limit) productID AS id, name, price_cop AS price, image_url AS image FROM productos $where ORDER BY productID DESC";

$stmt = sqlsrv_prepare($conn, $sql, $params);
if (!$stmt || !sqlsrv_execute($stmt)) {
    echo json_encode([ 'items' => [], 'error' => 'sql_error' ]);
    exit;
}

$items = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Normalizar imagen relativa si solo guardaste nombre
    $image = $row['image'];
    if ($image && !preg_match('/^https?:/i', $image)) {
        $image = 'assets/img/' . ltrim($image, '/');
    }
    $items[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'price' => (float)$row['price'], // asumido en COP
        'image' => $image ?: 'assets/img/BLACK FRONT.png'
    ];
}

echo json_encode([ 'items' => $items ]);
?>
