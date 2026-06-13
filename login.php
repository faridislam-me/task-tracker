<?php
require __DIR__ . '/includes/csrf.php'; // starts session
require __DIR__ . '/config/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: tasks.php');
    exit;
}

$errors = [];
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $email = trim($_POST['email'] ?? '');
    $pw    = (string) ($_POST['password'] ?? '');
    $old_email = $email;

    if ($email === '' || $pw === '') {
        $errors[] = 'Please enter both email and password.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Same generic message whether the email or password is wrong.
        if ($user && password_verify($pw, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: tasks.php');
            exit;
        }
        $errors[] = 'Invalid email or password.';
    }
}

$page_title = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="card">
        <h2 class="mt-0">Welcome back</h2>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="post" action="login.php" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($old_email, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn" style="width:100%;">Login</button>
        </form>
        <p class="auth-footer">No account yet? <a href="register.php">Register here</a></p>
        <p class="auth-footer muted">Demo login: <code>demo@example.com</code> / <code>Demo@1234</code></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
