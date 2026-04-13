<?php
// Consulta técnica: Contamos cuántos votos tiene cada opción
$sql = "SELECT o.nombre_platillo, COUNT(v.id_voto) as total_votos 
        FROM encuesta_opciones o
        LEFT JOIN encuesta_votos v ON o.id_opcion = v.id_opcion
        GROUP BY o.id_opcion 
        ORDER BY total_votos DESC";
$resultados = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="glass-card">
    <h3><i class="fa-solid fa-chart-bar"></i> Resultados de la Semana</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Platillo Propuesto</th>
                <th>Votos Recibidos</th>
                <th>Popularidad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados as $res): ?>
                <tr>
                    <td><strong>
                            <?= $res['nombre_platillo'] ?>
                        </strong></td>
                    <td>
                        <?= $res['total_votos'] ?> alumnos
                    </td>
                    <td>
                        <div style="background:#eee; width:100%; height:10px; border-radius:5px;">
                            <div
                                style="background:var(--primary); width:<?= ($res['total_votos'] / 10) ?>%; height:10px; border-radius:5px;">
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>