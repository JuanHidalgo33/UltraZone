<?php
session_start();
require "forms/conection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID   = $_SESSION['user_id'];
$fullname = $_POST['fullname'];
$username = $_POST['username'];
$email    = $_POST['email'];
$newFile  = null;

// ✅ Si el usuario sube una nueva imagen
if (!empty($_FILES['profile_image']['name'])) {
    $fileName = $_FILES['profile_image']['name'];
    $tmpName  = $_FILES['profile_image']['tmp_name'];

    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFile = "profile_" . $userID . "." . $ext;

    move_uploaded_file($tmpName, "uploads/" . $newFile);

    $sql = "UPDATE usuarios SET fullname=?, username=?, email=?, profile_image=? WHERE userID=?";
    $params = array($fullname, $username, $email, $newFile, $userID);

} else {
    // ✅ Sin imagen
    $sql = "UPDATE usuarios SET fullname=?, username=?, email=? WHERE userID=?";
    $params = array($fullname, $username, $email, $userID);
}

$stmt = sqlsrv_prepare($conn, $sql, $params);

if ($stmt && sqlsrv_execute($stmt)) {
    header("Location: MyAccount.php?update=ok");
    exit();
} else {
    header("Location: MyAccount.php?update=error");
    exit();
}
?>
