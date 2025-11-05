<?php
$serverName = "DESKTOP-STJSKJ9";

$connectionInfo = [
    "Database" => "UltraZoneDB",
    "UID"      => "ultrauser",          
    "PWD"      => "Ultra1234!",         
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die("Error de conexión: " . print_r(sqlsrv_errors(), true));
}
?>