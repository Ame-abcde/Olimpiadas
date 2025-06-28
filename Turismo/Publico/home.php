<?php
session_start();
require_once __DIR__ . '/../Backend/config/db.php';

if (!isset($_SESSION['id_cliente'])) {
    header('Location: login_cliente.php');
    exit;
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$cliente_id = $_SESSION['id_cliente'];
$stmt = $pdo->prepare("SELECT nombre, email FROM clientes WHERE id_cliente = ?");
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

$total_items_carrito = !empty($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;

$stmt = $pdo->query("SELECT * FROM productos WHERE disponible=1 ORDER BY descripcion");
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($cliente['nombre']) ?> | Agencia de Turismo</title>
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
      --color-white: #1e293b;
      --gradient-bg: linear-gradient(135deg, #1e40af 0%, #0f172a 100%);
    }

    [data-theme="dark"] .search-input,
    [data-theme="dark"] .filter-input,
    [data-theme="dark"] .quantity-input {
      background-color: var(--color-gray-light);
      color: var(--color-dark);
      border-color: var(--color-gray);
    }

    [data-theme="dark"] .product-card {
      background-color: var(--color-gray-light);
      color: var(--color-dark);
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

    .cart-badge {
      position: absolute;
      top: -0.5rem;
      right: -0.5rem;
      background-color: var(--color-danger);
      color: var(--color-white);
      font-size: 0.75rem;
      min-width: 1.25rem;
      height: 1.25rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cart-btn {
      position: relative;
    }

    main {
      flex: 1;
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
    }

    .hero {
      text-align: center;
      margin-bottom: 3rem;
      padding: 2rem 1rem;
    }

    .hero-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      background: linear-gradient(to right, var(--color-primary), var(--color-primary-dark));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
      font-size: 1.2rem;
      color: var(--color-gray);
      max-width: 700px;
      margin: 0 auto;
    }

    .search-section {
      background-color: var(--color-white);
      border-radius: var(--border-radius);
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .search-input {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 1px solid var(--color-gray-light);
      border-radius: var(--border-radius-sm);
      font-size: 1rem;
      margin-bottom: 1rem;
      transition: var(--transition);
    }

    .search-input:focus {
      outline: none;
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .filter-group {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .filter-input {
      flex: 1;
      min-width: 150px;
      padding: 0.8rem 1rem;
      border: 1px solid var(--color-gray-light);
      border-radius: var(--border-radius-sm);
      font-size: 1rem;
    }

    .filter-btn {
      background-color: var(--color-primary);
      color: var(--color-white);
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: var(--border-radius-sm);
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
    }

    .filter-btn:hover {
      background-color: var(--color-primary-dark);
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }

    .product-card {
      background-color: var(--color-white);
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      display: flex;
      flex-direction: column;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--box-shadow-lg);
    }

    .product-image {
      height: 180px;
      background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-primary) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--color-white);
      font-size: 3rem;
    }

    .product-content {
      padding: 1.5rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .product-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: var(--color-dark);
    }

    .product-meta {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
      color: var(--color-gray);
    }

    .product-price {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--color-primary);
      margin: 1rem 0;
    }

    .product-form {
      margin-top: auto;
      display: flex;
      gap: 0.75rem;
    }

    .quantity-input {
      width: 70px;
      padding: 0.6rem;
      border: 1px solid var(--color-gray-light);
      border-radius: var(--border-radius-sm);
      text-align: center;
    }

    .add-to-cart-btn {
      flex: 1;
      background-color: var(--color-primary);
      color: var(--color-white);
      border: none;
      padding: 0.6rem 1rem;
      border-radius: var(--border-radius-sm);
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .add-to-cart-btn:hover {
      background-color: var(--color-primary-dark);
    }

    footer {
      background: var(--gradient-bg);
      color: var(--color-white);
      text-align: center;
      padding: 1.5rem;
      margin-top: 3rem;
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

    .toast-icon {
      font-size: 1.2rem;
    }

    .fly-item {
      position: fixed;
      width: 40px;
      height: 40px;
      background-color: var(--color-primary);
      border-radius: 50%;
      z-index: 999;
      pointer-events: none;
      transition: transform 0.8s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.8s;
    }

    .theme-toggle {
      background-color: var(--color-dark);
      color: var(--color-white);
      border: none;
    }

    .theme-toggle:hover {
      background-color: var(--color-gray);
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
      }

      .user-actions {
        width: 100%;
        justify-content: space-between;
      }

      .hero-title {
        font-size: 2rem;
      }

      .products-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 480px) {
      .filter-group {
        flex-direction: column;
      }

      .filter-input, .filter-btn {
        width: 100%;
      }

      .product-form {
        flex-direction: column;
      }

      .quantity-input {
        width: 100%;
      }
    }
    .fly-item {
    position: fixed;
    width: 40px;
    height: 40px;
    background-color: var(--color-primary);
    border-radius: 50%;
    z-index: 999;
    pointer-events: none;
    transition: transform 0.8s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.8s;
}
  </style>
</head>
<body data-theme="light">
  <header>
    <div class="logo">
      <i class="fas fa-plane logo-icon"></i>
      <span class="logo-text">Turismo</span>
    </div>

    <div class="user-actions">
      <a href="carrito.php" class="btn btn-primary cart-btn">
  <i class="fas fa-shopping-cart"></i>
  <span>Carrito</span>
  <?php if ($total_items_carrito > 0): ?>
    <span id="cart-count" class="cart-badge"><?= $total_items_carrito ?></span>

  <?php endif; ?>
</a>
      
      <button onclick="location.href='historial.php'" class="btn btn-secondary">
        <i class="fas fa-history"></i>
        <span>Historial</span>
      </button>
      
      <a href="../Backend/logout.php" class="btn btn-danger">
        <i class="fas fa-sign-out-alt"></i>
        <span>Salir</span>
      </a>
      
      <button id="theme-toggle" class="btn theme-toggle">
        <i class="fas fa-moon"></i>
        <span>Modo oscuro</span>
      </button>
    </div>
  </header>

  <main>
    <section class="hero">
      <h1 class="hero-title">Hola <?= htmlspecialchars($cliente['nombre']) ?></h1>
      <p class="hero-subtitle">Encuentra tus próximas vacaciones ideales</p>
    </section>

    <section class="search-section">
      <h2 class="section-title">
        <i class="fas fa-search"></i>
        Buscar paquetes
      </h2>
      <input type="text" id="search-input" class="search-input" placeholder="Buscar por destino...">
      
      <h2 class="section-title" style="margin-top: 1.5rem;">
        <i class="fas fa-filter"></i>
        Filtrar por precio
      </h2>
      <div class="filter-group">
        <input type="number" id="min-price" class="filter-input" placeholder="Mínimo" min="0">
        <input type="number" id="max-price" class="filter-input" placeholder="Máximo" min="0">
        <button id="filter-btn" class="filter-btn">
          <i class="fas fa-sliders-h"></i>
          Filtrar
        </button>
      </div>
    </section>

    <div class="products-grid" id="products-grid">
      <?php if (!empty($productos)): ?>
        <?php foreach ($productos as $prod): ?>
          <article class="product-card" data-price="<?= $prod['precio_unitario'] ?>" 
                   data-desc="<?= strtolower(htmlspecialchars($prod['descripcion'])) ?>" 
                   data-destino="<?= strtolower(htmlspecialchars($prod['destino'])) ?>">
            <div class="product-image">
              <i class="fas fa-map-marked-alt"></i>
            </div>
            <div class="product-content">
              <h3 class="product-title"><?= htmlspecialchars($prod['descripcion']) ?></h3>
              <div class="product-meta">
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($prod['destino']) ?></span>
                <span><i class="fas fa-calendar-day"></i> <?= $prod['duracion_dias'] ?> días</span>
              </div>
              <div class="product-price">$<?= number_format($prod['precio_unitario'], 2) ?></div>
              
<form class="product-form" method="POST" action="../Backend/agregar_carrito.php" target="hiddenFrame">
    <input type="hidden" name="id_producto" value="<?= $prod['id_producto'] ?>">
    <input type="number" name="cantidad" class="quantity-input" value="1" min="1">
    <button type="submit" class="add-to-cart-btn">
        <i class="fas fa-cart-plus"></i>
        Agregar
    </button>
</form>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
          <p style="color: var(--color-gray);">No hay paquetes disponibles</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    <p>&copy; <?= date('Y') ?> Turismo</p>
  </footer>

</div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const themeBtn = document.getElementById('theme-toggle');
      const html = document.documentElement;
      const savedTheme = localStorage.getItem('theme') || 'light';
      
      html.setAttribute('data-theme', savedTheme);
      updateThemeIcon(savedTheme);
      
      themeBtn.addEventListener('click', () => {
        const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
      });
      
      function updateThemeIcon(theme) {
        const icon = themeBtn.querySelector('i');
        const text = themeBtn.querySelector('span');
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        text.textContent = theme === 'dark' ? 'Modo claro' : 'Modo oscuro';
      }
      
      const searchInput = document.getElementById('search-input');
      const minPrice = document.getElementById('min-price');
      const maxPrice = document.getElementById('max-price');
      const products = document.querySelectorAll('.product-card');
      
      function filterProducts() {
        const search = searchInput.value.toLowerCase();
        const min = parseFloat(minPrice.value) || 0;
        const max = parseFloat(maxPrice.value) || Infinity;
        
        products.forEach(product => {
          const desc = product.dataset.desc;
          const destino = product.dataset.destino;
          const price = parseFloat(product.dataset.price);
          
          const matchesSearch = desc.includes(search) || destino.includes(search);
          const matchesPrice = price >= min && price <= max;
          
          product.style.display = matchesSearch && matchesPrice ? 'flex' : 'none';
        });
      }
      
      searchInput.addEventListener('input', filterProducts);
      minPrice.addEventListener('input', filterProducts);
      maxPrice.addEventListener('input', filterProducts);
      document.getElementById('filter-btn').addEventListener('click', filterProducts);
      
      document.querySelectorAll('.product-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
          e.preventDefault();
          
          const productImg = this.closest('.product-card').querySelector('.product-image');
          const cartBtn = document.querySelector('.cart-btn');
          
          const flyItem = document.createElement('div');
          flyItem.className = 'fly-item';
          
          const imgRect = productImg.getBoundingClientRect();
          flyItem.style.left = `${imgRect.left}px`;
          flyItem.style.top = `${imgRect.top}px`;
          document.body.appendChild(flyItem);
          
          setTimeout(() => {
            const cartRect = cartBtn.getBoundingClientRect();
            flyItem.style.transform = `translate(${cartRect.left - imgRect.left}px, ${cartRect.top - imgRect.top}px)`;
            flyItem.style.opacity = '0';
          }, 10);
          
          setTimeout(() => flyItem.remove(), 800);
          
          const formData = new FormData(this);
          try {
            await fetch(this.action, {
              method: 'POST',
              body: formData
            });
            
            this.querySelector('input[name="cantidad"]').value = 1;
          } catch (error) {
            console.error('Error:', error);
          }
        });
      });
    });
  </script>
</body>
</html>