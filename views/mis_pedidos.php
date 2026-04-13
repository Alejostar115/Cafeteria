<?php
session_start();
require '../config/conexion.php';

// Seguridad: Si no está logueado, al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$id_user = $_SESSION['id_usuario'];

// Consultamos los pedidos activos del alumno (todo lo que NO sea 'Entregado' o 'Cancelado')
$sql = "SELECT p.*, pr.nombre as producto_nombre, pr.imagen_url 
        FROM pedidos p 
        JOIN productos pr ON p.id_producto = pr.id_producto 
        WHERE p.id_usuario = ? AND p.estado IN ('Pendiente', 'Preparando', 'Listo')
        ORDER BY p.fecha_hora DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_user]);
$mis_pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - UPAP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --bg: #f8fafc;
            --dark: #1e293b;
            --muted: #64748b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            margin: 0;
            padding-bottom: 90px;
        }

        .header-pedidos {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .orders-container {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Tarjeta de Pedido */
        .order-card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            border-left: 6px solid #cbd5e1;
        }

        /* Colores dinámicos según el estado */
        .status-pendiente {
            border-left-color: #f59e0b;
        }

        .status-preparando {
            border-left-color: #3b82f6;
        }

        .status-listo {
            border-left-color: #22c55e;
            background: #f0fdf4;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 10px;
        }

        .turno-badge {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--dark);
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        /* Estilos de las pastillas de estado */
        .bg-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .bg-preparando {
            background: #dbeafe;
            color: #1e40af;
            animation: pulse 2s infinite;
        }

        .bg-listo {
            background: #dcfce7;
            color: #166534;
        }

        .order-details {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .order-details img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
        }

        .order-info h4 {
            margin: 0 0 5px;
            color: var(--dark);
            font-size: 1rem;
        }

        .order-info p {
            margin: 0;
            color: var(--muted);
            font-size: 0.85rem;
        }

        .alert-time {
            margin-top: 15px;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .time-preparando {
            background: #eff6ff;
            color: #1e40af;
        }

        .time-listo {
            background: #dcfce7;
            color: #166534;
            font-size: 1rem;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }

        /* Navbar Inferior */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.08);
            z-index: 200;
        }

        .nav-item {
            text-align: center;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .nav-item.active {
            color: var(--primary);
        }

        .nav-item i {
            font-size: 1.3rem;
            display: block;
            margin-bottom: 3px;
        }
    </style>
</head>

<body>

    <header class="header-pedidos">
        <h2 style="margin:0; font-size: 1.4rem;">Monitor de Pedidos</h2>
        <p style="margin:5px 0 0; color:var(--muted); font-size: 0.85rem;">Sigue el estado de tu comida en tiempo real.
        </p>
    </header>

    <div class="orders-container">

        <?php if (isset($_GET['msj']) && $_GET['msj'] == 'exito'): ?>
            <div
                style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; text-align: center; border: 1px solid #bbf7d0;">
                <i class="fa-solid fa-check-circle"></i> ¡Turno generado correctamente!
            </div>
        <?php endif; ?>

        <?php foreach ($mis_pedidos as $mp): ?>
            <div class="order-card status-<?= strtolower($mp['estado']) ?>">

                <div class="order-header">
                    <div class="turno-badge">#
                        <?= $mp['numero_turno'] ?>
                    </div>
                    <div class="status-badge bg-<?= strtolower($mp['estado']) ?>">
                        <?= $mp['estado'] ?>
                    </div>
                </div>

                <div class="order-details">
                    <img src="../<?= $mp['imagen_url'] ?>" onerror="this.src='../assets/img/productos/default.png'">
                    <div class="order-info">
                        <h4>
                            <?= htmlspecialchars($mp['producto_nombre']) ?>
                        </h4>
                        <p>Cant: <strong>
                                <?= $mp['cantidad'] ?>
                            </strong> | Total: <strong style="color:var(--primary);">$
                                <?= number_format($mp['total'], 2) ?>
                            </strong></p>
                        <p style="font-size: 0.75rem; margin-top: 5px;">
                            <i
                                class="fa-solid <?= $mp['metodo_pago'] == 'Tarjeta' ? 'fa-credit-card' : 'fa-money-bill' ?>"></i>
                            Paga con
                            <?= $mp['metodo_pago'] ?>
                        </p>
                    </div>
                </div>

                <?php if ($mp['estado'] == 'Pendiente'): ?>
                    <div class="alert-time" style="background: #fef3c7; color: #92400e;">
                        <i class="fa-solid fa-hourglass-start"></i> En cola. Esperando a cocina...
                    </div>
                <?php elseif ($mp['estado'] == 'Preparando'): ?>
                    <div class="alert-time time-preparando">
                        <i class="fa-solid fa-fire-burner"></i> Cocinando... Aprox.
                        <?= $mp['tiempo_espera'] ?> min.
                    </div>
                <?php elseif ($mp['estado'] == 'Listo'): ?>
                    <div class="alert-time time-listo">
                        <i class="fa-solid fa-bell"></i> ¡LISTO! Pasa a la barra a recoger.
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

        <?php if (empty($mis_pedidos)): ?>
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fa-solid fa-receipt" style="font-size: 3rem; color: #cbd5e1;"></i>
                <h3 style="color: var(--dark); margin: 15px 0 5px;">No hay pedidos activos</h3>
                <p style="color: var(--muted); font-size: 0.9rem;">Cuando ordenes algo en la cafetería, tu turno aparecerá
                    aquí.</p>
                <a href="menu.php"
                    style="display: inline-block; margin-top: 15px; background: var(--primary); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;">Ver
                    menú</a>
            </div>
        <?php endif; ?>

    </div>

    <nav class="bottom-nav">
        <a href="menu.php" class="nav-item">
            <i class="fa-solid fa-utensils"></i>Menú
        </a>
        <a href="mis_pedidos.php" class="nav-item active">
            <i class="fa-solid fa-receipt"></i>Pedidos
        </a>
        <a href="encuesta.php" class="nav-item">
            <i class="fa-solid fa-square-poll-vertical"></i>Votar
        </a>
        <a href="../logout.php" class="nav-item">
            <i class="fa-solid fa-right-from-bracket"></i>Salir
        </a>
    </nav>

</body>

</html>