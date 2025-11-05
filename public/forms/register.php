<?php
// public/forms/register.php
session_start();
require "conection.php";

$errors = [];
$input = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar y limpiar datos
    $input['fullname'] = trim($_POST['fullname']);
    $input['username'] = trim($_POST['username']);
    $input['email']    = trim($_POST['email']);
    $input['password'] = $_POST['password'];
    $input['confirm']  = $_POST['confirm_password'];
    $input['terms']    = isset($_POST['terms']);

    // Validaciones
    if (empty($input['fullname'])) $errors[] = "El nombre es obligatorio.";
    if (empty($input['username'])) $errors[] = "El usuario es obligatorio.";
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Email inválido.";
    if (strlen($input['password']) < 8) $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    if (!preg_match("/[A-Za-z]/", $input['password']) || !preg_match("/\d/", $input['password'])) {
        $errors[] = "La contraseña debe tener letras y números.";
    }
    if ($input['password'] !== $input['confirm']) $errors[] = "Las contraseñas no coinciden.";
    if (!$input['terms']) $errors[] = "Debes aceptar los términos.";

    // Si no hay errores, insertar en SQL Server
    if (empty($errors)) {
        $hashed = password_hash($input['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre_completo, username, email, password) VALUES (?, ?, ?, ?)";
        $params = [$input['fullname'], $input['username'], $input['email'], $hashed];

        $stmt = sqlsrv_prepare($conn, $sql, $params);
        if ($stmt && sqlsrv_execute($stmt)) {
            header("Location: login.php?success=1");
            exit();
        } else {
            $sql_errors = sqlsrv_errors();
            foreach ($sql_errors as $e) {
                if ($e['code'] == 2601 || $e['code'] == 2627) {
                    if (strpos($e['message'], 'email') !== false) {
                        $errors[] = "Este email ya está registrado.";
                    } elseif (strpos($e['message'], 'username') !== false) {
                        $errors[] = "Este usuario ya existe.";
                    }
                }
            }
            if (empty($errors)) $errors[] = "Error del servidor. Intenta más tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sign up.css">
</head>
<body>

<div class="register-container">
    <form action="register.php" method="post">
        <h1>Create an account</h1>

        <!-- MENSAJE DE ÉXITO -->
        <?php if (isset($_GET['success'])): ?>
            <div class="success-box">
                ¡Registro exitoso! <a href="login.php" style="color:#fff; text-decoration:underline;">Iniciar sesión</a>
            </div>
        <?php endif; ?>

        <!-- ERRORES -->
        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <ul class="error-list">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <label for="fullname">Full name</label>
        <input type="text" id="fullname" name="fullname" 
               value="<?php echo htmlspecialchars($input['fullname'] ?? ''); ?>" 
               required autocomplete="name">

        <label for="username">Username</label>
        <input type="text" id="username" name="username" 
               value="<?php echo htmlspecialchars($input['username'] ?? ''); ?>" 
               required autocomplete="username">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" 
               value="<?php echo htmlspecialchars($input['email'] ?? ''); ?>" 
               required autocomplete="email">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8"
               pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password"
               title="At least 8 characters, including letters and numbers.">

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">

        <div class="remember-me">
            <input type="checkbox" id="remember" name="remember" 
                   <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
            <label for="remember">Remember me</label>
        </div>

        <div class="row">
            <input type="checkbox" id="terms" name="terms" required 
                   <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
            <label for="terms">I agree to the Terms and Conditions</label>
        </div>

        <button type="submit">Register</button>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>

</body>
</html>