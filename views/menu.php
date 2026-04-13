<?php
session_start();
require '../config/conexion.php';

// Seguridad: Si no hay sesión, rebotamos al index (login)
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

// 1. Obtener categorías para el filtro superior
$categorias = $conexion->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);

// 2. Lógica de filtrado
$cat_id = isset($_GET['cat']) ? $_GET['cat'] : null;

if ($cat_id) {
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id_categoria = ? AND stock > 0");
    $stmt->execute([$cat_id]);
} else {
    $stmt = $conexion->query("SELECT * FROM productos WHERE stock > 0 ORDER BY id_producto DESC");
}
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- FUNCIÓN TÉCNICA PARA EL NOMBRE (EVITA EL ERROR DE ARRAY) ---
// Esta lógica detecta si tu sesión guarda el nombre como texto o como arreglo
$nombre_usuario = "Usuario";
if (isset($_SESSION['nombre'])) {
    if (is_array($_SESSION['nombre'])) {
        // Si es un arreglo, buscamos la llave 'nombre' dentro
        $nombre_completo = $_SESSION['nombre']['nombre'];
    } else {
        // Si ya es un texto, lo usamos directo
        $nombre_completo = $_SESSION['nombre'];
    }
    // Tomamos solo el primer nombre
    $nombre_usuario = explode(' ', $nombre_completo);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - Cafetería UPAP</title>
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

        /* Header fijo */
        .header-alumno {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Scroll de categorías */
        .categories-scroll {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding: 15px 0 5px;
            scrollbar-width: none;
        }

        .categories-scroll::-webkit-scrollbar {
            display: none;
        }

        .cat-card {
            background: #f1f5f9;
            padding: 10px 18px;
            border-radius: 25px;
            text-decoration: none;
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            transition: 0.3s;
        }

        .cat-card.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        /* Grid de productos */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .product-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            transition: 0.2s;
        }

        .product-card:active {
            transform: scale(0.96);
        }

        .product-img {
            width: 100%;
            height: 130px;
            object-fit: cover;
        }

        .product-info {
            padding: 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-name {
            font-weight: 600;
            font-size: 0.85rem;
            margin: 0;
            color: var(--dark);
            height: 2.5em;
            overflow: hidden;
        }

        .product-price {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.1rem;
            margin: 5px 0;
        }

        .btn-order {
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            font-size: 0.85rem;
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

    <header class="header-alumno">
        <h2 style="margin:0; font-size: 1.2rem;">Hola, <?= $nombre_usuario ?> 👋</h2>
        <p style="margin:5px 0 0; color:var(--muted); font-size: 0.75rem;">¿Qué se te antoja hoy?</p>

        <div class="categories-scroll">
            <a href="menu.php" class="cat-card <?= !$cat_id ? 'active' : '' ?>">Todos</a>
            <?php foreach ($categorias as $c): ?>
                <a href="menu.php?cat=<?= $c['id_categoria'] ?>"
                    class="cat-card <?= $cat_id == $c['id_categoria'] ? 'active' : '' ?>">
                    <?= $c['nombre_categoria'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </header>

    <div class="menu-grid">
        <?php foreach ($productos as $p): ?>
            <div class="product-card">
                <img src="../<?= $p['imagen_url'] ?>" class="product-img"
                    onerror="this.src='../assets/img/productos/default.png'">
                <div class="product-info">
                    <h3 class="product-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                    <div class="product-price">$<?= number_format($p['precio'], 2) ?></div>
                    <a href="checkout.php?id=<?= $p['id_producto'] ?>" class="btn-order">Ordenar</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <nav class="bottom-nav">
        <a href="menu.php" class="nav-item active">
            <i class="fa-solid fa-utensils"></i>Menú
        </a>
        <a href="mis_pedidos.php" class="nav-item">
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