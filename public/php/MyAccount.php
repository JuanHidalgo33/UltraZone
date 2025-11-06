<?php
session_start();
require "forms/conection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: forms/login.php?info=login_required");
    exit();
}

$userID = $_SESSION['user_id'];

$sql = "SELECT fullname, username, email, profile_image FROM usuarios WHERE userID = ?";
$params = array($userID);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$rawProfile = isset($user['profile_image']) ? $user['profile_image'] : '';
$uploadsDir = realpath(__DIR__ . '/../uploads');
$hasCustom = ($rawProfile && $uploadsDir && file_exists($uploadsDir . DIRECTORY_SEPARATOR . $rawProfile));
$profileUrl = $hasCustom ? ('../uploads/' . htmlspecialchars($rawProfile)) : '../assets/img/default_profile.svg';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cuenta</title>
    <link href="../assets/img/FavIcon.png" rel="icon">
    <link rel="stylesheet" href="../css/MyAccount.css?v=<?php echo time(); ?>">
</head>
<body>

<header>
    <h1>Mi Cuenta</h1>
    <nav>
        <a href="../index.html">Inicio</a> |
        <a href="logout.php">Cerrar sesion</a>
    </nav>
    </header>

<main>

    <section>
        <h2>Informacion del Usuario</h2>

        <figure>
            <img src="<?php echo $profileUrl; ?>" alt="Foto de perfil" width="128" height="128" style="border-radius:50%; object-fit:cover; background:#fff;">
            <figcaption>
                <strong><?php echo htmlspecialchars($user['fullname']); ?></strong><br>
                <small>@<?php echo htmlspecialchars($user['username']); ?></small>
            </figcaption>
        </figure>

        <form action="updateAccount.php" method="post" enctype="multipart/form-data" id="account-form">

            <dl>
                <dt>Nombre completo</dt>
                <dd>
                    <input type="text" name="fullname" id="fullname"
                        value="<?php echo htmlspecialchars($user['fullname']); ?>" required disabled>
                    <span class="read-value" data-for="fullname"><?php echo htmlspecialchars($user['fullname']); ?></span>
                </dd>

                <dt>Usuario</dt>
                <dd>
                    <input type="text" name="username" id="username"
                        value="<?php echo htmlspecialchars($user['username']); ?>" required disabled>
                    <span class="read-value" data-for="username">@<?php echo htmlspecialchars($user['username']); ?></span>
                </dd>

                <dt>Correo</dt>
                <dd>
                    <input type="email" name="email" id="email"
                        value="<?php echo htmlspecialchars($user['email']); ?>" required disabled>
                    <span class="read-value" data-for="email"><?php echo htmlspecialchars($user['email']); ?></span>
                </dd>

                <dt>Cambiar foto de perfil</dt>
                <dd>
                    <input type="file" name="profile_image" id="profile_image" disabled>
                </dd>
            </dl>

            <p style="text-align:center; display:flex; gap:12px; justify-content:center;">
                <button type="button" id="btn-edit">Editar</button>
                <button type="button" id="btn-cancel" disabled>Cancelar</button>
                <button type="submit" class="btn-save" id="btn-save" disabled>Guardar cambios</button>
            </p>

        </form>
    </section>

</main>

<footer>
    <?php echo date('Y'); ?> UltraZone - Todos los derechos reservados.
</footer>

<div class="popup-overlay" id="popup-update">
    <div class="popup-box">
        <h2>Datos actualizados correctamente</h2>
        <button id="popup-update-accept">Aceptar</button>
    </div>
    
</div>

<script src="../assets/js/account.js?v=<?php echo time(); ?>"></script>

</body>
</html>
