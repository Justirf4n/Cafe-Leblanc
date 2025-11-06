<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = [
    'loggedIn' => isset($_SESSION['user_id']),
    'username' => isset($_SESSION['username']) ? $_SESSION['username'] : null,
    'role' => isset($_SESSION['role']) ? $_SESSION['role'] : null
];

echo json_encode($response);
?>