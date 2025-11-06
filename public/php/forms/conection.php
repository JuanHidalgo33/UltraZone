<?php
$serverName = "DESKTOP-STJSKJ9";

$connectionInfo = [
    "Database" => "UltraZone",       
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die("Error de conexión: " . print_r(sqlsrv_errors(), true));
}
?>

