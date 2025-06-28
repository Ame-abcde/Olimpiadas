<?php
session_start();
require '../Backend/config/db.php';

if (!empty($_SESSION['id_admin'])) {
    header('Location: panel_admin.php');
    exit;
}

$error_msg = '';
if (!empty($_SESSION['login_error'])) {
    $error_msg = htmlspecialchars($_SESSION['login_error']);
    unset($_SESSION['login_error']);
}

$success_msg = '';
if (!empty($_SESSION['success_msg'])) {
    $success_msg = htmlspecialchars($_SESSION['success_msg']);
    unset($_SESSION['success_msg']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrador | Turismo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --azul-principal: #3a5ae8;
        --azul-oscuro: #2c4bd4;
        --blanco: #ffffff;
        --gris-claro: #f5f7fa;
        --gris-medio: #e1e5eb;
        --gris-oscuro: #6b7280;
        --negro: #1f2937;
        --rojo-error: #ef4444;
        --verde-exito: #10b981;
        --sombra: 0 4px 12px rgba(0, 0, 0, 0.08);
        --radio-borde: 8px;
        --transicion: all 0.3s ease;
    }
    
    .modo-oscuro {
        --azul-principal: #5b7bf0;
        --azul-oscuro: #4a6ad8;
        --blanco: #1e1e2d;
        --gris-claro: #121218;
        --gris-medio: #2d2d3a;
        --gris-oscuro: #a1a1b3;
        --negro: #f3f4f6;
        --sombra: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--gris-claro);
        color: var(--negro);
        line-height: 1.5;
        transition: var(--transicion);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    
    .contenedor-login {
        width: 100%;
        max-width: 420px;
        background: var(--blanco);
        border-radius: var(--radio-borde);
        box-shadow: var(--sombra);
        padding: 2.5rem;
        transition: var(--transicion);
    }
    
    .cabecera-login {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .icono-login {
        width: 70px;
        height: 70px;
        background-color: var(--azul-principal);
        color: var(--blanco);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }
    
    .titulo-login {
        font-size: 1.6rem;
        font-weight: 600;
        color: var(--azul-principal);
        margin-bottom: 0.5rem;
    }
    
    .subtitulo-login {
        color: var(--gris-oscuro);
        font-size: 0.9rem;
    }
    
    .alerta {
        padding: 12px 16px;
        border-radius: var(--radio-borde);
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alerta-error {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--rojo-error);
        border-left: 4px solid var(--rojo-error);
    }
    
    .alerta-exito {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--verde-exito);
        border-left: 4px solid var(--verde-exito);
    }
    
    .grupo-formulario {
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .etiqueta {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--negro);
    }
    
    .campo {
        width: 100%;
        padding: 12px 16px;
        font-size: 1rem;
        border: 1px solid var(--gris-medio);
        border-radius: var(--radio-borde);
        background-color: var(--blanco);
        color: var(--negro);
        transition: var(--transicion);
    }
    
    .campo:focus {
        outline: none;
        border-color: var(--azul-principal);
        box-shadow: 0 0 0 3px rgba(58, 90, 232, 0.2);
    }
    
    .mostrar-pass {
        position: absolute;
        right: 15px;
        top: 38px;
        color: var(--gris-oscuro);
        cursor: pointer;
        transition: var(--transicion);
    }
    
    .mostrar-pass:hover {
        color: var(--azul-principal);
    }
    
    .boton {
        width: 100%;
        padding: 14px;
        background-color: var(--azul-principal);
        color: var(--blanco);
        font-weight: 600;
        border: none;
        border-radius: var(--radio-borde);
        font-size: 1rem;
        cursor: pointer;
        transition: var(--transicion);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .boton:hover {
        background-color: var(--azul-oscuro);
        transform: translateY(-1px);
    }
    
    .enlace {
        color: var(--azul-principal);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transicion);
    }
    
    .enlace:hover {
        text-decoration: underline;
    }
    
    .olvido-pass {
        display: block;
        text-align: right;
        font-size: 0.85rem;
        margin: -10px 0 15px;
    }
    
    .pie-login {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.9rem;
        color: var(--gris-oscuro);
    }
    
    .boton-modo {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--azul-principal);
        color: var(--blanco);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: var(--transicion);
        z-index: 100;
    }
    
    .boton-modo:hover {
        transform: scale(1.1);
    }
    
    @media (max-width: 480px) {
        .contenedor-login {
            padding: 1.8rem;
        }
        
        .boton-modo {
            top: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
        }
    }
    </style>
</head>
<body>
    <button class="boton-modo" id="cambiar-modo" aria-label="Cambiar modo de color">
        <i class="fas fa-moon"></i>
    </button>
    
    <div class="contenedor-login">
        <div class="cabecera-login">
            <div class="icono-login">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="titulo-login">Acceso Administrador</h1>
            <p class="subtitulo-login">Ingresa tus credenciales para continuar</p>
            
            <?php if (!empty($error_msg)): ?>
                <div class="alerta alerta-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $error_msg ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_msg)): ?>
                <div class="alerta alerta-exito">
                    <i class="fas fa-check-circle"></i>
                    <span><?= $success_msg ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <form action="../Backend/procesar_login_admin.php" method="POST" id="formulario-login">
            <div class="grupo-formulario">
                <label for="email" class="etiqueta">Correo electrónico</label>
                <input type="email" id="email" name="email" class="campo" placeholder="tu@email.com" required>
            </div>
            
            <div class="grupo-formulario">
                <label for="password" class="etiqueta">Contraseña</label>
                <input type="password" id="password" name="password" class="campo" placeholder="••••••••" required>
                <i class="fas fa-eye mostrar-pass" id="mostrar-pass"></i>
            </div>
            
            <button type="submit" class="boton" id="boton-login">
                <i class="fas fa-sign-in-alt"></i>
                <span>Ingresar</span>
            </button>
        </form>
        
        <div class="pie-login">
            ¿No tienes cuenta? <a href="admin_register.php" class="enlace">Regístrate aquí</a>
        </div>
    </div>

    <script>
    const botonMostrarPass = document.getElementById('mostrar-pass');
    const campoPassword = document.getElementById('password');
    
    botonMostrarPass.addEventListener('click', function() {
        if (campoPassword.type === 'password') {
            campoPassword.type = 'text';
            this.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            campoPassword.type = 'password';
            this.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
    
    const botonModo = document.getElementById('cambiar-modo');
    const iconoModo = botonModo.querySelector('i');
    const body = document.body;
    
    const modoGuardado = localStorage.getItem('modo');
    const prefiereOscuro = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (modoGuardado === 'oscuro' || (!modoGuardado && prefiereOscuro)) {
        activarModoOscuro();
    }
    
    botonModo.addEventListener('click', function() {
        if (body.classList.contains('modo-oscuro')) {
            desactivarModoOscuro();
        } else {
            activarModoOscuro();
        }
    });
    
    function activarModoOscuro() {
        body.classList.add('modo-oscuro');
        iconoModo.classList.replace('fa-moon', 'fa-sun');
        localStorage.setItem('modo', 'oscuro');
    }
    
    function desactivarModoOscuro() {
        body.classList.remove('modo-oscuro');
        iconoModo.classList.replace('fa-sun', 'fa-moon');
        localStorage.setItem('modo', 'claro');
    }
    
    const formulario = document.getElementById('formulario-login');
    const botonLogin = document.getElementById('boton-login');
    
    formulario.addEventListener('submit', function(e) {
        botonLogin.innerHTML = '<i class="fas fa-spinner fa-pulse"></i><span>Verificando...</span>';
        botonLogin.disabled = true;
    });
    </script>
</body>
</html>
