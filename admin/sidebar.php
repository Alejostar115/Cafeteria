<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-utensils"></i>
        <span>Café UPAP</span>
    </div>
    <div class="user-info">
        <small>Bienvenido,</small>
        <strong>
            <?php echo $_SESSION['nombre']; ?>
        </strong>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"
            class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i> Inicio
        </a>
        <a href="productos.php"
            class="<?php echo basename($_SERVER['PHP_SELF']) == 'productos.php' || basename($_SERVER['PHP_SELF']) == 'editar_producto.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-boxes-stacked"></i> Inventario
        </a>
        <a href="pedidos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-receipt"></i> Pedidos
        </a>
        <a href="../logout.php" class="logout-link">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
        </a>
    </nav>
</div>