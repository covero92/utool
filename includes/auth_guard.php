<?php
// includes/auth_guard.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/portal_auth.php'; // Ensures access to helpers like isLoggedIn()

// Load Public Config
$publicConfig = require __DIR__ . '/public_config.php';
$whitelist = $publicConfig['whitelist'] ?? [];

// Determine current script
$currentScript = basename($_SERVER['SCRIPT_NAME']);
$currentURI = $_SERVER['REQUEST_URI'];

// 1. Check if strictly whitelisted script
if (in_array($currentScript, $whitelist)) {
    // Allowed.
    return;
}

// 2. Check login status
if (!isLoggedIn()) {
    // If it's an API request, return JSON 401
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Please login.']);
        exit;
    }

    // Otherwise redirect to login (index.php)
    // Store original destination maybe?
    header('Location: /utool/index.php?redirect=' . urlencode($currentURI));
    exit;
}

// 3. Status Check (Active vs Pending)
$status = $_SESSION['user_status'] ?? 'active';
// We might need to fetch fresh status from DB here to be super secure, 
// but session is usually enough for performance. 
// PortalAuth::login sets session, but does it set 'user_status'? Let's ensure it does.

if ($status !== 'active') {
     if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Account pending approval.']);
        exit;
    }
    
    // Redirect to a specific "Pending Approval" page or back to index with error
    // For now, index.php with a flag
    header('Location: /utool/index.php?error=account_pending');
    exit;
}
?>
