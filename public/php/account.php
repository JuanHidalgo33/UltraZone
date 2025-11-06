<?php
session_start();
require __DIR__ . '/forms/conection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: forms/login.php?info=login_required');
  exit();
}

$userID = (int)$_SESSION['user_id'];

$sql = "SELECT fullname, username, email, profile_image FROM usuarios WHERE userID = ?";
$stmt = sqlsrv_prepare($conn, $sql, [$userID]);
if (!$stmt || !sqlsrv_execute($stmt)) {
  die('Error consultando usuario');
}
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ?: [];

$isAdmin = false;
$qRol = sqlsrv_prepare($conn, "SELECT rol AS r FROM usuarios WHERE userID=?", [$userID]);
if ($qRol && sqlsrv_execute($qRol)) {
  $r = sqlsrv_fetch_array($qRol, SQLSRV_FETCH_ASSOC);
  $isAdmin = (strtolower((string)($r['r'] ?? 'user')) === 'admin');
}

$raw = $user['profile_image'] ?? '';
$uploadsDir = realpath(__DIR__ . '/../uploads');
$hasCustom = ($raw && $uploadsDir && file_exists($uploadsDir . DIRECTORY_SEPARATOR . $raw));
$profileUrl = $hasCustom ? ('../uploads/' . htmlspecialchars($raw)) : '../assets/img/default_profile.svg';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Mi Cuenta</title>
  <link href="../assets/img/FavIcon.png" rel="icon" />
  <link rel="stylesheet" href="../css/account.css?v=<?php echo time(); ?>" />
</head>
<body>
  <header class="acc-header">
    <h1>Mi Cuenta</h1>
    <nav class="acc-breadcrumb">
      <a href="../index.html">Inicio</a>
      <span>·</span>
      <a href="logout.php">Cerrar sesión</a>
    </nav>
  </header>

  <main class="acc-container">
    <section class="acc-card">
      <h2>Información del Usuario</h2>
      <div class="acc-profile">
        <img class="acc-avatar" src="<?php echo $profileUrl; ?>" alt="Foto de perfil" />
        <div class="acc-identity">
          <div class="acc-name"><?php echo htmlspecialchars($user['fullname'] ?? ''); ?></div>
          <div class="acc-username">@<?php echo htmlspecialchars($user['username'] ?? ''); ?></div>
        </div>
      </div>

      <form id="acc-remove-form" action="account_update.php" method="post" class="acc-remove">
        <input type="hidden" name="remove_photo" value="1" />
        <button type="button" id="acc-remove-photo" class="btn btn-ghost" <?php echo $hasCustom ? '' : 'disabled'; ?>>Eliminar foto de perfil</button>
      </form>

      <form id="acc-form" class="acc-form" action="account_update.php" method="post" enctype="multipart/form-data">
        <div class="acc-row">
          <label>Nombre completo</label>
          <div class="acc-value" data-key="fullname"><?php echo htmlspecialchars($user['fullname'] ?? ''); ?></div>
          <input class="acc-input" type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" />
        </div>
        <div class="acc-row">
          <label>Usuario</label>
          <div class="acc-value" data-key="username">@<?php echo htmlspecialchars($user['username'] ?? ''); ?></div>
          <input class="acc-input" type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" />
        </div>
        <div class="acc-row">
          <label>Correo</label>
          <div class="acc-value" data-key="email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
          <input class="acc-input" type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" />
        </div>
        <div class="acc-row">
          <label>Cambiar foto de perfil</label>
          <div class="acc-value">—</div>
          <input class="acc-input" type="file" name="profile_image" accept="image/*" />
        </div>

        <div class="acc-actions">
          <button type="button" id="acc-edit" class="btn">Editar</button>
          <button type="button" id="acc-cancel" class="btn btn-ghost" disabled>Cancelar</button>
          <button type="submit" id="acc-save" class="btn btn-primary" disabled>Guardar cambios</button>
        </div>
      </form>
    </section>

    <?php if ($isAdmin): ?>
    <section class="acc-card" id="acc-admin">
      <h2>Gestionar usuarios</h2>
      <div class="acc-admin-desc">Solo visible para administradores.</div>
      <div class="acc-admin-actions">
        <input type="text" id="u-search" class="acc-search" placeholder="Buscar por nombre, usuario, correo o rol" />
        <button type="button" class="btn" id="u-btn-search">Buscar</button>
        <button type="button" class="btn" id="u-btn-create">Crear</button>
        <button type="button" class="btn" id="u-btn-edit">Editar</button>
        <button type="button" class="btn btn-ghost" id="u-btn-delete">Eliminar</button>
        <button type="button" class="btn" id="u-btn-refresh">Recargar</button>
      </div>
      <div class="acc-table-wrap">
        <table class="acc-table" id="acc-users-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Usuario</th>
              <th>Correo</th>
              <th>Rol</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="5">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="acc-card" id="acc-admin-products">
      <h2>Gestionar productos</h2>
      <div class="acc-admin-desc">CRUD de productos (solo administradores).</div>
      <div class="acc-admin-actions">
        <input type="text" id="p-search" class="acc-search" placeholder="Buscar por nombre o categoría" />
        <button type="button" class="btn" id="p-btn-search">Buscar</button>
        <button type="button" class="btn" id="p-btn-create">Crear</button>
        <button type="button" class="btn" id="p-btn-edit">Editar</button>
        <button type="button" class="btn btn-ghost" id="p-btn-delete">Eliminar</button>
        <button type="button" class="btn" id="p-btn-refresh">Recargar</button>
      </div>
      <div class="acc-table-wrap">
        <table class="acc-table" id="acc-products-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Precio (COP)</th>
              <th>Imagen URL</th>
              <th>Categoría</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="5">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>
    <footer class="acc-footer">© <?php echo date('Y'); ?> UltraZone</footer>
  </main>

  <!-- Popup éxito actualización -->
  <div id="acc-popup" class="acc-overlay" style="display:none;">
    <div class="acc-modal">
      <h3>Cambios guardados</h3>
      <p>La información de tu cuenta se actualizó correctamente.</p>
      <button id="acc-popup-ok" class="btn btn-primary" type="button">Aceptar</button>
    </div>
  </div>

  <script src="../assets/js/account.js?v=<?php echo time(); ?>"></script>
</body>
</html>
