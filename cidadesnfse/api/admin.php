<?php
require_once '../config.php';

session_start();
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? null;

// Verifica se o usuário está logado
if ($action === 'status') {
    echo json_encode(['loggedIn' => isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true]);
    exit;
}

// Processa o login
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['password']) && $data['password'] === ADMIN_PASSWORD) {
        $_SESSION['isAdmin'] = true;
        echo json_encode(['success' => true, 'loggedIn' => true]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Senha incorreta.']);
    }
    exit;
}

// Processa o logout
if ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_destroy();
    echo json_encode(['success' => true, 'loggedIn' => false]);
    exit;
}

http_response_code(400); // Bad Request
echo json_encode(['error' => 'Ação inválida.']);