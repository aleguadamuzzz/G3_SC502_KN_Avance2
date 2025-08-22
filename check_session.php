<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'logged_in' => true,
        'username' => $_SESSION['username'],
        'is_admin' => $_SESSION['rol'] === 'admin'
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>