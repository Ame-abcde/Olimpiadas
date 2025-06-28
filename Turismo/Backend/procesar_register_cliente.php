<?php
session_start();
require_once '../Backend/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['registro_error'] = 'Algo salió mal';
    header('Location: register_cliente.php');
    exit;
}

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$email = trim($_POST['email']);
$password = $_POST['password'];

if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
    $_SESSION['registro_error'] = 'Faltan datos importantes';
    header('Location: register_cliente.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['registro_error'] = 'El correo no parece válido';
    header('Location: register_cliente.php');
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['registro_error'] = 'La contraseña es muy corta';
    header('Location: register_cliente.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_cliente FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['registro_error'] = 'Este correo ya existe';
        header('Location: register_cliente.php');
        exit;
    }
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO clientes (nombre, apellido, email, contraseña) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $apellido, $email, $passwordHash]);
    
    $_SESSION['registro_success'] = '¡Listo! Ahora puedes iniciar sesión';
    header('Location: login_cliente.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['registro_error'] = 'Ocurrió un problema al registrarte';
    header('Location: register_cliente.php');
    exit;
}