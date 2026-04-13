<?php
session_start();
require '../config/conexion.php';

// 1. Verificación de Seguridad
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 2. Limpieza de archivos (Borrar la foto física del servidor)
        $stmt = $conexion->prepare("SELECT imagen_url FROM productos WHERE id_producto = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();

        if ($producto) {
            $ruta_imagen = "../" . $producto['imagen_url'];
            // Solo borramos si el archivo existe y no es la imagen por defecto
            if (file_exists($ruta_imagen) && $producto['imagen_url'] != 'assets/img/productos/default.png') {
                unlink($ruta_imagen);
            }
        }

        // 3. Borrar el registro de la Base de Datos
        $delete = $conexion->prepare("DELETE FROM productos WHERE id_producto = ?");
        $delete->execute([$id]);

        // Redirigir con éxito
        header("Location: productos.php?eliminado=1");
        exit();

    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: productos.php");
}