<?php
session_start();
require_once '../Backend/config/db.php';

// Redirigir si ya está logueado
if (isset($_SESSION['id_cliente'])) {
    header('Location: ../Publico/home.php');
    exit;
}

// Manejo de errores
$error_msg = '';
if (isset($_SESSION['registro_error'])) {
    $error_msg = htmlspecialchars($_SESSION['registro_error']);
    unset($_SESSION['registro_error']);
}

// Mensajes de éxito
$success_msg = '';
if (isset($_SESSION['registro_success'])) {
    $success_msg = htmlspecialchars($_SESSION['registro_success']);
    unset($_SESSION['registro_success']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente | Turismo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --color-primary: #3b82f6;
        --color-primary-dark: #2563eb;
        --color-danger: #ef4444;
        --color-success: #10b981;
        --color-light: #f8fafc;
        --color-dark: #1e293b;
        --color-gray: #64748b;
        --color-white: #ffffff;
        --gradient-bg: linear-gradient(to right, #003da0, #60a5fa);
        --border-radius: 0.75rem;
        --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    body {
        background: var(--gradient-bg);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .card {
        background: var(--color-white);
        padding: 2.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 450px;
        transition: var(--transition);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .card-icon {
        width: 70px;
        height: 70px;
        background-color: var(--color-primary);
        color: var(--color-white);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }

    .card-title {
        font-size: 1.6rem;
        font-weight: 600;
        color: var(--color-dark);
        margin-bottom: 0.5rem;
    }

    .card-subtitle {
        color: var(--color-gray);
        font-size: 0.9rem;
    }

    .alert {
        padding: 0.75rem 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--color-danger);
        border-left: 4px solid var(--color-danger);
    }

    .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--color-success);
        border-left: 4px solid var(--color-success);
    }

    .form-group {
        margin-bottom: 1.25rem;
        position: relative;
    }

    .form-label {
        display: block;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: var(--color-dark);
        font-size: 0.9rem;
    }

    .form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        font-size: 1rem;
        border: 1px solid #d1d5db;
        border-radius: var(--border-radius);
        background-color: var(--color-white);
        color: var(--color-dark);
        transition: var(--transition);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .btn {
        width: 100%;
        padding: 0.9rem;
        background-color: var(--color-primary);
        color: var(--color-white);
        font-weight: 600;
        border: none;
        border-radius: var(--border-radius);
        font-size: 1rem;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn:hover {
        background-color: var(--color-primary-dark);
        transform: translateY(-2px);
    }

    .card-footer {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.9rem;
        color: var(--color-gray);
    }

    .card-footer a {
        color: var(--color-primary);
        text-decoration: none;
        font-weight: 500;
    }

    .card-footer a:hover {
        text-decoration: underline;
    }

    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 2.3rem;
        color: var(--color-gray);
        cursor: pointer;
        transition: var(--transition);
    }

    .password-toggle:hover {
        color: var(--color-primary);
    }

    @media (max-width: 480px) {
        .card {
            padding: 1.75rem;
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
    }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1 class="card-title">Crear Cuenta</h1>
            <p class="card-subtitle">Regístrate para acceder a todos nuestros servicios</p>
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $error_msg ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= $success_msg ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <form action="../Backend/procesar_register_cliente.php" method="POST" id="registerForm">
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       placeholder="Ej: Juan" required minlength="2" maxlength="50"
                       pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+">
            </div>
            
            <div class="form-group">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" id="apellido" name="apellido" class="form-control" 
                       placeholder="Ej: Pérez" required minlength="2" maxlength="50"
                       pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+">
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="tu@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Mínimo 8 caracteres" required minlength="8">
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i>
                <span>Registrarse</span>
            </button>
        </form>
        
        <div class="card-footer">
            ¿Ya tienes cuenta? <a href="login_cliente.php">Inicia sesión</a>
        </div>
    </div>

    <script>
    // Mostrar/ocultar contraseña
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            this.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordField.type = 'password';
            this.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
    
    // Validación del formulario
    const form = document.getElementById('registerForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function() {
        // Mostrar estado de carga
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Registrando...</span>';
        submitBtn.disabled = true;
    });
    
    // Validación en tiempo real
    form.addEventListener('input', function(e) {
        const target = e.target;
        
        if (target.id === 'nombre' || target.id === 'apellido') {
            const regex = /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/;
            if (!regex.test(target.value)) {
                target.setCustomValidity('Solo se permiten letras y espacios');
            } else {
                target.setCustomValidity('');
            }
        }
    });
    </script>
</body>
</html>