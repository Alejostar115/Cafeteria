<?php
session_start();
require '../config/conexion.php';

// Si no hay sesión, rebotamos al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

// Recibimos el ID del producto que el alumno quiere comprar
if (!isset($_GET['id'])) {
    header("Location: menu.php");
    exit();
}

$id_producto = $_GET['id'];

// Consultamos los datos del producto seleccionado
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id_producto = ?");
$stmt->execute([$id_producto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el producto no existe o se acabó el stock, lo regresamos al menú
if (!$producto || $producto['stock'] <= 0) {
    header("Location: menu.php?error=nostock");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Orden - UPAP</title>
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
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .checkout-card {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        /* Detalles del producto */
        .product-preview {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .product-preview img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
        }

        /* Opciones de pago */
        .payment-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .payment-options input[type="radio"] {
            display: none;
        }

        .payment-label {
            flex: 1;
            text-align: center;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            color: var(--muted);
            transition: 0.2s;
        }

        .payment-options input[type="radio"]:checked+.payment-label {
            border-color: var(--primary);
            background: #eff6ff;
            color: var(--primary);
        }

        /* Campo para efectivo */
        .cash-input-group {
            display: none;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease;
        }

        .cash-input-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-confirm {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div class="checkout-card">
        <h2 style="margin: 0 0 5px; font-size: 1.4rem;">Confirma tu orden</h2>
        <p style="color: var(--muted); margin-top: 0; font-size: 0.85rem;">Verifica los detalles antes de generar el
            turno.</p>

        <div class="product-preview">
            <img src="../<?= $producto['imagen_url'] ?>" onerror="this.src='../assets/img/productos/default.png'">
            <div style="width: 100%;">
                <h3 style="margin: 0 0 5px; font-size: 1.1rem; color: var(--dark);">
                    
                    <?= htmlspecialchars($producto['nombre']) ?></h3>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label style="font-size: 0.85rem; color: var(--muted); font-weight: bold;">Cant:</label>
                        <input type="number" name="cantidad" id="input_cantidad" form="form_checkout" value="1" min="1"
                            max="<?= $producto['stock'] ?>"
                            style="width: 60px; padding: 5px; border-radius: 8px; border: 1px solid #cbd5e1; text-align: center;">
                    </div>

                    <p style="margin: 0; color: var(--primary); font-weight: 700; font-size: 1.3rem;"
                        id="precio_display">
                        $<?= number_format($producto['precio'], 2) ?>
                    </p>
                </div>
                <small style="color: #94a3b8; font-size: 0.75rem;">Disponibles: <?= $producto['stock'] ?></small>
            </div>
        </div>

        <form action="procesar_pedido.php" method="POST" id="form_checkout">
            <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
            <input type="hidden" name="precio_total" id="precio_total" value="<?= $producto['precio'] ?>">

            <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 0.9rem;">¿Cómo vas a pagar
                en caja?</label>

            <div class="payment-options">
                <input type="radio" name="metodo_pago" value="Efectivo" id="pay_cash" checked>
                <label for="pay_cash" class="payment-label">
                    <i class="fa-solid fa-money-bill-wave"></i><br>Efectivo
                </label>

                <input type="radio" name="metodo_pago" value="Tarjeta" id="pay_card">
                <label for="pay_card" class="payment-label">
                    <i class="fa-solid fa-credit-card"></i><br>Tarjeta
                </label>
            </div>

            <div class="cash-input-group" id="cash_div" style="display: block;">
                <label style="font-size: 0.85rem; color: var(--muted); display: block; margin-bottom: 5px;">¿Con qué
                    billete vas a pagar?</label>
                <input type="number" step="0.50" name="pago_con" id="input_pago" placeholder="Ej: 50 o 100" required
                    min="<?= $producto['precio'] ?>">
                <small id="error_pago" style="color: #ef4444; display: none; margin-top: 5px;">El billete no alcanza
                    para pagar.</small>
            </div>

            <button type="submit" class="btn-confirm" id="btn_submit">
                Generar Turno <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <a href="menu.php" class="btn-cancel">Cancelar y regresar al menú</a>
    </div>

    <script>
        const radioCash = document.getElementById('pay_cash');
        const radioCard = document.getElementById('pay_card');
        const cashDiv = document.getElementById('cash_div');
        const inputPago = document.getElementById('input_pago');
        const inputCantidad = document.getElementById('input_cantidad');
        const precioDisplay = document.getElementById('precio_display');
        const inputPrecioTotal = document.getElementById('precio_total');
        const btnSubmit = document.getElementById('btn_submit');
        const errorPago = document.getElementById('error_pago');

        const precioUnitario = <?= $producto['precio'] ?>;
        const maxStock = <?= $producto['stock'] ?>;

        // Multiplicador en tiempo real
        inputCantidad.addEventListener('input', () => {
            let cant = parseInt(inputCantidad.value) || 1;

            // Bloqueo de seguridad: No pueden pedir más de lo que hay
            if (cant > maxStock) {
                cant = maxStock;
                inputCantidad.value = cant;
            } else if (cant < 1) {
                cant = 1;
                inputCantidad.value = cant;
            }

            let nuevoTotal = cant * precioUnitario;

            precioDisplay.innerText = '$' + nuevoTotal.toFixed(2);
            inputPrecioTotal.value = nuevoTotal;

            validarPago();
        });

        // Alternar vistas de método de pago
        radioCard.addEventListener('change', () => {
            cashDiv.style.display = 'none';
            inputPago.required = false;
            btnSubmit.disabled = false;
            btnSubmit.style.opacity = '1';
            errorPago.style.display = 'none';
        });

        radioCash.addEventListener('change', () => {
            cashDiv.style.display = 'block';
            inputPago.required = true;
            validarPago();
        });

        // Validación anti-errores para el billete ingresado
        inputPago.addEventListener('input', validarPago);

        function validarPago() {
            if (radioCash.checked) {
                let pago = parseFloat(inputPago.value) || 0;
                let totalActual = parseFloat(inputPrecioTotal.value);

                if (pago < totalActual) {
                    errorPago.style.display = 'block';
                    errorPago.innerText = 'El pago debe ser de al menos $' + totalActual.toFixed(2);
                    btnSubmit.style.opacity = '0.5';
                    btnSubmit.disabled = true;
                } else {
                    errorPago.style.display = 'none';
                    btnSubmit.style.opacity = '1';
                    btnSubmit.disabled = false;
                }
            }
        }
    </script>
</body>

</html>