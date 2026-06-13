<?php
/**
 * CSRF protection helpers.
 * Generates a per-session token, renders it as a hidden form field,
 * and verifies submitted tokens.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback if the mbstring extension is not enabled (XAMPP ships it by default,
// but some minimal/host PHP builds do not). Keeps length checks working.
if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = null): int
    {
        return strlen((string) $string);
    }
}

/**
 * Return the current CSRF token, creating one if needed.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden input containing the CSRF token. Use inside every <form>.
 */
function csrf_field(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate a submitted token against the session token.
 * Uses hash_equals() to avoid timing attacks.
 */
function csrf_verify(?string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verify the CSRF token from a POST request, or stop execution with 403.
 */
function csrf_require(): void
{
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Invalid or missing CSRF token. Please go back and try again.');
    }
}
