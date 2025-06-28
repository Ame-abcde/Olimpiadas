<?php
session_start();
require_once __DIR__ . '/config/db.php';

$nombre = trim($_POST['nombre'] ?? '');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirmar-password'] ?? '';

if (!$nombre || !$email || !$password || !$confirm_password) {
    $_SESSION['registro_error'] = "Todos los campos son requeridos";
    header('Location: ../Admin/admin_register.php');
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['registro_error'] = "La contraseña debe tener al menos 8 caracteres";
    header('Location: ../Admin/admin_register.php');
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['registro_error'] = "Las contraseñas no coinciden";
    header('Location: ../Admin/admin_register.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_admin FROM administradores WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $_SESSION['registro_error'] = "Este correo ya está registrado";
        header('Location: ../Admin/admin_register.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO administradores (nombre, email, contraseña) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$nombre, $email, $hash])) {
        $_SESSION['success_msg'] = "Registro exitoso. Puede iniciar sesión";
        header('Location: ../Admin/admin_login.php');
        exit;
    } else {
        throw new Exception("Error al registrar el administrador");
    }
    
} catch (PDOException $e) {
    $_SESSION['registro_error'] = "Error en el servidor. Intente nuevamente";
    header('Location: ../Admin/admin_register.php');
    exit;
}