<?php
session_start();
require_once __DIR__ . '/../Backend/config/db.php';

if (!isset($_SESSION['id_cliente'])) {
    header('Location: login_cliente.php');
    exit;
}

$id_cliente = $_SESSION['id_cliente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_pedido'])) {
    $id_pedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
    
    if ($id_pedido) {
        $stmt = $pdo->prepare("SELECT id_pedido FROM pedidos WHERE id_pedido = ? AND id_cliente = ? AND estado = 'Pendiente'");
        $stmt->execute([$id_pedido, $id_cliente]);
        $pedido_valido = $stmt->fetch();
        
        if ($pedido_valido) {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("DELETE FROM detalle_pedido WHERE id_pedido = ?");
                $stmt->execute([$id_pedido]);
                
                $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
                $stmt->execute([$id_pedido]);
                
                $pdo->commit();
                
                header("Location: historial.php?eliminado=1");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error al eliminar el pedido: " . $e->getMessage();
            }
        } else {
            $error = "No se puede eliminar el pedido. Solo puedes eliminar pedidos pendientes que te pertenezcan.";
        }
    }
}

if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1) {
    $mensaje = "Pedido eliminado correctamente.";
}

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id_cliente = ? ORDER BY fecha_pedido DESC");
$stmt->execute([$id_cliente]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Historial de Compras</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --color-primario: #0052cc;
      --color-blanco: #fff;
      --color-negro: #1f2937;
      --color-gris: #6b7280;
      --color-acento: #2563eb;
      --color-fondo: #f9fafb;
      --color-fondo-oscuro: #0f172a;
      --color-card-oscuro: #1e293b;
      --color-texto-oscuro: #f8fafc;
      --color-borde-oscuro: #334155;
      --color-error: #dc2626;
      --color-success: #10b981;
      --border-radius: 0.5rem;
    }

    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      margin: 0;
      padding: 1rem;
      background-color: var(--color-fondo);
      color: var(--color-negro);
      transition: background-color 0.3s, color 0.3s;
    }

    body.dark-mode {
      background-color: var(--color-fondo-oscuro);
      color: var(--color-texto-oscuro);
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    header h1 {
      margin: 0;
      font-size: 1.5rem;
    }

    button {
      padding: 0.6rem 1rem;
      font-weight: bold;
      font-size: 1rem;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      background-color: var(--color-primario);
      color: var(--color-blanco);
    }

    button:hover {
      background-color: var(--color-acento);
    }

    .btn-danger {
      background-color: var(--color-error);
    }

    .btn-danger:hover {
      background-color: #b91c1c;
    }

    .pedido {
      background: var(--color-blanco);
      padding: 1rem;
      border-radius: var(--border-radius);
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 1.5rem;
      transition: background 0.3s, color 0.3s;
      position: relative;
    }

    body.dark-mode .pedido {
      background: var(--color-card-oscuro);
      color: var(--color-texto-oscuro);
      border: 1px solid var(--color-borde-oscuro);
    }

    .pedido h3 {
      margin: 0 0 0.5rem;
    }

    .detalle {
      margin-top: 0.5rem;
      padding-left: 1rem;
    }

    .detalle li {
      margin-bottom: 0.3rem;
    }

    .sin-pedidos {
      text-align: center;
      margin-top: 2rem;
      font-size: 1.2rem;
      color: var(--color-gris);
    }

    .acciones {
      margin-top: 1rem;
      display: flex;
      gap: 0.5rem;
    }

    .mensaje {
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: var(--border-radius);
      text-align: center;
    }

    .mensaje-error {
      background-color: #fee2e2;
      color: var(--color-error);
      border: 1px solid #fca5a5;
    }

    .mensaje-success {
      background-color: #d1fae5;
      color: var(--color-success);
      border: 1px solid #6ee7b7;
    }

    body.dark-mode .mensaje-error {
      background-color: #7f1d1d;
      color: #fecaca;
      border-color: #ef4444;
    }

    body.dark-mode .mensaje-success {
      background-color: #064e3b;
      color: #6ee7b7;
      border-color: #10b981;
    }

    @media (max-width: 600px) {
      body {
        padding: 1rem;
      }
      
      .acciones {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>Historial de Compras</h1>
  <div>
    <button onclick="window.location.href='home.php'">🏠 Inicio</button>
    <button id="toggle-mode">🌙</button>
  </div>
</header>

<?php if (isset($error)): ?>
  <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
<?php elseif (isset($mensaje)): ?>
  <div class="mensaje mensaje-success"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<?php if (count($pedidos) === 0): ?>
  <p class="sin-pedidos">No tienes pedidos registrados aún.</p>
<?php else: ?>
  <?php foreach ($pedidos as $pedido): ?>
    <div class="pedido">
      <h3>Pedido #<?= $pedido['id_pedido'] ?> - <?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></h3>
      <p><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado']) ?></p>
      <p><strong>Total:</strong> $<?= number_format($pedido['total'], 2) ?></p>
      
      <ul class="detalle">
        <?php
          $stmt_detalle = $pdo->prepare("SELECT d.*, p.descripcion FROM detalle_pedido d JOIN productos p ON d.id_producto = p.id_producto WHERE d.id_pedido = ?");
          $stmt_detalle->execute([$pedido['id_pedido']]);
          $detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
          foreach ($detalles as $detalle):
        ?>
          <li><?= htmlspecialchars($detalle['descripcion']) ?> x <?= $detalle['cantidad'] ?> = $<?= number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2) ?></li>
        <?php endforeach; ?>
      </ul>
      
      <?php if ($pedido['estado'] === 'Pendiente'): ?>
        <div class="acciones">
          <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este pedido? Esta acción no se puede deshacer.');">
            <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
            <button type="submit" name="eliminar_pedido" class="btn-danger">❌ Eliminar Pedido</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<script>
  const toggleBtn = document.getElementById('toggle-mode');
  const body = document.body;

  function setMode(mode) {
    if (mode === 'dark') {
      body.classList.add('dark-mode');
      localStorage.setItem('theme', 'dark');
      toggleBtn.textContent = '☀️';
    } else {
      body.classList.remove('dark-mode');
      localStorage.setItem('theme', 'light');
      toggleBtn.textContent = '🌙';
    }
  }

  toggleBtn.addEventListener('click', () => {
    const mode = body.classList.contains('dark-mode') ? 'light' : 'dark';
    setMode(mode);
  });

  document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('theme') || 'light';
    setMode(saved);
  });
</script>

</body>
</html>