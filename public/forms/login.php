<?php
session_start();
require "conection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass  = $_POST['pass'];

    $sql = "SELECT id, email, password FROM usuarios WHERE email = ?";
    $params = array($email);

    $stmt = sqlsrv_prepare($conn, $sql, $params);
    if ($stmt === false || sqlsrv_execute($stmt) === false) {
        die("Error: " . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email']   = $user['email'];
            header("Location: ../MyAccount.php");
            exit();
        } else {
            header("Location: login.php?error=contrasena_incorrecta");
            exit();
        }
    } else {
        header("Location: login.php?error=usuario_no_encontrado");
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
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="pass" required>

        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>

        <div class="remember-me">
            <input type="checkbox" id="remember" name="remember" 
                   <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
            <label for="remember">Remember Me</label>
        </div>
        
        <div class="register-link">
            <span>Don't have an account? <a href="register.php">Register</a></span>
        </div>

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