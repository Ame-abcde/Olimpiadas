<?php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Admin/admin_login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Por favor ingrese email y contraseña';
    header('Location: ../Admin/admin_login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($password, $admin['contraseña'])) {
        $_SESSION['login_error'] = 'Credenciales incorrectas';
        header('Location: ../Admin/admin_login.php');
        exit;
    }
    
    $_SESSION['id_admin'] = $admin['id_admin'];
    $_SESSION['nombre_admin'] = $admin['nombre'];
    $_SESSION['email_admin'] = $admin['email'];
    
    header('Location: ../Admin/panel_admin.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['login_error'] = 'Error al iniciar sesión. Por favor intente nuevamente.';
    header('Location: ../Admin/admin_login.php');
    exit;
}