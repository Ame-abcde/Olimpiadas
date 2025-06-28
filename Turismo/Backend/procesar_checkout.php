<?php
session_start();
require_once __DIR__ . '/config/db.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id_cliente']) || empty($_SESSION['carrito'] ?? [])) {
    header('Location: ../Publico/login_cliente.php');
    exit;
}

$requiredFields = ['nombre', 'apellido', 'email', 'direccion', 'telefono', 'dni', 'metodo_pago'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        die(json_encode(['error' => "El campo $field es obligatorio"]));
    }
}

$id_cliente = $_SESSION['id_cliente'];
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
$dni = filter_input(INPUT_POST, 'dni', FILTER_SANITIZE_STRING);
$metodo_pago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);


$numero_tarjeta = isset($_POST['numero_tarjeta']) ? str_replace(' ', '', $_POST['numero_tarjeta']) : '';
$titular = $_POST['titular'] ?? '';
$vencimiento = $_POST['vencimiento'] ?? '';
$cvv = $_POST['cvv'] ?? '';

$banco = $_POST['banco'] ?? '';
$cbu = $_POST['cbu'] ?? '';
$titular_cuenta = $_POST['titular_cuenta'] ?? '';

if (!$nombre || !$apellido || !$email || !$direccion || !$telefono || !$dni || !$metodo_pago) {
    die("Error: Faltan datos obligatorios.");
}

if ($metodo_pago === 'tarjeta') {
    if (empty($numero_tarjeta) || empty($titular) || empty($vencimiento) || empty($cvv)) {
        die("Error: Faltan datos de la tarjeta.");
    }
    
    if (!preg_match('/^\d{16}$/', $numero_tarjeta)) {
        die("Error: El número de tarjeta debe tener 16 dígitos.");
    }
    
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $vencimiento)) {
        die("Error: Formato de vencimiento inválido (MM/AA).");
    }
    
    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        die("Error: CVV debe tener 3 o 4 dígitos.");
    }
} elseif ($metodo_pago === 'transferencia') {
    if (empty($banco) || empty($cbu) || empty($titular_cuenta)) {
        die("Error: Faltan datos de transferencia.");
    }
    
    if (!preg_match('/^\d{22}$/', $cbu)) {
        die("Error: CBU debe tener 22 dígitos.");
    }
}

$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    die("Error: No hay productos en el carrito.");
}

$ids = array_keys($carrito);
if (empty($ids)) {
    die("Error: El carrito está vacío.");
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id_producto, descripcion, precio_unitario FROM productos WHERE id_producto IN ($placeholders)");
$stmt->execute($ids);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($productos)) {
    die("Error: No se encontraron los productos del carrito.");
}

$total = 0;
foreach ($productos as $producto) {
    if (!isset($carrito[$producto['id_producto']])) {
        continue;
    }
    $cantidad = $carrito[$producto['id_producto']];
    $total += $producto['precio_unitario'] * $cantidad;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, fecha_pedido, estado, total) VALUES (?, NOW(), 'Pendiente', ?)");
    $stmt->execute([$id_cliente, $total]);
    $id_pedido = $pdo->lastInsertId();

    $stmt_detalle = $pdo->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    
    foreach ($productos as $producto) {
        if (!isset($carrito[$producto['id_producto']])) {
            continue;
        }
        $cantidad = $carrito[$producto['id_producto']];
        $stmt_detalle->execute([
            $id_pedido,
            $producto['id_producto'],
            $cantidad,
            $producto['precio_unitario']
        ]);
    }

    $pdo->commit();
    unset($_SESSION['carrito']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error al procesar el pedido: " . $e->getMessage());
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'lospapus12321@gmail.com';
    $mail->Password   = 'nzfnruucsfkqwkyt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('lospapus12321@gmail.com', 'Turismo');
    $mail->addAddress($email, $nombre);
    $mail->isHTML(true);
    $mail->Subject = 'Confirmación de Compra - Turismo';

    $body = "<h2>Gracias por tu compra, $nombre</h2>";
    $body .= "<p>Tu pedido fue recibido y está siendo procesado.</p><ul>";

    foreach ($productos as $producto) {
        $cant = $carrito[$producto['id_producto']];
        $subtotal = $producto['precio_unitario'] * $cant;
        $body .= "<li>" . htmlspecialchars($producto['descripcion']) . " x $cant = $" . number_format($subtotal, 2) . "</li>";
    }

    $body .= "</ul>";
    $body .= "<p><strong>Total:</strong> $" . number_format($total, 2) . "</p>";
    $body .= "<p>Dirección: " . htmlspecialchars($direccion) . "</p>";
    $body .= "<p>Teléfono: $telefono | DNI: $dni</p>";
    $body .= "<p>Método de pago: $metodo_pago</p>";

    $mail->Body = $body;
    $mail->CharSet = 'UTF-8';
    $mail->send();

} catch (Exception $e) {
    error_log("Error al enviar el correo: " . $mail->ErrorInfo);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Compra Confirmada</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --color-primario: #0052cc;
      --color-acento: #007bff;
      --color-blanco: #fff;
      --color-negro: #1f2937;
      --color-fondo: #f9faff;
      --color-fondo-oscuro: #0f172a;
      --color-texto-oscuro: #f8fafc;
      --border-radius: 0.6rem;
    }

    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      background-color: var(--color-fondo);
      color: var(--color-negro);
      margin: 0;
      padding: 2rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      text-align: center;
      transition: background-color 0.3s, color 0.3s;
    }

    body.dark-mode {
      background-color: var(--color-fondo-oscuro);
      color: var(--color-texto-oscuro);
    }

    h1 {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: var(--color-primario);
    }

    p {
      font-size: 1.1rem;
      margin-bottom: 1rem;
    }

    ul {
      text-align: left;
      margin-top: 1rem;
      margin-bottom: 2rem;
    }

    ul li {
      margin-bottom: 0.5rem;
    }

    .btn-volver {
      background-color: var(--color-primario);
      color: var(--color-blanco);
      padding: 0.8rem 1.5rem;
      border: none;
      border-radius: var(--border-radius);
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .btn-volver:hover {
      background-color: var(--color-acento);
    }

    .checkmark {
      font-size: 4rem;
      color: green;
      margin-bottom: 1rem;
    }

    @media (max-width: 480px) {
      h1 {
        font-size: 1.5rem;
      }
      .checkmark {
        font-size: 3rem;
      }
    }
  </style>
</head>
<body>
  <div class="checkmark">✔️</div>
  <h1>¡Gracias por tu compra, <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>!</h1>
  <p>Tu pedido fue recibido correctamente y se está procesando.</p>
  <p>En breve recibirás un correo con el detalle de la compra.</p>

  <p><strong>Total:</strong> $<?= number_format($total, 2) ?></p>

  <a href="../Publico/home.php" class="btn-volver">Volver al inicio</a>

  <script>
    const modo = localStorage.getItem('theme');
    if (modo === 'dark') document.body.classList.add('dark-mode');
  </script>
</body>
</html>