<?php
session_start();
require '../config/conexion.php';

// Seguridad: Solo administradores
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit();
}

// 1. Lógica para ACTUALIZAR ESTADO Y TIEMPO (Notificación al alumno)
if (isset($_POST['actualizar_pedido'])) {
    $id_ped = $_POST['id_pedido'];
    $nuevo_estado = $_POST['estado'];
    $tiempo = $_POST['tiempo_espera'];

    $stmt = $conexion->prepare("UPDATE pedidos SET estado = ?, tiempo_espera = ? WHERE id_pedido = ?");
    $stmt->execute([$nuevo_estado, $tiempo, $id_ped]);
    header("Location: pedidos.php?msj=actualizado");
    exit();
}

// 2. Lógica para FINALIZAR (Entregar y archivar)
if (isset($_GET['finalizar'])) {
    $id_ped = $_GET['finalizar'];
    $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'Entregado' WHERE id_pedido = ?");
    $stmt->execute([$id_ped]);
    header("Location: pedidos.php?msj=entregado");
    exit();
}

// 3. Consulta de pedidos activos (Todo lo que no sea 'Entregado')
$sql = "SELECT p.*, u.nombre as cliente 
        FROM pedidos p 
        JOIN usuarios u ON p.id_usuario = u.id_usuario 
        WHERE p.estado IN ('Pendiente', 'Preparando', 'Listo') 
        ORDER BY p.fecha_hora ASC";
$pedidos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Monitor de Pedidos - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .grid-pedidos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        .pedido-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            position: relative;
            transition: 0.3s;
            border-top: 6px solid #f59e0b;
        }

        .metodo-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Colores por estado */
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 5px;
            font-weight: bold;
        }

        .st-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .st-preparando {
            background: #e0e7ff;
            color: #3730a3;
        }

        .st-listo {
            background: #dcfce7;
            color: #166534;
        }

        .form-update {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-update select,
        .form-update input {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 0.9rem;
        }

        .btn-update {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-entregar {
            background: #1e293b;
            color: white;
            text-decoration: none;
            display: block;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            font-weight: bold;
        }

        .btn-entregar:hover {
            background: #10b981;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header style="margin-bottom: 30px;">
            <h1>Monitor de Cocina y Caja</h1>
            <p style="color:var(--text-muted);">Actualiza tiempos de espera y notifica a los alumnos.</p>
        </header>

        <div class="grid-pedidos">
            <?php foreach ($pedidos as $ped): ?>
                <div class="pedido-card"
                    style="border-top-color: <?= $ped['metodo_pago'] == 'Tarjeta' ? '#3b82f6' : '#22c55e' ?>;">

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span style="font-size: 1.3rem; font-weight: 800;">Turno #<?= $ped['numero_turno'] ?></span>
                        <span class="status-badge st-<?= strtolower($ped['estado']) ?>">
                            <?= strtoupper($ped['estado']) ?>
                        </span>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <span class="metodo-badge"
                            style="background: <?= $ped['metodo_pago'] == 'Tarjeta' ? '#dbeafe; color:#1e40af;' : '#dcfce7; color:#166534;' ?>">
                            <i
                                class="fa-solid <?= $ped['metodo_pago'] == 'Tarjeta' ? 'fa-credit-card' : 'fa-money-bill' ?>"></i>
                            <?= strtoupper($ped['metodo_pago']) ?>
                        </span>
                        <small style="color: #64748b; margin-left: 10px;">
                            <i class="fa-regular fa-clock"></i> <?= date('H:i', strtotime($ped['fecha_hora'])) ?>
                        </small>
                    </div>

                    <div style="padding: 10px 0; border-top: 1px dashed #e2e8f0;">
                        <p style="margin: 0; font-size: 0.9rem;"><strong>Cliente:</strong> <?= $ped['cliente'] ?></p>
                        <p style="margin: 5px 0; font-size: 1.1rem; color: #1e293b;"><strong>Total:
                                $<?= number_format($ped['total'], 2) ?></strong></p>

                        <?php if ($ped['metodo_pago'] == 'Efectivo'): ?>
                            <div
                                style="font-size: 0.85rem; color: #166534; background: #f0fdf4; padding: 5px; border-radius: 4px;">
                                <strong>Cambio: $<?= number_format($ped['pago_con'] - $ped['total'], 2) ?></strong>
                                <span style="font-size: 0.7rem;">(Pagó con $<?= number_format($ped['pago_con'], 2) ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" class="form-update">
                        <input type="hidden" name="id_pedido" value="<?= $ped['id_pedido'] ?>">

                        <div style="display:flex; gap:10px; align-items:center;">
                            <div style="flex:1;">
                                <label style="font-size:0.75rem; font-weight:bold; color:#64748b;">⏳ Minutos:</label>
                                <input type="number" name="tiempo_espera" value="<?= $ped['tiempo_espera'] ?>" min="0"
                                    style="width:100%;">
                            </div>
                            <div style="flex:2;">
                                <label style="font-size:0.75rem; font-weight:bold; color:#64748b;">🚀 Estado:</label>
                                <select name="estado" style="width:100%;">
                                    <option value="Pendiente" <?= $ped['estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente
                                    </option>
                                    <option value="Preparando" <?= $ped['estado'] == 'Preparando' ? 'selected' : '' ?>>
                                        Preparando
                                    </option>
                                    <option value="Listo" <?= $ped['estado'] == 'Listo' ? 'selected' : '' ?>>¡LISTO!</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="actualizar_pedido" class="btn-update">
                            Actualizar Alumno
                        </button>
                    </form>

                    <?php if ($ped['estado'] == 'Listo'): ?>
                        <a href="pedidos.php?finalizar=<?= $ped['id_pedido'] ?>" class="btn-entregar"
                            onclick="return confirm('¿Confirmas que el alumno ya recogió su pedido?')">
                            <i class="fa-solid fa-box-open"></i> ENTREGAR PEDIDO
                        </a>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

            <?php if (empty($pedidos)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                    <i class="fa-solid fa-mug-hot" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h3>Bandeja de entrada vacía</h3>
                    <p style="color: #64748b;">No hay órdenes pendientes en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>