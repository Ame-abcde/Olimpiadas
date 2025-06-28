<?php
session_start();
require_once __DIR__ . '/../Backend/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['login_error'] = 'Acceso no permitido';
    header('Location: ../publico/login_cliente.php');
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Necesitas email y contraseña';
    header('Location: ../publico/login_cliente.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_cliente, nombre, email, contraseña FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();
    
    if (!$cliente || !password_verify($password, $cliente['contraseña'])) {
        $_SESSION['login_error'] = 'Datos incorrectos';
        header('Location: ../publico/login_cliente.php');
        exit;
    }
    
    $_SESSION['id_cliente'] = $cliente['id_cliente'];
    $_SESSION['nombre_cliente'] = $cliente['nombre'];
    $_SESSION['email_cliente'] = $cliente['email'];
    
    header('Location: ../publico/home.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['login_error'] = 'Error en el sistema';
    header('Location: ../publico/login_cliente.php');
    exit;
}