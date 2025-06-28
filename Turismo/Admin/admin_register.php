<?php
session_start();
require '../Backend/config/db.php';

if (!empty($_SESSION['id_admin'])) {
    header('Location: panel_admin.php');
    exit;
}

$error_msg = '';
if (!empty($_SESSION['registro_error'])) {
    $error_msg = $_SESSION['registro_error'];
    unset($_SESSION['registro_error']);
}

$success_msg = '';
if (!empty($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Administrador | Turismo</title>
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
    
    .contenedor-registro {
        width: 100%;
        max-width: 480px;
        background: var(--blanco);
        border-radius: var(--radio-borde);
        box-shadow: var(--sombra);
        padding: 2.5rem;
        transition: var(--transicion);
    }
    
    .cabecera-registro {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .icono-registro {
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
    
    .titulo-registro {
        font-size: 1.6rem;
        font-weight: 600;
        color: var(--azul-principal);
        margin-bottom: 0.5rem;
    }
    
    .subtitulo-registro {
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
    
    .fortaleza-contrasena {
        height: 4px;
        background-color: var(--gris-medio);
        border-radius: 2px;
        margin-top: 8px;
        overflow: hidden;
    }
    
    .nivel-fortaleza {
        height: 100%;
        width: 0;
        transition: width 0.3s ease;
    }
    
    .requisitos-contrasena {
        margin-top: 8px;
        font-size: 0.8rem;
        color: var(--gris-oscuro);
    }
    
    .requisito {
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .requisito.cumplido {
        color: var(--verde-exito);
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
    
    .pie-registro {
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
        .contenedor-registro {
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
    
    <div class="contenedor-registro">
        <div class="cabecera-registro">
            <div class="icono-registro">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1 class="titulo-registro">Registro de Administrador</h1>
            <p class="subtitulo-registro">Crea una cuenta para gestionar el sistema</p>
            
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
        
        <form action="../Backend/procesar_register_admin.php" method="POST" id="formulario-registro">
            <div class="grupo-formulario">
                <label for="nombre" class="etiqueta">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" class="campo" 
                       placeholder="Ej: María González" required minlength="3" maxlength="60"
                       pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" title="Solo letras y espacios">
            </div>
            
            <div class="grupo-formulario">
                <label for="email" class="etiqueta">Correo electrónico</label>
                <input type="email" id="email" name="email" class="campo" 
                       placeholder="admin@turismo.com" required autocomplete="email">
            </div>
            
            <div class="grupo-formulario">
                <label for="password" class="etiqueta">Contraseña</label>
                <input type="password" id="password" name="password" class="campo" 
                       placeholder="Mínimo 8 caracteres" required minlength="8" autocomplete="new-password">
                <i class="fas fa-eye mostrar-pass" id="mostrar-pass"></i>
                <div class="fortaleza-contrasena">
                    <div class="nivel-fortaleza" id="nivel-fortaleza"></div>
                </div>
                <div class="requisitos-contrasena">
                    <div class="requisito" id="req-longitud">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span>Mínimo 8 caracteres</span>
                    </div>
                    <div class="requisito" id="req-mayuscula">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span>Al menos una mayúscula</span>
                    </div>
                    <div class="requisito" id="req-numero">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span>Al menos un número</span>
                    </div>
                    <div class="requisito" id="req-especial">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <span>Al menos un carácter especial</span>
                    </div>
                </div>
            </div>
            
            <div class="grupo-formulario">
                <label for="confirmar-password" class="etiqueta">Confirmar contraseña</label>
                <input type="password" id="confirmar-password" name="confirmar-password" class="campo" 
                       placeholder="Repite tu contraseña" required minlength="8">
                <i class="fas fa-eye mostrar-pass" id="mostrar-confirmar-pass"></i>
            </div>
            
            <button type="submit" class="boton" id="boton-registro">
                <i class="fas fa-user-plus"></i>
                <span>Registrarse</span>
            </button>
        </form>
        
        <div class="pie-registro">
            ¿Ya tienes una cuenta? <a href="admin_login.php" class="enlace">Inicia sesión aquí</a>
        </div>
    </div>

    <script>
    const botonMostrarPass = document.getElementById('mostrar-pass');
    const botonMostrarConfirmarPass = document.getElementById('mostrar-confirmar-pass');
    const campoPassword = document.getElementById('password');
    const campoConfirmarPassword = document.getElementById('confirmar-password');
    
    botonMostrarPass.addEventListener('click', function() {
        if (campoPassword.type === 'password') {
            campoPassword.type = 'text';
            this.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            campoPassword.type = 'password';
            this.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
    
    botonMostrarConfirmarPass.addEventListener('click', function() {
        if (campoConfirmarPassword.type === 'password') {
            campoConfirmarPassword.type = 'text';
            this.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            campoConfirmarPassword.type = 'password';
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
    
    campoPassword.addEventListener('input', function() {
        const password = this.value;
        const nivelFortaleza = document.getElementById('nivel-fortaleza');
        const requisitos = {
            longitud: document.getElementById('req-longitud'),
            mayuscula: document.getElementById('req-mayuscula'),
            numero: document.getElementById('req-numero'),
            especial: document.getElementById('req-especial')
        };
        
        const cumpleLongitud = password.length >= 8;
        const cumpleMayuscula = /[A-Z]/.test(password);
        const cumpleNumero = /[0-9]/.test(password);
        const cumpleEspecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        actualizarRequisito(requisitos.longitud, cumpleLongitud);
        actualizarRequisito(requisitos.mayuscula, cumpleMayuscula);
        actualizarRequisito(requisitos.numero, cumpleNumero);
        actualizarRequisito(requisitos.especial, cumpleEspecial);
        
        const totalRequisitos = [cumpleLongitud, cumpleMayuscula, cumpleNumero, cumpleEspecial]
            .filter(cumple => cumple).length;
        const porcentajeFortaleza = (totalRequisitos / 4) * 100;
        
        nivelFortaleza.style.width = `${porcentajeFortaleza}%`;
        
        if (porcentajeFortaleza < 50) {
            nivelFortaleza.style.backgroundColor = '#ef4444';
        } else if (porcentajeFortaleza < 75) {
            nivelFortaleza.style.backgroundColor = '#f59e0b';
        } else {
            nivelFortaleza.style.backgroundColor = '#10b981';
        }
    });
    
    function actualizarRequisito(elemento, cumple) {
        if (cumple) {
            elemento.classList.add('cumplido');
        } else {
            elemento.classList.remove('cumplido');
        }
    }
    
    campoConfirmarPassword.addEventListener('input', function() {
        if (this.value !== campoPassword.value) {
            this.setCustomValidity('Las contraseñas no coinciden');
        } else {
            this.setCustomValidity('');
        }
    });
    
    const formulario = document.getElementById('formulario-registro');
    const botonRegistro = document.getElementById('boton-registro');
    
    formulario.addEventListener('submit', function(e) {
        if (campoPassword.value !== campoConfirmarPassword.value) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return;
        }
        
        botonRegistro.innerHTML = '<i class="fas fa-spinner fa-pulse"></i><span>Registrando...</span>';
        botonRegistro.disabled = true;
    });
    </script>
</body>
</html>