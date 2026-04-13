<?php
session_start();
require '../config/conexion.php';
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit();
}

// Consulta de productos
$sql = "SELECT p.*, c.nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria ORDER BY p.id_producto DESC";
$productos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inventario - Administración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px;">
            <div>
                <h1 style="margin:0;">Inventario de Productos</h1>
                <p style="color:var(--text-muted);">Gestiona el menú y el stock disponible.</p>
            </div>
            <a href="nuevo_producto.php" class="btn-primary"
                style="background:var(--primary); color:white; padding:12px 24px; border-radius:8px; text-decoration:none; font-weight:bold; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-plus"></i> Agregar Producto
            </a>
        </div>

        <div class="glass-card">
            <?php if (empty($productos)): ?>
                <div style="text-align:center; padding:50px;">
                    <i class="fa-solid fa-box-open" style="font-size:4rem; color:#cbd5e1; margin-bottom:20px;"></i>
                    <h3>No hay productos registrados</h3>
                    <p>Comienza agregando platillos o bebidas al menú de la cafetería.</p>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td><img src="../<?= $p['imagen_url'] ?>"
                                        style="width:50px; height:50px; object-fit:cover; border-radius:8px;"></td>
                                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                                <td><span
                                        style="background:#f1f5f9; padding:4px 8px; border-radius:4px; font-size:0.8rem;"><?= $p['nombre_categoria'] ?></span>
                                </td>
                                <td>$<?= number_format($p['precio'], 2) ?></td>
                                <td><?= $p['stock'] ?> pz</td>
                                <td>
                                    <a href="editar_producto.php?id=<?= $p['id_producto'] ?>"
                                        style="color:var(--primary); margin-right:15px;"><i class="fa-solid fa-pen"></i></a>
                                    <a href="eliminar_producto.php?id=<?= $p['id_producto'] ?>" style="color:#ef4444;"
                                        onclick="return confirm('¿Eliminar producto?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>