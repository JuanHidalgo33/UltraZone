<?php
session_start();
require "forms/conection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: forms/login.php");
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
$profilePic = $user['profile_image'] ? $user['profile_image'] : "default.png";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cuenta</title>
    <link rel="stylesheet" href="css/MyAccount.css">
</head>
<body>

<header>
    <h1>Mi Cuenta</h1>
    <nav>
        <a href="index.html">Inicio</a> |
        <a href="logout.php">Cerrar sesion</a>
    </nav>
    </header>

<main>

    <section>
        <h2>Informacion del Usuario</h2>

        <figure>
            <img src="uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="Foto de perfil">
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
                </dd>

                <dt>Usuario</dt>
                <dd>
                    <input type="text" name="username" id="username"
                        value="<?php echo htmlspecialchars($user['username']); ?>" required disabled>
                </dd>

                <dt>Correo</dt>
                <dd>
                    <input type="email" name="email" id="email"
                        value="<?php echo htmlspecialchars($user['email']); ?>" required disabled>
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

<script src="assets/js/account.js?v=<?php echo time(); ?>"></script>

</body>
</html>
