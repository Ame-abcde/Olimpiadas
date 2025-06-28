<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['id_cliente'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión para modificar el carrito']);
    exit;
}

if (empty($_POST['id_producto']) || !isset($_POST['cantidad'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_producto = (int)$_POST['id_producto'];
$cantidad = (int)$_POST['cantidad'];

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($cantidad > 0) {
    $stmt = $pdo->prepare("SELECT id_producto FROM productos WHERE id_producto = ? AND disponible = 1");
    $stmt->execute([$id_producto]);

    if ($stmt->fetch()) {
        $_SESSION['carrito'][$id_producto] = $cantidad;
    } else {
        unset($_SESSION['carrito'][$id_producto]);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Producto no disponible']);
        exit;
    }
} else {
    unset($_SESSION['carrito'][$id_producto]);
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Carrito actualizado',
    'total_items' => array_sum($_SESSION['carrito'])
]);