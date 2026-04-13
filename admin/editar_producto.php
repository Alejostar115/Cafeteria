<?php
session_start();
require '../config/conexion.php';

// Seguridad: Solo administradores
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit();
}

// 1. Obtener los datos del producto a editar
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
        header("Location: productos.php");
        exit();
    }
} else {
    header("Location: productos.php");
    exit();
}

// 2. Obtener categorías para el select
$categorias = $conexion->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);

// 3. Procesar la actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_cat = $_POST['id_categoria'];
    $desc = $_POST['descripcion'];
    $img_final = $_POST['img_actual']; // Por si no suben una nueva

    // Lógica para cambio de imagen
    if (isset($_FILES['nueva_img']) && $_FILES['nueva_img']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['nueva_img']['name'], PATHINFO_EXTENSION));
        $nuevo_nombre = uniqid('upd_', true) . "." . $ext;
        $ruta_destino = "../assets/img/productos/" . $nuevo_nombre;

        if (move_uploaded_file($_FILES['nueva_img']['tmp_name'], $ruta_destino)) {
            // Borrar la imagen anterior si no es la default
            if ($img_final != 'assets/img/productos/default.png' && file_exists("../" . $img_final)) {
                unlink("../" . $img_final);
            }
            $img_final = "assets/img/productos/" . $nuevo_nombre;
        }
    }

    $sql_upd = "UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, imagen_url=?, id_categoria=? WHERE id_producto=?";
    $stmt_upd = $conexion->prepare($sql_upd);

    if ($stmt_upd->execute([$nombre, $desc, $precio, $stock, $img_final, $id_cat, $id])) {
        header("Location: productos.php?msj=editado");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Producto - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div style="margin-bottom: 30px;">
            <h1><i class="fa-solid fa-pen-to-square"></i> Editar Información</h1>
            <p style="color:var(--text-muted);">Modifica los detalles del producto #<?= $id ?></p>
        </div>

        <div class="glass-card" style="max-width: 800px;">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="img_actual" value="<?= $p['imagen_url'] ?>">

                <div style="display: flex; gap: 30px; flex-wrap: wrap;">

                    <div
                        style="flex: 1; min-width: 250px; text-align: center; background: #f8fafc; padding: 20px; border-radius: 12px;">
                        <label style="display:block; margin-bottom:10px; font-weight:bold; color:#64748b;">Imagen
                            Actual</label>
                        <img src="../<?= $p['imagen_url'] ?>"
                            style="width:100%; max-width:200px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1);">
                        <div style="margin-top:20px;">
                            <label style="display:block; font-size:0.8rem; margin-bottom:5px;">¿Cambiar foto?</label>
                            <input type="file" name="nueva_img" accept="image/*" style="font-size:0.8rem;">
                        </div>
                    </div>

                    <div style="flex: 2; min-width: 300px;">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:bold;">Nombre del
                                Producto</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($p['nombre']) ?>" required
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                        </div>

                        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div style="flex: 1;">
                                <label style="display:block; margin-bottom:5px; font-weight:bold;">Precio ($)</label>
                                <input type="number" step="0.01" name="precio" value="<?= $p['precio'] ?>" required
                                    style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                            </div>
                            <div style="flex: 1;">
                                <label style="display:block; margin-bottom:5px; font-weight:bold;">Stock</label>
                                <input type="number" name="stock" value="<?= $p['stock'] ?>" required
                                    style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:bold;">Categoría</label>
                            <select name="id_categoria" required
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>" <?= $cat['id_categoria'] == $p['id_categoria'] ? 'selected' : '' ?>>
                                        <?= $cat['nombre_categoria'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:bold;">Descripción</label>
                            <textarea name="descripcion" rows="3"
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;"><?= htmlspecialchars($p['descripcion']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button type="submit"
                        style="flex:1; background:var(--primary); color:white; border:none; padding:15px; border-radius:8px; font-weight:bold; cursor:pointer;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                    <a href="productos.php"
                        style="flex:1; background:#e2e8f0; color:#475569; text-decoration:none; text-align:center; padding:15px; border-radius:8px; font-weight:bold;">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>