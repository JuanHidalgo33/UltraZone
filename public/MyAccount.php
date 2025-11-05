<?php
session_start();
require "forms/conection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// SELECT sin modificar columnas existentes
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
        <a href="index.php">Inicio</a> |
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>

    <section>
        <h2>Información del Usuario</h2>

        <figure>
            <img src="uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="Foto de perfil">
            <figcaption>
                <strong><?php echo htmlspecialchars($user['fullname']); ?></strong><br>
                <small>@<?php echo htmlspecialchars($user['username']); ?></small>
            </figcaption>
        </figure>

        <form action="updateAccount.php" method="post" enctype="multipart/form-data">

            <dl>
                <dt>Nombre completo</dt>
                <dd>
                    <input type="text" name="fullname" 
                        value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                </dd>

                <dt>Usuario</dt>
                <dd>
                    <input type="text" name="username" 
                        value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </dd>

                <dt>Correo</dt>
                <dd>
                    <input type="email" name="email" 
                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </dd>

                <dt>Cambiar foto de perfil</dt>
                <dd>
                    <input type="file" name="profile_image">
                </dd>
            </dl>

            <p style="text-align:center;">
                <button type="submit" class="btn-save">Guardar cambios</button>
            </p>

        </form>
    </section>

</main>

<footer>
    © <?php echo date("Y"); ?> UltraZone - Todos los derechos reservados.
</footer>

<!-- ✅ POPUP -->
<?php if (isset($_GET['update']) && $_GET['update'] === "ok"): ?>
<div class="popup-overlay" id="popup">
    <div class="popup-box">
        <h2>✅ Datos actualizados correctamente</h2>
        <button onclick="window.location.href='MyAccount.php'">Aceptar</button>
    </div>
</div>
<?php endif; ?>

</body>
</html>
