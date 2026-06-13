<?php
/**
 * Shared site header + navigation.
 * Pages set $page_title before including this file.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = !empty($_SESSION['user_id']);
$display_name = $is_logged_in ? htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8') : '';

// Highlight the active nav link based on the current script name.
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');
function nav_active(string $file, string $current): string
{
    return $file === $current ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . ' · ' : '' ?>Task Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="brand">✓ Task&nbsp;Tracker</a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">☰</button>
        <nav class="main-nav" id="mainNav">
            <a href="index.php"<?= nav_active('index.php', $current) ?>>Home</a>
            <a href="about.php"<?= nav_active('about.php', $current) ?>>About</a>
            <?php if ($is_logged_in): ?>
                <a href="tasks.php"<?= nav_active('tasks.php', $current) ?>>My Tasks</a>
                <span class="nav-user">Hi, <?= $display_name ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
            <?php else: ?>
                <a href="login.php"<?= nav_active('login.php', $current) ?>>Login</a>
                <a href="register.php" class="btn btn-sm">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
