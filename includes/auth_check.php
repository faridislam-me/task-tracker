<?php
/**
 * Session-based access control.
 * Include at the top of any page that requires a logged-in user.
 * Redirects unauthenticated visitors to the login page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/**
 * Convenience helpers available to protected pages.
 */
function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function current_user_name(): string
{
    return (string) ($_SESSION['user_name'] ?? 'User');
}
