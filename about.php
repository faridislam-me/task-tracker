<?php
require __DIR__ . '/includes/csrf.php'; // starts session for header nav state

$page_title = 'About';
require __DIR__ . '/includes/header.php';

// ---- Edit your group details here ----
$group_id = '[your group ID]';
$members = [
    ['name' => 'Md. Faridul Islam', 'id' => '2020200010006'],
    // Add the remaining group members here, e.g.:
    // ['name' => '[member 2]', 'id' => '[ID 2]'],
];
?>

<div class="card">
    <h2 class="mt-0">About Task Tracker</h2>
    <p>
        Task Tracker is a full-stack web application built for the
        <strong>CSE 471 - Web Programming</strong> course. It lets a registered
        user securely manage a personal to-do list: create tasks with a title,
        description, priority and due date; mark them done or pending; edit or
        delete them; and search, filter and sort the list.
    </p>

    <h3>Tech stack</h3>
    <ul>
        <li><strong>Frontend:</strong> HTML5, CSS3, vanilla JavaScript (no frameworks)</li>
        <li><strong>Backend:</strong> PHP with PDO</li>
        <li><strong>Database:</strong> MySQL</li>
    </ul>

    <h3>Key features</h3>
    <ul>
        <li>User registration, login and logout with hashed passwords</li>
        <li>Full task CRUD with priority badges, status and due dates</li>
        <li>Search by title, filter by status, and sort by due date</li>
        <li>Strict per-user data scoping</li>
        <li>Security: prepared statements, output escaping, CSRF tokens, session access control</li>
    </ul>
</div>

<div class="card">
    <h2 class="mt-0">Team</h2>
    <p><strong>Group ID:</strong> <?= htmlspecialchars($group_id, ENT_QUOTES, 'UTF-8') ?></p>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>#</th><th>Name</th><th>Student ID</th></tr>
            </thead>
            <tbody>
                <?php foreach ($members as $i => $m): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($m['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
