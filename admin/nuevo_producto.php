<?php
session_start();
require '../config/conexion.php';

// Seguridad: Solo administradores
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit();
}

// 1. Obtener categorías para el menú desplegable
$stmt_cat = $conexion->query("SELECT * FROM categorias");
$categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// 2. Procesar el formulario al enviar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'];
    $descripcion = $_POST['descripcion'];
    $imagen_url = "assets/img/productos/default.png"; // Imagen base

    // Lógica para subir la imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $nuevo_nombre = uniqid('prod_', true) . "." . $ext;
        $ruta_destino = "../assets/img/productos/" . $nuevo_nombre;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen_url = "assets/img/productos/" . $nuevo_nombre;
        }
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen_url, id_categoria) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    if ($stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen_url, $id_categoria])) {
        header("Location: productos.php?msj=agregado");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div style="margin-bottom: 30px;">
            <h1><i class="fa-solid fa-plus-circle"></i> Agregar Nuevo Producto</h1>
            <p style="color:var(--text-muted);">Completa los datos para añadir un platillo al menú.</p>
        </div>

        <div class="glass-card" style="max-width: 700px;">
            <form action="nuevo_producto.php" method="POST" enctype="multipart/form-data">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="grid-column: span 2;">
                        <label style="display:block; margin-bottom:8px; font-weight:bold;">Nombre del Producto</label>
                        <input type="text" name="nombre" required
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:bold;">Categoría</label>
                        <select name="id_categoria" required
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                            <option value="">Selecciona...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>">

                                    <?= $cat['nombre_categoria'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:bold;">Precio ($)</label>
                        <input type="number" step="0.01" name="precio" required
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:bold;">Stock Inicial</label>
                        <input type="number" name="stock" required
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:bold;">Imagen</label>
                        <input type="file" name="imagen" accept="image/*" required style="width:100%; padding:8px;">
                    </div>

                    <div style="grid-column: span 2;">
                        <label style="display:block; margin-bottom:8px; font-weight:bold;">Descripción</label>
                        <textarea name="descripcion" rows="3"
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></textarea>
                    </div>
                </div>

                <div style="margin-top:30px; display:flex; gap:15px;">
                    <button type="submit"
                        style="background:var(--primary); color:white; border:none; padding:12px 25px; border-radius:8px; cursor:pointer; font-weight:bold; flex:1;">
                        <i class="fa-solid fa-save"></i> Guardar Producto
                    </button>
                    <a href="productos.php"
                        style="background:#64748b; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; text-align:center; flex:1;">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </main>
</body>

</html>