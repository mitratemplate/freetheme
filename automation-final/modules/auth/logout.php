<?php
/**
 * Logout Handler
 */
session_start();

$db = Database::getInstance();

// Create audit log before destroying session
if (isset($_SESSION['user_id'])) {
    $db->insert('audit_log', [
        'user_id' => $_SESSION['user_id'],
        'action' => 'logout',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

// Destroy session
session_destroy();

// Redirect to login
header('Location: login');
exit;
