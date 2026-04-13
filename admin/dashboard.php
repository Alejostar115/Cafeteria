<?php
session_start();
require '../config/conexion.php';

// Seguridad: Solo administradores
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit();
}

// --- CONSULTAS PARA LOS INDICADORES (KPIS) ---
// 1. Pedidos pendientes
$total_pendientes = $conexion->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'Pendiente'")->fetchColumn();

// 2. Ventas de HOY
$total_ventas = $conexion->query("SELECT SUM(total) FROM pedidos WHERE DATE(fecha_hora) = CURDATE() AND estado = 'Entregado'")->fetchColumn() ?: 0;

// 3. Productos activos
$total_productos = $conexion->query("SELECT COUNT(*) FROM productos")->fetchColumn();

// 4. NUEVO: Total de Sugerencias/Encuestas recibidas
$total_sugerencias = $conexion->query("SELECT COUNT(*) FROM sugerencias_menu")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Cafetería</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>Resumen del Día</h1>
            <p style="color: #64748b;">Bienvenido al centro de mando de la cafetería.</p>
        </header>

        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">

            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="stat-data">
                    <small>Pendientes</small>
                    <h2><?php echo $total_pendientes; ?></h2>
                    <a href="pedidos.php" class="stat-link">Ver pedidos</a>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div class="stat-data">
                    <small>Ventas Hoy</small>
                    <h2>$<?php echo number_format($total_ventas, 2); ?></h2>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #6366f1;">
                <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div class="stat-data">
                    <small>Productos</small>
                    <h2><?php echo $total_productos; ?></h2>
                    <a href="productos.php" class="stat-link">Inventario</a>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #ec4899;">
                <div class="stat-icon" style="background: #fce7f3; color: #ec4899;">
                    <i class="fa-solid fa-comment-dots"></i>
                </div>
                <div class="stat-data">
                    <small>Sugerencias</small>
                    <h2><?php echo $total_sugerencias; ?></h2>
                    <a href="sugerencias.php" class="stat-link">Ver encuestas</a>
                </div>
            </div>

        </div>


    </main>

</body>

</html>