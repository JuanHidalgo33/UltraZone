<?php
session_start();
require __DIR__ . '/forms/conection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: forms/login.php?info=login_required');
  exit();
}

$userID   = (int)$_SESSION['user_id'];
$fullname = trim($_POST['fullname'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');

$hasFile  = !empty($_FILES['profile_image']['name']);

// Eliminar foto de perfil si se solicita
if (isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
  // Obtener nombre de archivo actual
  $stmtSel = sqlsrv_prepare($conn, "SELECT profile_image FROM usuarios WHERE userID=?", [$userID]);
  if ($stmtSel && sqlsrv_execute($stmtSel)) {
    $row = sqlsrv_fetch_array($stmtSel, SQLSRV_FETCH_ASSOC);
    $current = $row['profile_image'] ?? '';
    if ($current) {
      $path = realpath(__DIR__ . '/../uploads');
      if ($path && file_exists($path . DIRECTORY_SEPARATOR . $current)) {
        @unlink($path . DIRECTORY_SEPARATOR . $current);
      }
    }
  }
  // Borrar referencia en DB
  $stmtUpd = sqlsrv_prepare($conn, "UPDATE usuarios SET profile_image=NULL WHERE userID=?", [$userID]);
  if ($stmtUpd && sqlsrv_execute($stmtUpd)) {
    header('Location: account.php?update=ok&photo=removed');
    exit();
  }
  header('Location: account.php?update=error');
  exit();
}

if ($hasFile) {
  $fileName = $_FILES['profile_image']['name'];
  $tmpName  = $_FILES['profile_image']['tmp_name'];
  $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
  $safeExt = in_array($ext, ['png','jpg','jpeg','gif','webp']) ? $ext : 'png';
  $newFile = 'profile_' . $userID . '.' . $safeExt;
  @move_uploaded_file($tmpName, __DIR__ . '/../uploads/' . $newFile);
  $sql = "UPDATE usuarios SET fullname=?, username=?, email=?, profile_image=? WHERE userID=?";
  $params = [$fullname, $username, $email, $newFile, $userID];
} else {
  $sql = "UPDATE usuarios SET fullname=?, username=?, email=? WHERE userID=?";
  $params = [$fullname, $username, $email, $userID];
}

$stmt = sqlsrv_prepare($conn, $sql, $params);
if ($stmt && sqlsrv_execute($stmt)) {
  header('Location: account.php?update=ok');
  exit();
}
header('Location: account.php?update=error');
exit();
?>
