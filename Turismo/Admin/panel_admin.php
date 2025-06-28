<?php
session_start();
require_once '../Backend/config/db.php';

if (empty($_SESSION['id_admin'])) {
    header('Location: admin_login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false];
    
    try {
        switch($_POST['action']) {
            case 'addProduct':
                if (empty($_POST['codigo']) || empty($_POST['descripcion']) || empty($_POST['precio'])) {
                    throw new Exception("Todos los campos son requeridos");
                }
                
                $stmt = $pdo->prepare("INSERT INTO productos (codigo_producto, descripcion, precio_unitario) VALUES (?, ?, ?)");
                $stmt->execute([
                    $_POST['codigo'],
                    $_POST['descripcion'],
                    floatval($_POST['precio'])
                ]);
                $response = ['success' => true, 'msg' => 'Producto agregado correctamente'];
                break;

            case 'fetchProducts':
                $products = $pdo->query("SELECT * FROM productos ORDER BY codigo_producto")->fetchAll();
                $response = $products;
                break;

            case 'deleteProduct':
                if (empty($_POST['codigo'])) {
                    throw new Exception("Código de producto no especificado");
                }
                
                $stmt = $pdo->prepare("DELETE FROM productos WHERE codigo_producto = ?");
                $stmt->execute([$_POST['codigo']]);
                
                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'msg' => 'Producto eliminado correctamente'];
                } else {
                    throw new Exception("No se encontró el producto a eliminar");
                }
                break;

            case 'fetchPendingOrders':
                $orders = $pdo->query("
                    SELECT p.id_pedido, c.nombre, c.apellido,
                    DATE_FORMAT(p.fecha_pedido, '%d/%m/%Y %H:%i') as fecha_pedido, 
                    p.total, p.estado
                    FROM pedidos p 
                    JOIN clientes c USING(id_cliente)
                    WHERE p.estado = 'Pendiente'
                    ORDER BY p.fecha_pedido DESC
                ")->fetchAll();
                $response = $orders;
                break;

            case 'fetchAccountDate':
                $orders = $pdo->query("
                    SELECT p.id_pedido, c.nombre, c.apellido,
                    DATE_FORMAT(p.fecha_pedido, '%d/%m/%Y %H:%i') as fecha_pedido, 
                    p.total, p.estado
                    FROM pedidos p 
                    JOIN clientes c USING(id_cliente)
                    ORDER BY p.fecha_pedido DESC
                    LIMIT 100
                ")->fetchAll();
                $response = $orders;
                break;

            case 'fetchAccountClient':
                $orders = $pdo->query("
                    SELECT p.id_pedido, c.nombre, c.apellido,
                    DATE_FORMAT(p.fecha_pedido, '%d/%m/%Y %H:%i') as fecha_pedido, 
                    p.total, p.estado
                    FROM pedidos p 
                    JOIN clientes c USING(id_cliente)
                    ORDER BY c.nombre, c.apellido, p.fecha_pedido DESC
                    LIMIT 100
                ")->fetchAll();
                $response = $orders;
                break;

            case 'updateOrderStatus':
                $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
                $stmt->execute([$_POST['estado'], $_POST['id_pedido']]);
                $response = ['success' => true, 'msg' => 'Estado actualizado'];
                break;

            default: 
                throw new Exception("Acción no válida");
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'msg' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo · Turismo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --dark-bg: #1a1a2e;
            --dark-card: #16213e;
            --dark-text: #e6e6e6;
            --dark-border: #2d4059;
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
        }

        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: white;
            padding: var(--space-md) 0;
            position: fixed;
            height: 100vh;
            transition: var(--transition);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 var(--space-md) var(--space-md);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: var(--space-md);
        }

        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .sidebar-brand i {
            font-size: 1.5rem;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: var(--space-xs);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-sm) var(--space-md);
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }

        .nav-link i {
            width: 24px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            transition: var(--transition);
        }

        .topbar {
            background-color: white;
            padding: var(--space-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        body.dark-mode .topbar {
            background-color: var(--dark-card);
            border-bottom: 1px solid var(--dark-border);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .toggle-dark-mode {
            background: none;
            border: none;
            color: var(--dark);
            cursor: pointer;
            font-size: 1.25rem;
            transition: var(--transition);
        }

        body.dark-mode .toggle-dark-mode {
            color: var(--dark-text);
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--dark);
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        body.dark-mode .logout-btn {
            color: var(--dark-text);
        }

        .logout-btn:hover {
            background-color: rgba(0,0,0,0.05);
        }

        body.dark-mode .logout-btn:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .content-wrapper {
            padding: var(--space-md);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
        }

        .card {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-md);
            overflow: hidden;
            transition: var(--transition);
        }

        body.dark-mode .card {
            background-color: var(--dark-card);
            border: 1px solid var(--dark-border);
        }

        .card-header {
            padding: var(--space-md);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        body.dark-mode .card-header {
            border-bottom-color: var(--dark-border);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: var(--space-md);
        }

        .form-group {
            margin-bottom: var(--space-md);
        }

        .form-label {
            display: block;
            margin-bottom: var(--space-xs);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: var(--space-sm) var(--space-md);
            border: 1px solid #ddd;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: var(--transition);
        }

        body.dark-mode .form-control {
            background-color: var(--dark-card);
            border-color: var(--dark-border);
            color: var(--dark-text);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .form-row {
            display: flex;
            gap: var(--space-md);
            margin-bottom: var(--space-md);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-sm) var(--space-md);
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            gap: var(--space-sm);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-sm {
            padding: var(--space-xs) var(--space-sm);
            font-size: 0.875rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .table th {
            background-color: var(--primary);
            color: white;
            padding: var(--space-sm) var(--space-md);
            text-align: left;
            font-weight: 500;
            position: sticky;
            top: 0;
        }

        .table td {
            padding: var(--space-sm) var(--space-md);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            vertical-align: middle;
        }

        body.dark-mode .table td {
            border-bottom-color: var(--dark-border);
        }

        .table tr:hover td {
            background-color: rgba(0,0,0,0.02);
        }

        body.dark-mode .table tr:hover td {
            background-color: rgba(255,255,255,0.05);
        }

        .badge {
            display: inline-block;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-warning {
            background-color: #f8961e;
            color: white;
        }

        .badge-success {
            background-color: #4cc9f0;
            color: white;
        }

        .badge-danger {
            background-color: #f72585;
            color: white;
        }

        .toast-container {
            position: fixed;
            bottom: var(--space-md);
            right: var(--space-md);
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }

        .toast {
            padding: var(--space-sm) var(--space-md);
            border-radius: var(--radius-sm);
            color: white;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            max-width: 300px;
            animation: slideIn 0.3s ease forwards;
        }

        .toast.success {
            background-color: var(--success);
        }

        .toast.error {
            background-color: var(--danger);
        }

        .toast.info {
            background-color: var(--primary);
        }

        .toast i {
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-brand span, .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: var(--space-sm);
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Turismo Admin</span>
                </div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active" data-tab="products">
                        <i class="fas fa-box-open"></i>
                        <span>Productos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-tab="pending">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pedidos Pendientes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-tab="account-date">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Cuenta por Fecha</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-tab="account-client">
                        <i class="fas fa-users"></i>
                        <span>Cuenta por Cliente</span>
                    </a>
                </li>
            </ul>
        </aside>

        <div class="main-content">
            <header class="topbar">
                <div class="breadcrumb">
                    <span>Productos</span>
                </div>
                <div class="topbar-actions">
                    <button class="toggle-dark-mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    <a href="?logout=1" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                    <div class="user-menu">
                        <div class="user-avatar">AD</div>
                        <span>Admin</span>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <section id="products" class="section active">
                    <div class="page-header">
                        <h1 class="page-title">Gestión de Productos</h1>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Productos</h3>
                        </div>
                        <div class="card-body">
                            <form id="addProductForm" class="form-row" style="margin-bottom: var(--space-md);">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="codigo" placeholder="Código" required>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="descripcion" placeholder="Descripción" required>
                                </div>
                                <div class="form-group">
                                    <input type="number" step="0.01" class="form-control" name="precio" placeholder="Precio" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    <span>Agregar</span>
                                </button>
                            </form>
                            
                            <div class="table-responsive">
                                <table class="table" id="productsTable">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Descripción</th>
                                            <th>Precio Unitario</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="pending" class="section">
                    <div class="page-header">
                        <h1 class="page-title">Pedidos Pendientes</h1>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Pedidos</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="pendingTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="account-date" class="section">
                    <div class="page-header">
                        <h1 class="page-title">Cuenta por Fecha</h1>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Historial de Pedidos</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="accountDateTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="account-client" class="section">
                    <div class="page-header">
                        <h1 class="page-title">Cuenta por Cliente</h1>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pedidos por Cliente</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="accountClientTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <div id="toast-container" class="toast-container"></div>

    <script>
        const DOM = {
            toggleDarkMode: document.querySelector('.toggle-dark-mode'),
            navLinks: document.querySelectorAll('.nav-link'),
            sections: document.querySelectorAll('.section'),
            addProductForm: document.getElementById('addProductForm'),
            toastContainer: document.getElementById('toast-container')
        };

        const state = {
            darkMode: localStorage.getItem('darkMode') === 'true',
            currentTab: 'products',
            refreshInterval: null
        };

        function init() {
            if (state.darkMode) {
                document.body.classList.add('dark-mode');
                DOM.toggleDarkMode.innerHTML = '<i class="fas fa-sun"></i>';
            }

            setupEventListeners();
            loadCurrentTab();
            setupAutoRefresh();
        }

        function setupEventListeners() {
            DOM.toggleDarkMode.addEventListener('click', toggleDarkMode);

            DOM.navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tab = link.dataset.tab;
                    switchTab(tab);
                });
            });

            DOM.addProductForm.addEventListener('submit', (e) => {
                e.preventDefault();
                addProduct();
            });
        }

        function setupAutoRefresh() {
            if (state.refreshInterval) {
                clearInterval(state.refreshInterval);
            }

            state.refreshInterval = setInterval(() => {
                if (document.visibilityState === 'visible') {
                    loadCurrentTab();
                }
            }, 30000);
        }

        function toggleDarkMode() {
            state.darkMode = !state.darkMode;
            document.body.classList.toggle('dark-mode');
            
            if (state.darkMode) {
                DOM.toggleDarkMode.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                DOM.toggleDarkMode.innerHTML = '<i class="fas fa-moon"></i>';
            }
            
            localStorage.setItem('darkMode', state.darkMode);
        }

        function switchTab(tab) {
            state.currentTab = tab;
            
            DOM.navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.dataset.tab === tab) {
                    link.classList.add('active');
                }
            });
            
            DOM.sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === tab) {
                    section.classList.add('active');
                }
            });
            
            document.querySelector('.breadcrumb span').textContent = 
                document.querySelector(`.nav-link[data-tab="${tab}"] span`).textContent;
            
            loadCurrentTab();
            setupAutoRefresh();
        }

        function loadCurrentTab() {
            switch(state.currentTab) {
                case 'products':
                    loadProducts();
                    break;
                case 'pending':
                    loadPendingOrders();
                    break;
                case 'account-date':
                    loadAccountDate();
                    break;
                case 'account-client':
                    loadAccountClient();
                    break;
            }
        }

        function loadProducts() {
            fetchData('fetchProducts')
                .then(data => renderProducts(data))
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error al cargar productos', 'error');
                });
        }

        function loadPendingOrders() {
            fetchData('fetchPendingOrders')
                .then(data => renderPendingOrders(data))
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error al cargar pedidos', 'error');
                });
        }

        function loadAccountDate() {
            fetchData('fetchAccountDate')
                .then(data => renderAccountDate(data))
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error al cargar historial', 'error');
                });
        }

        function loadAccountClient() {
            fetchData('fetchAccountClient')
                .then(data => renderAccountClient(data))
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error al cargar clientes', 'error');
                });
        }

        function fetchData(action, params = {}) {
            const formData = new FormData();
            formData.append('action', action);
            
            for (const key in params) {
                formData.append(key, params[key]);
            }
            
            return fetch('', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            });
        }

        function renderProducts(products) {
            const tbody = document.querySelector('#productsTable tbody');
            tbody.innerHTML = '';
            
            if (!products || products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No hay productos registrados</td></tr>';
                return;
            }
            
            products.forEach(product => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${product.codigo_producto}</td>
                    <td>${product.descripcion}</td>
                    <td>$${parseFloat(product.precio_unitario).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="deleteProduct('${product.codigo_producto}')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderPendingOrders(orders) {
            const tbody = document.querySelector('#pendingTable tbody');
            tbody.innerHTML = '';
            
            if (!orders || orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay pedidos pendientes</td></tr>';
                return;
            }
            
            orders.forEach(order => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${order.id_pedido}</td>
                    <td>${order.nombre} ${order.apellido}</td>
                    <td>${order.fecha_pedido}</td>
                    <td>$${parseFloat(order.total).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="updateOrderStatus(${order.id_pedido}, 'Entregado')">
                            <i class="fas fa-check"></i> Entregar
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="updateOrderStatus(${order.id_pedido}, 'Anulado')">
                            <i class="fas fa-times"></i> Anular
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderAccountDate(orders) {
            const tbody = document.querySelector('#accountDateTable tbody');
            tbody.innerHTML = '';
            
            if (!orders || orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay pedidos registrados</td></tr>';
                return;
            }
            
            orders.forEach(order => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${order.id_pedido}</td>
                    <td>${order.nombre} ${order.apellido}</td>
                    <td>${order.fecha_pedido}</td>
                    <td><span class="badge ${getStatusBadgeClass(order.estado)}">${order.estado}</span></td>
                    <td>$${parseFloat(order.total).toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderAccountClient(orders) {
            const tbody = document.querySelector('#accountClientTable tbody');
            tbody.innerHTML = '';
            
            if (!orders || orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay pedidos registrados</td></tr>';
                return;
            }
            
            orders.forEach(order => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${order.id_pedido}</td>
                    <td>${order.nombre} ${order.apellido}</td>
                    <td>${order.fecha_pedido}</td>
                    <td><span class="badge ${getStatusBadgeClass(order.estado)}">${order.estado}</span></td>
                    <td>$${parseFloat(order.total).toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        function addProduct() {
            const formData = new FormData(DOM.addProductForm);
            
            fetchData('addProduct', {
                codigo: formData.get('codigo'),
                descripcion: formData.get('descripcion'),
                precio: formData.get('precio')
            })
            .then(data => {
                if (data.success) {
                    showToast('Producto agregado correctamente', 'success');
                    DOM.addProductForm.reset();
                    loadProducts();
                } else {
                    throw new Error(data.msg || 'Error al agregar producto');
                }
            })
            .catch(error => {
                showToast(error.message, 'error');
                console.error('Error:', error);
            });
        }

        function deleteProduct(code) {
            if (!confirm(`¿Estás seguro de eliminar el producto ${code}?`)) {
                return;
            }
            
            fetchData('deleteProduct', { codigo: code })
                .then(data => {
                    if (data.success) {
                        showToast('Producto eliminado correctamente', 'success');
                        loadProducts();
                    } else {
                        throw new Error(data.msg || 'Error al eliminar producto');
                    }
                })
                .catch(error => {
                    showToast(error.message, 'error');
                    console.error('Error:', error);
                });
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'Pendiente': return 'badge-warning';
                case 'Entregado': return 'badge-success';
                case 'Anulado': return 'badge-danger';
                default: return '';
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            let icon = '';
            switch(type) {
                case 'success': icon = '<i class="fas fa-check-circle"></i>'; break;
                case 'error': icon = '<i class="fas fa-exclamation-circle"></i>'; break;
                case 'info': icon = '<i class="fas fa-info-circle"></i>'; break;
            }
            
            toast.innerHTML = `${icon} ${message}`;
            DOM.toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function updateOrderStatus(orderId, status) {
            fetchData('updateOrderStatus', {
                id_pedido: orderId,
                estado: status
            })
            .then(data => {
                if (data.success) {
                    showToast(`Pedido #${orderId} actualizado a ${status}`, 'success');
                    loadPendingOrders();
                    
                    if (state.currentTab === 'account-date' || state.currentTab === 'account-client') {
                        loadCurrentTab();
                    }
                } else {
                    throw new Error(data.msg || 'Error al actualizar pedido');
                }
            })
            .catch(error => {
                showToast(error.message, 'error');
                console.error('Error:', error);
            });
        }

        window.updateOrderStatus = updateOrderStatus;
        window.deleteProduct = deleteProduct;

        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
