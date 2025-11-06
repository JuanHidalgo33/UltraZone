<?php
// ULTRAZONE/public/php/pay/return.php
$status = $_GET['status'] ?? 'unknown';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Resultado del pago</title>
</head>
<body>
  <?php if ($status === 'success'): ?>
    <h1>✅ Pago aprobado (simulación)</h1>
    <p>Gracias por tu compra. El retorno indica éxito.</p>
  <?php elseif ($status === 'failure'): ?>
    <h1>❌ Pago fallido</h1>
    <p>El pago no pudo completarse o fue rechazado.</p>
  <?php elseif ($status === 'pending'): ?>
    <h1>⌛ Pago pendiente</h1>
    <p>Tu pago está en revisión (simulación).</p>
  <?php else: ?>
    <h1>ℹ️ Estado desconocido</h1>
  <?php endif; ?>

  <hr>
  <h3>Parámetros recibidos</h3>
  <pre><?php print_r($_GET); ?></pre>
</body>
</html>
