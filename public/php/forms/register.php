<?php
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

    $sqlInsert = "INSERT INTO usuarios (email, fullname, username, passwrd) VALUES (?, ?, ?, ?)";
    $params = array($input['email'], $input['fullname'], $input['username'], $input['password']);
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
    <link href="../../assets/img/FavIcon.png" rel="icon">
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/sign up.css?v=<?php echo time(); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
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
                            if ($_GET['error'] === "email") echo "Este email ya esta registrado.";
                            else if ($_GET['error'] === "pass") echo "Las contrasenas no coinciden.";
                            else echo "Error en el servidor, intenta mas tarde.";
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

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <div class="terms">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">Acepto los términos y condiciones</label>
        </div>

        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>
