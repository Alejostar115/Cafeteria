<?php
session_start();
require '../config/conexion.php';

// 1. Seguridad básica: Solo usuarios logueados y peticiones por POST
if (!isset($_SESSION['id_usuario']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: menu.php");
    exit();
}

// 2. Recibimos los datos del formulario (checkout.php)
$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'];
$cantidad = intval($_POST['cantidad']); // Aseguramos que la cantidad sea un número entero
$metodo_pago = $_POST['metodo_pago'];

// 3. Validación de Seguridad (Evitar fraudes o compras sin stock)
$stmt = $conexion->prepare("SELECT precio, stock FROM productos WHERE id_producto = ?");
$stmt->execute([$id_producto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificamos que el producto exista y que la cantidad pedida no supere el stock real
if (!$producto || $cantidad < 1 || $cantidad > $producto['stock']) {
    header("Location: menu.php?error=nostock");
    exit();
}

// Calculamos el total real y seguro desde la base de datos (Ignoramos el del HTML por si lo manipularon)
$total = $producto['precio'] * $cantidad;

// 4. Lógica de Cobro (Efectivo vs Tarjeta)
if ($metodo_pago == 'Efectivo') {
    $pago_con = floatval($_POST['pago_con']);
    // Validación extra: Si el billete ingresado es menor al costo total, lo rebotamos
    if ($pago_con < $total) {
        header("Location: checkout.php?id=$id_producto&error=pago_insuficiente");
        exit();
    }
} else {
    // Si es Tarjeta, asumimos que se cobra la cantidad exacta
    $pago_con = $total;
}

// 5. TRANSACCIÓN SEGURA A LA BASE DE DATOS
try {
    // Iniciamos transacción: Si algo falla a la mitad, no se guarda nada (Cero errores de inventario)
    $conexion->beginTransaction();

    // A) Generar el Turno del Día (Busca el último turno de hoy y le suma 1)
    $stmt_turno = $conexion->query("SELECT MAX(numero_turno) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
    $ultimo_turno = $stmt_turno->fetchColumn();
    $nuevo_turno = $ultimo_turno ? $ultimo_turno + 1 : 1;

    // B) Insertar el Pedido (Incluyendo la cantidad multiplicada)
    $sql_pedido = "INSERT INTO pedidos (id_usuario, id_producto, cantidad, total, metodo_pago, pago_con, numero_turno, estado, tiempo_espera) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente', 15)";
    $stmt_insert = $conexion->prepare($sql_pedido);
    $stmt_insert->execute([$id_usuario, $id_producto, $cantidad, $total, $metodo_pago, $pago_con, $nuevo_turno]);

    // C) Descontar del Inventario (Restamos exactamente la cantidad que el alumno pidió)
    $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
    $stmt_stock = $conexion->prepare($sql_stock);
    $stmt_stock->execute([$cantidad, $id_producto]);

    // Confirmamos que todo salió perfecto y lo guardamos
    $conexion->commit();

    // Todo listo, mandamos al alumno a ver su turno en vivo
    header("Location: mis_pedidos.php?msj=exito");
    exit();

} catch (Exception $e) {
    $conexion->rollBack();
    // Esto va a imprimir el error exacto en la pantalla en lugar de regresarte al menú
    die("Error fatal en la Base de Datos: " . $e->getMessage());
}
?>