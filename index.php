<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafetería UPAP</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body class="login-body">
    <div class="login-container">
        <img src="assets/img/logo_upap.png" alt="Logo UPAP" class="login-logo">

        <h2>Sistema de Cafetería</h2>

        <form action="validar_login.php" method="POST">
            <div class="input-group">
                <label>Correo Institucional</label>
                <input type="email" name="correo" required placeholder="usuario@upap.mx">
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" required placeholder="********">
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>

        <p class="footer-text">© 2026 Universidad Politécnica de Apodaca</p>
    </div>
</body>

</html>