<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['id_cliente']) || !isset($_POST['id_producto']) || !isset($_POST['cantidad'])) {
    exit;
}

$id_producto = (int)$_POST['id_producto'];
$cantidad = (int)$_POST['cantidad'];

if ($cantidad < 1) {
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_producto FROM productos WHERE id_producto = ? AND disponible = 1");
    $stmt->execute([$id_producto]);
    
    if ($stmt->fetch()) {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        $_SESSION['carrito'][$id_producto] = ($_SESSION['carrito'][$id_producto] ?? 0) + $cantidad;
    }
} catch (PDOException $e) {
    error_log('Error en agregar_carrito.php: ' . $e->getMessage());
}