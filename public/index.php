<?php
require __DIR__ . '/../vendor/autoload.php';

session_start();

$controllerName = $_GET['controller'] ?? 'Auth';
$action         = $_GET['action'] ?? 'showLoginForm';

// Lista blanca: solo estos controladores y acciones son accesibles
$allowed = [
    'Auth' => ['showRegisterForm', 'register', 'showLoginForm', 'login', 'logout'],
];

if (!isset($allowed[$controllerName]) || !in_array($action, $allowed[$controllerName], true)) {
    http_response_code(404);
    echo 'Página no encontrada';
    exit;
}

$class = "App\\Controllers\\{$controllerName}Controller";
$controller = new $class();
$controller->$action();