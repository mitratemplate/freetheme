<?php
/**
 * Logout Handler
 * Enterprise HR Automation System v2.0
 */

// Log activity before logout
if (isset($_SESSION['user_id'])) {
    require_once APP_ROOT . '/app/Core/Database.php';
    logActivity('logout', 'auth', 'users', $_SESSION['user_id']);
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
redirect('login');
