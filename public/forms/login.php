<?php
session_start();
require "conection.php";

$correoGuardado = isset($_COOKIE['recordar_usuario']) ? $_COOKIE['recordar_usuario'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass  = trim($_POST['passwrd']);

    $sql = "SELECT userID, email, passwrd FROM usuarios WHERE email = ? AND passwrd = ?";
    $params = array($email, $pass);

    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if ($stmt === false || sqlsrv_execute($stmt) === false) {
        die("Error SQL: " . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $_SESSION['user_id'] = $user['userID'];
        $_SESSION['email']   = $user['email'];

        if (isset($_POST['remember'])) {
            setcookie("recordar_usuario", $email, time() + (60*60*24*30), "/");
        } else {
            setcookie("recordar_usuario", "", time() - 3600, "/");
        }

        header("Location: login.php?success=login_ok");
        exit();
    } else {
        header("Location: login.php?error=datos_incorrectos");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Form</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css?v=<?php echo time(); ?>">
</head>
<body>

<form action="login.php" method="post">
    <div class="login-container">

        <label for="email">Email:</label>

        <input type="text" id="email" name="email" 
               value="<?php 
                    echo isset($_POST['email']) 
                    ? htmlspecialchars($_POST['email']) 
                    : htmlspecialchars($correoGuardado);
               ?>" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="passwrd" required>

        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>

        <div class="remember-me">

            <input type="checkbox" id="remember" name="remember"
                   <?php echo isset($_COOKIE['recordar_usuario']) ? 'checked' : ''; ?>>
            <label for="remember">Remember Me</label>
        </div>
        
        <div class="register-link">
            <span>Don't have an account? <a href="register.php">Register</a></span>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] === "login_ok"): ?>
            <div class="popup-overlay" id="popup">
                <div class="popup-box">
                    <h2>✅ Inicio de sesión exitoso</h2>
                </div>
            </div>

            <script>
                document.getElementById('popup').style.display = 'flex';

                setTimeout(() => {
                    window.location.href = "../MyAccount.php";
                }, 2000);
            </script>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-box">
                <p class="error-msg">
                    <?php 
                        echo $_GET['error'] === "usuario_no_encontrado" 
                            ? "❌ Usuario no encontrado" 
                            : "❌ Contraseña incorrecta";
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="submit-button">
            <button type="submit">Login</button>
        </div>

    </div>
</form>

</body>
</html>
