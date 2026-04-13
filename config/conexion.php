<?php
// config/conexion.php
$host = "localhost";
$db_name = "cafeteria_upap";
$username = "root";
$password = ""; // En XAMPP por defecto va vacío

try {
    $conexion = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->exec("set names utf8");
} catch (PDOException $exception) {
    echo "Error de conexión: " . $exception->getMessage();
}
?>