<?php
require 'db.php';

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO administradores (nombre, email, contraseña) VALUES (?, ?, ?)");
$stmt->execute([$nombre, $email, $password]);

echo "Administrador registrado correctamente.";
?>
