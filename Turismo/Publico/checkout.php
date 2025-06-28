<?php
session_start();
require_once __DIR__ . '/../Backend/config/db.php';

if (!isset($_SESSION['id_cliente'])) {
    header('Location: login_cliente.php');
    exit;
}

if (empty($_SESSION['carrito'] ?? [])) {
    header('Location: carrito.php');
    exit;
}

$cliente_id = $_SESSION['id_cliente'];
$stmt = $pdo->prepare("SELECT nombre, apellido, email FROM clientes WHERE id_cliente = ?");
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

$carrito = $_SESSION['carrito'] ?? [];
$productos = [];
$total = 0;

if (!empty($carrito)) {
    $ids = array_keys($carrito);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id_producto, descripcion, precio_unitario, destino FROM productos WHERE id_producto IN ($placeholders)");
    $stmt->execute($ids);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($productos as $producto) {
        $total += $producto['precio_unitario'] * $carrito[$producto['id_producto']];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Agencia de Turismo</title>
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
            --color-black: #000000;
            --color-input-dark: #334155;
            --color-input-border-dark: #475569;
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
            --color-gray-light: #e2e8f0;
            --color-white: #1e293b;
            --color-black: #ffffff;
            --color-input-dark: #0f172a;
            --color-input-border-dark: #1e40af;
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

        main {
            flex: 1;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 992px) {
            main {
                grid-template-columns: 1fr 1fr;
            }
        }

        .checkout-section {
            background-color: var(--color-white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        [data-theme="dark"] .checkout-section {
            background-color: var(--color-input-dark);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--color-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--color-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--color-gray-light);
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--color-white);
            color: var(--color-black);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .payment-option {
            display: none;
            padding: 1rem;
            border: 1px solid var(--color-gray-light);
            border-radius: var(--border-radius-sm);
            margin-top: 1rem;
            background-color: var(--color-light);
        }

        .payment-option.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .payment-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .payment-fields .form-group {
            margin-bottom: 0;
        }

        .order-summary {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
            padding-right: 0.5rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--color-gray-light);
        }

        .order-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-primary);
            border-top: 2px solid var(--color-gray-light);
            padding-top: 1rem;
            margin-top: 1rem;
            display: flex;
            justify-content: space-between;
        }

        .btn-submit {
            width: 100%;
            background-color: var(--color-success);
            color: var(--color-white);
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .error-message {
            color: var(--color-danger);
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: none;
        }

        .input-error {
            border-color: var(--color-danger) !important;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
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
            <button id="theme-toggle" class="btn theme-toggle">
                <i class="fas fa-moon"></i>
                <span>Modo oscuro</span>
            </button>
            
            <a href="../Backend/logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Salir</span>
            </a>
        </div>
    </header>

    <main>
        <section class="checkout-section">
            <h2 class="section-title">
                <i class="fas fa-user"></i>
                Información del Cliente
            </h2>

            <form id="checkout-form" action="../Backend/procesar_checkout.php" method="POST">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>" required>
                    <div id="nombre-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="apellido" class="form-label">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" class="form-control" 
                           value="<?= htmlspecialchars($cliente['apellido'] ?? '') ?>" required>
                    <div id="apellido-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($cliente['email'] ?? '') ?>" required>
                    <div id="email-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="telefono" class="form-label">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" required>
                    <div id="telefono-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="dni" class="form-label">DNI *</label>
                    <input type="number" id="dni" name="dni" class="form-control" required min="1000000" max="99999999">
                    <div id="dni-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="direccion" class="form-label">Dirección *</label>
                    <textarea id="direccion" name="direccion" class="form-control" required></textarea>
                    <div id="direccion-error" class="error-message"></div>
                </div>

                <h2 class="section-title" style="margin-top: 2rem;">
                    <i class="fas fa-credit-card"></i>
                    Método de Pago
                </h2>

                <div class="form-group payment-method">
                    <label for="metodo-pago" class="form-label">Seleccione método de pago *</label>
                    <select id="metodo-pago" name="metodo_pago" class="form-control" required>
                        <option value="" disabled selected>-- Elegir --</option>
                        <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="efectivo">Efectivo (Rapipago/Pago Fácil)</option>
                    </select>
                    <div id="metodo-pago-error" class="error-message"></div>
                </div>

                <div id="pago-tarjeta" class="payment-option">
                    <div class="form-group">
                        <label for="titular" class="form-label">Titular de la Tarjeta *</label>
                        <input type="text" id="titular" name="titular" class="form-control">
                        <div id="titular-error" class="error-message"></div>
                    </div>

                    <div class="form-group">
                        <label for="numero-tarjeta" class="form-label">Número de Tarjeta *</label>
                        <input type="text" id="numero-tarjeta" name="numero_tarjeta" class="form-control" maxlength="19">
                        <div id="numero-tarjeta-error" class="error-message"></div>
                    </div>

                    <div class="payment-fields">
                        <div class="form-group">
                            <label for="vencimiento" class="form-label">Vencimiento (MM/AA) *</label>
                            <input type="text" id="vencimiento" name="vencimiento" class="form-control" maxlength="5" placeholder="MM/AA">
                            <div id="vencimiento-error" class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="cvv" class="form-label">CVV *</label>
                            <input type="text" id="cvv" name="cvv" class="form-control" maxlength="4">
                            <div id="cvv-error" class="error-message"></div>
                        </div>
                    </div>
                </div>

                <div id="pago-transferencia" class="payment-option">
                    <div class="form-group">
                        <label for="banco" class="form-label">Banco *</label>
                        <input type="text" id="banco" name="banco" class="form-control">
                        <div id="banco-error" class="error-message"></div>
                    </div>

                    <div class="form-group">
                        <label for="cbu" class="form-label">CBU/CVU *</label>
                        <input type="text" id="cbu" name="cbu" class="form-control" maxlength="22">
                        <div id="cbu-error" class="error-message"></div>
                    </div>

                    <div class="form-group">
                        <label for="titular-cuenta" class="form-label">Titular de la Cuenta *</label>
                        <input type="text" id="titular-cuenta" name="titular_cuenta" class="form-control">
                        <div id="titular-cuenta-error" class="error-message"></div>
                    </div>
                </div>

                <div id="pago-efectivo" class="payment-option">
                    <p style="color: var(--color-gray);">Se generará un código para abonar en Rapipago o Pago Fácil. Recibirás los detalles por email.</p>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i>
                    Confirmar Compra
                </button>
            </form>
        </section>

        <section class="checkout-section order-summary">
            <h2 class="section-title">
                <i class="fas fa-receipt"></i>
                Resumen de Compra
            </h2>

            <div class="order-items">
                <?php foreach ($productos as $producto): 
                    $cantidad = $carrito[$producto['id_producto']];
                    $subtotal = $producto['precio_unitario'] * $cantidad;
                ?>
                    <div class="order-item">
                        <span class="order-item-name">
                            <?= htmlspecialchars($producto['descripcion']) ?> 
                            (<?= $cantidad ?> x $<?= number_format($producto['precio_unitario'], 2) ?>)
                        </span>
                        <span class="order-item-price">$<?= number_format($subtotal, 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-total">
                <span>Total:</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
        </section>
    </main>

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

        const paymentMethod = document.getElementById('metodo-pago');
        const paymentOptions = {
            tarjeta: document.getElementById('pago-tarjeta'),
            transferencia: document.getElementById('pago-transferencia'),
            efectivo: document.getElementById('pago-efectivo')
        };

        paymentMethod.addEventListener('change', function() {
            Object.values(paymentOptions).forEach(option => {
                option.classList.remove('active');
            });
            
            if (this.value && paymentOptions[this.value]) {
                paymentOptions[this.value].classList.add('active');
            }
        });

        document.getElementById('numero-tarjeta').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '');
            if (value.length > 16) value = value.substring(0, 16);
            
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            
            e.target.value = formatted;
        });

        document.getElementById('vencimiento').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;
            
            const requiredFields = this.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    showError(field, 'Este campo es obligatorio');
                    isValid = false;
                }
            });
            
            const dni = document.getElementById('dni').value;
            if (dni.length < 7 || dni.length > 8) {
                showError(document.getElementById('dni'), 'DNI debe tener entre 7 y 8 dígitos');
                isValid = false;
            }
            
            const metodoPago = paymentMethod.value;
            
            if (metodoPago === 'tarjeta') {
                const numeroTarjeta = document.getElementById('numero-tarjeta').value.replace(/\s/g, '');
                if (numeroTarjeta.length !== 16 || !/^\d+$/.test(numeroTarjeta)) {
                    showError(document.getElementById('numero-tarjeta'), 'Número de tarjeta inválido');
                    isValid = false;
                }
                
                const vencimiento = document.getElementById('vencimiento').value;
                if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(vencimiento)) {
                    showError(document.getElementById('vencimiento'), 'Formato MM/AA requerido');
                    isValid = false;
                }
                
                const cvv = document.getElementById('cvv').value;
                if (!/^\d{3,4}$/.test(cvv)) {
                    showError(document.getElementById('cvv'), 'CVV inválido');
                    isValid = false;
                }
            }
            
            if (metodoPago === 'transferencia') {
                const cbu = document.getElementById('cbu').value;
                if (!/^\d{22}$/.test(cbu)) {
                    showError(document.getElementById('cbu'), 'CBU debe tener 22 dígitos');
                    isValid = false;
                }
            }
            
            if (isValid) {
                this.submit();
            }
        });

        function showError(inputElement, message) {
            const errorElement = document.getElementById(inputElement.id + '-error');
            inputElement.classList.add('input-error');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    </script>
</body>
</html>


