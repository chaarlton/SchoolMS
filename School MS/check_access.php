<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

/**
 * Check if the current user has the allowed role(s)
 * @param array|string $allowedRoles - a role or an array of roles
 */
function requireRole($allowedRoles) {
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        // Redirect based on role
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: student_portal.php");
        }
        exit();
    }
}
?>
