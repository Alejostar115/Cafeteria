<?php
session_start();
require '../config/conexion.php';
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit();
}

// Lógica para RESETEAR la encuesta y poner nuevas opciones
if (isset($_POST['reiniciar_encuesta'])) {
    $conexion->query("TRUNCATE TABLE encuesta_opciones");
    $conexion->query("TRUNCATE TABLE encuesta_participacion");

    $opciones = $_POST['opciones']; // Array de nombres
    foreach ($opciones as $nombre) {
        if (!empty($nombre)) {
            $stmt = $conexion->prepare("INSERT INTO encuesta_opciones (nombre_platillo) VALUES (?)");
            $stmt->execute([$nombre]);
        }
    }
    header("Location: gestionar_encuesta.php?msj=actualizado");
}

$opciones_actuales = $conexion->query("SELECT * FROM encuesta_opciones")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <title>Gestionar Encuesta - Admin</title>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <h1>Control de Encuesta Semanal</h1>
        <p>Define las 4 opciones por las que los alumnos podrán votar esta semana.</p>

        <div class="glass-card" style="max-width: 500px;">
            <form method="POST">
                <div class="input-group" style="margin-bottom:20px;">
                    <label>Opción 1:</label> <input type="text" name="opciones[]" placeholder="Ej: Chilaquiles" required
                        style="width:100%; padding:8px;">
                    <label>Opción 2:</label> <input type="text" name="opciones[]" placeholder="Ej: Tacos de Guiso"
                        required style="width:100%; padding:8px;">
                    <label>Opción 3:</label> <input type="text" name="opciones[]" placeholder="Ej: Hamburguesas"
                        required style="width:100%; padding:8px;">
                    <label>Opción 4:</label> <input type="text" name="opciones[]" placeholder="Ej: Ensalada César"
                        required style="width:100%; padding:8px;">
                </div>

                <button type="submit" name="reiniciar_encuesta" class="btn-primary"
                    onclick="return confirm('Esto borrará los votos anteriores. ¿Continuar?')">
                    Publicar Nueva Encuesta
                </button>
            </form>
        </div>

        <div class="glass-card" style="margin-top:20px;">
            <h3>Resultados Actuales</h3>
            <?php foreach ($opciones_actuales as $o): ?>
                <div style="margin-bottom:10px;">
                    <strong><?= $o['nombre_platillo'] ?>:</strong> <?= $o['votos_count'] ?> votos
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>

</html>