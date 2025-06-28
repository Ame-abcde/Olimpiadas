<?php

session_start();
session_unset();
session_destroy();

header('Location: ../Publico/login_cliente.php');
exit;
?>