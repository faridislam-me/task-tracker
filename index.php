<?php
require __DIR__ . '/includes/csrf.php'; // also starts the session
require __DIR__ . '/config/db.php';

$is_logged_in = !empty($_SESSION['user_id']);

// If logged in, gather a tiny dashboard of task counts (scoped to this user).
$stats = ['total' => 0, 'pending' => 0, 'done' => 0];
if ($is_logged_in) {
    $stmt = $pdo->prepare(
        'SELECT status, COUNT(*) AS c FROM tasks WHERE user_id = ? GROUP BY status'
    );
    $stmt->execute([(int) $_SESSION['user_id']]);
    foreach ($stmt->fetchAll() as $row) {
        $stats[$row['status']] = (int) $row['c'];
        $stats['total'] += (int) $row['c'];
    }
}

$page_title = 'Home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Stay on top of your tasks</h1>
    <p>Task Tracker is a simple, secure full-stack web app to create, organize,
       and track your to-dos by priority, status, and due date.</p>
    <?php if ($is_logged_in): ?>
        <a href="tasks.php" class="btn btn-light">Go to My Tasks</a>
    <?php else: ?>
        <a href="register.php" class="btn btn-light">Get Started</a>
        &nbsp;
        <a href="login.php" class="btn btn-outline" style="color:#fff;border-color:#fff;">Login</a>
    <?php endif; ?>
</section>

<?php if ($is_logged_in): ?>
    <h2>Your dashboard</h2>
    <div class="stats">
        <div class="stat">
            <div class="num"><?= $stats['total'] ?></div>
            <div class="label">Total tasks</div>
        </div>
        <div class="stat">
            <div class="num"><?= $stats['pending'] ?></div>
            <div class="label">Pending</div>
        </div>
        <div class="stat">
            <div class="num"><?= $stats['done'] ?></div>
            <div class="label">Completed</div>
        </div>
    </div>
    <p><a href="tasks.php" class="btn">Manage tasks &rarr;</a></p>
<?php endif; ?>

<div class="card">
    <h2 class="mt-0">About this project</h2>
    <p class="muted">
        Built for <strong>CSE 471 - Web Programming</strong> using HTML, CSS,
        vanilla JavaScript, PHP (PDO), and MySQL. It demonstrates user
        authentication, full CRUD operations, search &amp; filtering, and core
        web-security practices (prepared statements, output escaping, CSRF
        tokens, and session-based access control).
    </p>
    <p><a href="about.php">Read more &amp; meet the team &rarr;</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
