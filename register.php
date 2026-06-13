<?php
require __DIR__ . '/includes/csrf.php'; // starts session
require __DIR__ . '/config/db.php';

// Already logged in? Send to tasks.
if (!empty($_SESSION['user_id'])) {
    header('Location: tasks.php');
    exit;
}

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pw      = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    $old['name']  = $name;
    $old['email'] = $email;

    // ---- Server-side validation ----
    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Name must be 100 characters or fewer.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (mb_strlen($email) > 190) {
        $errors[] = 'Email is too long.';
    }

    if (mb_strlen($pw) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($pw !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // ---- Enforce unique email ----
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    // ---- Create the account ----
    if (!$errors) {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hash]);

        // Log the new user straight in.
        $_SESSION['user_id']   = (int) $pdo->lastInsertId();
        $_SESSION['user_name'] = $name;
        session_regenerate_id(true);

        header('Location: tasks.php');
        exit;
    }
}

$page_title = 'Register';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="card">
        <h2 class="mt-0">Create your account</h2>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="registerForm" method="post" action="register.php" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" maxlength="100"
                       value="<?= htmlspecialchars($old['name'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="190"
                       value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
            </div>
            <button type="submit" class="btn" style="width:100%;">Register</button>
        </form>
        <p class="auth-footer">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
