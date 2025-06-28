<?php
session_start();
require_once __DIR__ . '/../Backend/config/db.php';

if (!isset($_SESSION['id_cliente'])) {
    header('Location: login_cliente.php');
    exit;
}

$cliente_id = $_SESSION['id_cliente'];
$stmt = $pdo->prepare("SELECT nombre, apellido, email FROM clientes WHERE id_cliente = ?");
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

$carrito = $_SESSION['carrito'] ?? [];
$productos = [];
$total_carrito = 0;

if (!empty($carrito)) {
    $ids = array_keys($carrito);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id_producto, codigo_producto, descripcion, precio_unitario, destino, duracion_dias FROM productos WHERE id_producto IN ($placeholders)");
    $stmt->execute($ids);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($productos as $producto) {
        $total_carrito += $producto['precio_unitario'] * $carrito[$producto['id_producto']];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Agencia de Turismo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #3b82f6;
            --color-primary-dark: #2563eb;
            --color-primary-light: #93c5fd;
            --color-danger: #ef4444;
            --color-danger-dark: #dc2626;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-light: #f8fafc;
            --color-dark: #1e293b;
            --color-gray: #64748b;
            --color-gray-light: #e2e8f0;
            --color-white: #ffffff;
            --gradient-bg: linear-gradient(135deg, #60a5fa 0%, #003da0 100%);
            --border-radius: 0.75rem;
            --border-radius-sm: 0.5rem;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --box-shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        [data-theme="dark"] {
            --color-primary: #60a5fa;
            --color-primary-dark: #3b82f6;
            --color-primary-light: #1e40af;
            --color-light: #1e293b;
            --color-dark: #f8fafc;
            --color-gray: #94a3b8;
            --color-gray-light: #334155;
            --gradient-bg: linear-gradient(135deg, #1e40af 0%, #0f172a 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--color-light);
            color: var(--color-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            line-height: 1.5;
        }

        header {
            background: var(--gradient-bg);
            color: var(--color-white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--box-shadow);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-icon {
            font-size: 1.8rem;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--color-white);
            color: var(--color-primary);
        }

        .btn-primary:hover {
            background-color: var(--color-primary-light);
            color: var(--color-white);
        }

        .btn-danger {
            background-color: var(--color-danger);
            color: var(--color-white);
        }

        .btn-danger:hover {
            background-color: var(--color-danger-dark);
        }

        .btn-secondary {
            background-color: transparent;
            border: 2px solid var(--color-white);
            color: var(--color-white);
        }

        .btn-secondary:hover {
            background-color: var(--color-white);
            color: var(--color-primary);
        }

        main {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-primary);
        }

        .cart-container {
            background-color: var(--color-white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        [data-theme="dark"] .cart-container {
            background-color: var(--color-gray-light);
        }

        .cart-empty {
            text-align: center;
            padding: 2rem;
            font-size: 1.2rem;
            color: var(--color-gray);
        }

        .cart-items {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-items thead th {
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--color-gray-light);
        }

        .cart-items tbody tr {
            border-bottom: 1px solid var(--color-gray-light);
        }

        .cart-items tbody tr:last-child {
            border-bottom: none;
        }

        .cart-items td {
            padding: 1.5rem 1rem;
            vertical-align: middle;
        }

        .cart-item-image {
            width: 100px;
            height: 70px;
            border-radius: var(--border-radius-sm);
            object-fit: cover;
            background-color: var(--color-primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-white);
        }

        .cart-item-image i {
            font-size: 2rem;
        }

        .cart-item-details {
            padding-left: 1rem;
        }

        .cart-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--color-dark);
        }

        .cart-item-meta {
            font-size: 0.9rem;
            color: var(--color-gray);
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background-color: var(--color-primary);
            color: var(--color-white);
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background-color: var(--color-primary-dark);
        }

        .quantity-btn.remove {
            background-color: var(--color-danger);
        }

        .quantity-btn.remove:hover {
            background-color: var(--color-danger-dark);
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            padding: 0.5rem;
            border: 1px solid var(--color-gray-light);
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
        }

        .cart-item-price {
            font-weight: 600;
            color: var(--color-primary);
        }

        .cart-item-subtotal {
            font-weight: 700;
            color: var(--color-dark);
        }

        .cart-summary {
            background-color: var(--color-white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        [data-theme="dark"] .cart-summary {
            background-color: var(--color-gray-light);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .summary-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-primary);
            border-top: 1px solid var(--color-gray-light);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .btn-continue {
            background-color: var(--color-primary);
            color: var(--color-white);
        }

        .btn-continue:hover {
            background-color: var(--color-primary-dark);
        }

        .btn-checkout {
            background-color: var(--color-success);
            color: var(--color-white);
        }

        .btn-checkout:hover {
            background-color: #0d9f6e;
        }

        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background-color: var(--color-success);
            color: var(--color-white);
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--box-shadow-lg);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.error {
            background-color: var(--color-danger);
        }

        .toast-icon {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .cart-items thead {
                display: none;
            }

            .cart-items tr {
                display: block;
                margin-bottom: 1.5rem;
                border-bottom: 2px solid var(--color-gray-light);
            }

            .cart-items td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem;
                border-bottom: 1px solid var(--color-gray-light);
            }

            .cart-items td::before {
                content: attr(data-label);
                font-weight: 600;
                margin-right: 1rem;
                color: var(--color-gray);
            }

            .cart-item-image {
                margin: 0 auto;
            }

            .cart-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .user-actions {
                width: 100%;
                justify-content: space-between;
            }

            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body data-theme="light">
    <header>
        <div class="logo">
            <i class="fas fa-plane logo-icon"></i>
            <span class="logo-text">Agencia Turismo</span>
        </div>

        <div class="user-actions">
            <a href="home.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
            
            <button id="theme-toggle" class="btn theme-toggle">
                <i class="fas fa-moon"></i>
                <span>Modo oscuro</span>
            </button>
            
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Salir</span>
            </a>
        </div>
    </header>

    <main>
        <div class="page-header">
            <h1 class="page-title">Carrito de Compras</h1>
            <div>
                <span>Bienvenido, <?= htmlspecialchars("{$cliente['nombre']} {$cliente['apellido']}") ?></span>
            </div>
        </div>

        <div class="cart-container">
            <?php if (empty($productos)): ?>
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--color-gray); margin-bottom: 1rem;"></i>
                    <p>Tu carrito está vacío</p>
                    <a href="home.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-arrow-left"></i>
                        Ver paquetes turísticos
                    </a>
                </div>
            <?php else: ?>
                <table class="cart-items">
                    <thead>
                        <tr>
                            <th>Paquete</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): 
                            $cantidad = $carrito[$producto['id_producto']];
                            $subtotal = $producto['precio_unitario'] * $cantidad;
                        ?>
                            <tr data-id="<?= $producto['id_producto'] ?>">
                                <td data-label="Paquete">
                                    <div style="display: flex; align-items: center;">
                                        <div class="cart-item-details">
                                            <div class="cart-item-title"><?= htmlspecialchars($producto['descripcion']) ?></div>
                                            <div class="cart-item-meta">
                                                <div><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($producto['destino']) ?></div>
                                                <div><i class="fas fa-calendar-day"></i> <?= $producto['duracion_dias'] ?> días</div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Precio" class="cart-item-price">$<?= number_format($producto['precio_unitario'], 2) ?></td>
                                <td data-label="Cantidad">
                                    <div class="quantity-control">
                                        <button class="quantity-btn btn-decrease">-</button>
                                        <input type="text" class="quantity-input" value="<?= $cantidad ?>" readonly>
                                        <button class="quantity-btn btn-increase">+</button>
                                    </div>
                                </td>
                                <td data-label="Subtotal" class="cart-item-subtotal">$<?= number_format($subtotal, 2) ?></td>
                                <td>
                                    <button class="quantity-btn remove btn-remove">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if (!empty($productos)): ?>
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($total_carrito, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Descuentos:</span>
                    <span>$0.00</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span>$<?= number_format($total_carrito, 2) ?></span>
                </div>
            </div>

            <div class="cart-actions">
                <a href="home.php" class="btn btn-continue">
                    <i class="fas fa-arrow-left"></i>
                    Seguir comprando
                </a>
                <button id="btn-checkout" class="btn btn-checkout">
                    <i class="fas fa-credit-card"></i>
                    Finalizar compra
                </button>
            </div>
        <?php endif; ?>
    </main>

    <div id="toast" class="toast" role="alert" aria-live="assertive">
        <i class="fas fa-check-circle toast-icon"></i>
        <span id="toast-message"></span>
    </div>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            const icon = themeToggle.querySelector('i');
            const text = themeToggle.querySelector('span');
            if (theme === 'dark') {
                icon.classList.replace('fa-moon', 'fa-sun');
                text.textContent = 'Modo claro';
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                text.textContent = 'Modo oscuro';
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = toast.querySelector('i');
            
            toastMessage.textContent = message;
            
            if (type === 'success') {
                toast.style.backgroundColor = 'var(--color-success)';
                toast.className = 'toast show';
                toastIcon.className = 'fas fa-check-circle toast-icon';
            } else if (type === 'error') {
                toast.style.backgroundColor = 'var(--color-danger)';
                toast.className = 'toast error show';
                toastIcon.className = 'fas fa-exclamation-circle toast-icon';
            }
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        async function updateCartItem(productId, quantity) {
            try {
                const response = await fetch('../Backend/actualizar_carrito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_producto=${productId}&cantidad=${quantity}`
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message || 'Carrito actualizado');
                    if (quantity === 0) {
                        document.querySelector(`tr[data-id="${productId}"]`).remove();
                        if (document.querySelectorAll('tbody tr').length === 0) {
                            location.reload();
                        }
                    }
                    updateCartTotal();
                } else {
                    showToast(result.message || 'Error al actualizar', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión', 'error');
            }
        }

        function updateCartTotal() {
            location.reload();
        }

        document.querySelectorAll('.btn-increase').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const productId = row.dataset.id;
                const input = row.querySelector('.quantity-input');
                let quantity = parseInt(input.value) + 1;
                input.value = quantity;
                updateCartItem(productId, quantity);
            });
        });

        document.querySelectorAll('.btn-decrease').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const productId = row.dataset.id;
                const input = row.querySelector('.quantity-input');
                let quantity = parseInt(input.value) - 1;
                
                if (quantity < 1) quantity = 1;
                
                input.value = quantity;
                updateCartItem(productId, quantity);
            });
        });

        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const productId = row.dataset.id;
                updateCartItem(productId, 0);
            });
        });

        document.getElementById('btn-checkout')?.addEventListener('click', () => {
            window.location.href = 'checkout.php';
        });
    </script>
</body>
</html>








