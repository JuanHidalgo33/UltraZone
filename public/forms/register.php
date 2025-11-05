<?php
session_start();
require "conection.php";

$errors = [];
$input = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input['fullname'] = trim($_POST['fullname']);
    $input['username'] = trim($_POST['username']);
    $input['email']    = trim($_POST['email']);
    $input['password'] = $_POST['password'];
    $input['confirm']  = $_POST['confirm_password'];
    $input['terms']    = isset($_POST['terms']);

    if ($input['password'] !== $input['confirm']) {
        header("Location: register.php?error=pass");
        exit();
    }

    $sqlCheck = "SELECT email FROM usuarios WHERE email = ?";
    $check = sqlsrv_prepare($conn, $sqlCheck, array($input['email']));
    sqlsrv_execute($check);

    if (sqlsrv_has_rows($check)) {
        header("Location: register.php?error=email");
        exit();
    }

    $hashed = password_hash($input['password'], PASSWORD_DEFAULT);

    $sqlInsert = "INSERT INTO usuarios (email, fullname, username, passwrd) VALUES (?, ?, ?, ?)";
    $params = array($input['email'], $input['fullname'], $input['username'], $hashed);
    $stmt = sqlsrv_prepare($conn, $sqlInsert, $params);

    if ($stmt && sqlsrv_execute($stmt)) {
        header("Location: register.php?success=ok");
        exit();
    } else {
        header("Location: register.php?error=sql");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sign up.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="register-container">
    <form action="register.php" method="post">
        <h1>Create an account</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-box">
                <ul class="error-list">
                    <li>
                        <?php
                            if ($_GET['error'] === "email") echo "❌ Este email ya está registrado.";
                            else if ($_GET['error'] === "pass") echo "❌ Las contraseñas no coinciden.";
                            else echo "❌ Error en el servidor, intenta más tarde.";
                        ?>
                    </li>
                </ul>
            </div>
        <?php endif; ?>

        <label for="fullname">Full name</label>
        <input type="text" id="fullname" name="fullname" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <div class="row">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">I agree to the Terms and Conditions</label>
        </div>

        <button type="submit">Register</button>

        <p>Already have an account? <a href="login.php">Login</a></p>

        <!-- ✅ POPUP DE REGISTRO EXITOSO -->
        <?php if (isset($_GET['success']) && $_GET['success'] === "ok"): ?>
            <div class="popup-overlay" id="popup">
                <div class="popup-box">
                    <h2>✅ Registro exitoso</h2>
                    <p>¡Tu cuenta ha sido creada correctamente!</p>
                </div>
            </div>

            <script>
                document.getElementById('popup').style.display = 'flex';
                setTimeout(() => {
                    window.location.href = "login.php";
                }, 2000);
            </script>
        <?php endif; ?>

    </form>
</div>

</body>
</html>
