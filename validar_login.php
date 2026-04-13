<?php
// validar_login.php

// 1. Iniciar la sesión para poder guardar variables globales
session_start();

// 2. Traer nuestra conexión a la base de datos
require 'config/conexion.php';

// 3. Verificar que los datos llegaron por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recoger los datos del formulario
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    try {
        // 4. Preparar la consulta SQL de forma segura
        $sql = "SELECT id_usuario, nombre, password, id_rol FROM usuarios WHERE correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        // Obtener el resultado
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 5. Validar si el usuario existe y la contraseña coincide
        if ($usuario && $password === $usuario['password']) {

            // ¡Login exitoso! Guardamos sus datos en la sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['id_rol'] = $usuario['id_rol'];

            // 6. Redirección basada en el ROL (RBAC)
            if ($usuario['id_rol'] == 1) {
                // Es Administrador
                header("Location: admin/dashboard.php");
            } else if ($usuario['id_rol'] == 2) {
                // Es Cliente / Alumno
                header("Location: views/menu.php");
            }
            exit(); // Siempre usar exit después de un header()

        } else {
            // Error: Credenciales incorrectas, regresamos al index
            // Mandamos un parámetro por la URL para mostrar el error después
            header("Location: index.php?error=1");
            exit();
        }

    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
} else {
    // Si alguien intenta entrar a validar_login.php directamente sin enviar formulario
    header("Location: index.php");
    exit();
}
?>