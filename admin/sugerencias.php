<?php
session_start();
require '../config/conexion.php';
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) { header("Location: ../index.php"); exit(); }

// Obtener las sugerencias más recientes
$sql = "SELECT s.*, u.nombre 
        FROM sugerencias_menu s 
        JOIN usuarios u ON s.id_usuario = u.id_usuario 
        ORDER BY s.fecha DESC";
$sugerencias = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <title>Sugerencias - Admin</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div style="margin-bottom:30px;">
            <h1><i class="fa-solid fa-comments"></i> Buzón de Sugerencias</h1>
            <p style="color:var(--text-muted);">Lo que los alumnos quieren comer la próxima semana.</p>
        </div>

        <div class="glass-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Alumno</th>
                        <th>Sugerencia de Platillo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sugerencias as $s): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= date('d/m H:i', strtotime($s['fecha'])) ?></td>
                        <td style="font-weight:bold;"><?= $s['nombre'] ?></td>
                        <td><?= htmlspecialchars($s['platillo_sugerido']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($sugerencias)): ?>
                        <tr><td colspan="3" style="text-align:center;">Aún no hay sugerencias registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>