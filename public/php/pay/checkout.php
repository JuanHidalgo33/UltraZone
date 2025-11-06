<?php
// ULTRAZONE/public/php/pay/checkout.php
require __DIR__ . '/config.php';

// Crear preferencia
$preference = new MercadoPago\Preference();

// Item de ejemplo (puedes hacerlo dinámico más adelante)
$item = new MercadoPago\Item();
$item->title = "Producto UltraZone";
$item->quantity = 1;
$item->unit_price = 45000; // COP

$preference->items = [$item];

// URLs de retorno (local, sin webhook)
$preference->back_urls = [
    "success" => $PUBLIC_BASE_URL . "/return.php?status=success",
    "failure" => $PUBLIC_BASE_URL . "/return.php?status=failure",
    "pending" => $PUBLIC_BASE_URL . "/return.php?status=pending"
];
$preference->auto_return = "approved";

// Guardar preferencia y obtener enlace
$preference->save();

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Checkout UltraZone</title>
</head>
<body>
  <h1>Checkout UltraZone</h1>
  <p>
    <a href="<?php echo htmlspecialchars($preference->init_point, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
      Pagar con Mercado Pago
    </a>
  </p>
</body>
</html>
