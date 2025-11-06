<?php
/**
 * ULTRAZONE/public/php/pay/config.php
 * Config base para Mercado Pago (SDK dx-php) sin webhooks.
 */

// 1) Autoload de Composer (probamos 2 rutas por robustez)
$autoloadPaths = [
    __DIR__ . '/../../../vendor/autoload.php', // si estás en UltraZone/public/php/pay
];
$autoloadLoaded = false;
foreach ($autoloadPaths as $p) {
    if (file_exists($p)) {
        require $p;
        $autoloadLoaded = true;
        break;
    }
}
if (!$autoloadLoaded) {
    http_response_code(500);
    die("No se encontró vendor/autoload.php. Ejecuta 'composer install' en la raíz del proyecto.");
}

// 2) Tu Access Token TEST (para pruebas locales)
MercadoPago\SDK::setAccessToken("TEST-2cf6ddc4-2c60-4c04-9974-e8056f8c80d5");

// 3) Construir URL base para las back_urls (local)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detectar si el script corre exactamente dentro de /public/php/pay
// dirname(__DIR__) => .../public/php
// basename(dirname(__DIR__)) => 'php'
// basename(__DIR__) => 'pay'
$baseSuffix = '/public/php/pay';
$PUBLIC_BASE_URL = $scheme . '://' . $host . $baseSuffix;
