<?php
require __DIR__ . '/includes/csrf.php';      // starts session
require __DIR__ . '/includes/auth_check.php'; // redirects if not logged in
require __DIR__ . '/config/db.php';

$uid = current_user_id();

/* ---------------------------------------------------------------------------
 * Helpers
 * ------------------------------------------------------------------------- */
function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/** Validate & normalise a task payload from $_POST. Returns [data, errors]. */
function read_task_input(array $src): array
{
    $errors = [];
    $title       = trim($src['title'] ?? '');
    $description = trim($src['description'] ?? '');
    $priority    = $src['priority'] ?? 'medium';
    $due_date    = trim($src['due_date'] ?? '');

    if ($title === '') {
        $errors[] = 'Task title is required.';
    } elseif (mb_strlen($title) > 200) {
        $errors[] = 'Task title must be 200 characters or fewer.';
    }

    if (!in_array($priority, ['low', 'medium', 'high'], true)) {
        $priority = 'medium';
    }

    // due_date is optional; if present it must be a real YYYY-MM-DD date.
    $due_value = null;
    if ($due_date !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $due_date);
        if ($d && $d->format('Y-m-d') === $due_date) {
            $due_value = $due_date;
        } else {
            $errors[] = 'Due date is not a valid date.';
        }
    }

    return [
        [
            'title'       => $title,
            'description' => $description !== '' ? $description : null,
            'priority'    => $priority,
            'due_date'    => $due_value,
        ],
        $errors,
    ];
}

/* ---------------------------------------------------------------------------
 * Handle POST actions (Create / Update / Delete / Toggle)
 * Uses Post/Redirect/Get to avoid resubmission.
 * ------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        [$data, $errors] = read_task_input($_POST);
        if ($errors) {
            flash('error', implode(' ', $errors));
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO tasks (user_id, title, description, priority, due_date)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$uid, $data['title'], $data['description'], $data['priority'], $data['due_date']]);
            flash('success', 'Task added.');
        }
        header('Location: tasks.php');
        exit;
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        [$data, $errors] = read_task_input($_POST);
        if ($errors) {
            flash('error', implode(' ', $errors));
            header('Location: tasks.php?edit=' . $id);
            exit;
        }
        // Scope the update to this user's own task.
        $stmt = $pdo->prepare(
            'UPDATE tasks
                SET title = ?, description = ?, priority = ?, due_date = ?
              WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$data['title'], $data['description'], $data['priority'], $data['due_date'], $id, $uid]);
        flash('success', 'Task updated.');
        header('Location: tasks.php');
        exit;
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        // Flip status only for a row owned by this user.
        $stmt = $pdo->prepare(
            "UPDATE tasks
                SET status = IF(status = 'done', 'pending', 'done')
              WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$id, $uid]);
        flash('success', 'Task status updated.');
        header('Location: tasks.php');
        exit;
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        flash('success', 'Task deleted.');
        header('Location: tasks.php');
        exit;
    }

    header('Location: tasks.php');
    exit;
}

/* ---------------------------------------------------------------------------
 * Read filters / search / sort from the query string
 * ------------------------------------------------------------------------- */
$search = trim($_GET['q'] ?? '');
$status_filter = $_GET['status'] ?? 'all';
if (!in_array($status_filter, ['all', 'pending', 'done'], true)) {
    $status_filter = 'all';
}
$sort = $_GET['sort'] ?? 'created_desc';
$sort_map = [
    'created_desc' => 'created_at DESC',
    'due_asc'      => 'due_date IS NULL, due_date ASC',
    'due_desc'     => 'due_date IS NULL, due_date DESC',
    'priority'     => "FIELD(priority,'high','medium','low')",
];
$order_by = $sort_map[$sort] ?? $sort_map['created_desc'];

/* ---- Build the query with prepared parameters (always scoped to user) ---- */
$sql = 'SELECT * FROM tasks WHERE user_id = ?';
$params = [$uid];

if ($search !== '') {
    $sql .= ' AND title LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($status_filter !== 'all') {
    $sql .= ' AND status = ?';
    $params[] = $status_filter;
}
$sql .= ' ORDER BY ' . $order_by; // $order_by is from a fixed whitelist, never user text

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

/* ---- If editing, load that single task (scoped to user) ---- */
$edit_task = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([(int) $_GET['edit'], $uid]);
    $edit_task = $stmt->fetch() ?: null;
}

/* ---- Pull and clear the flash message ---- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'My Tasks';
require __DIR__ . '/includes/header.php';

// Small escaping shortcut for this page.
function e(?string $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
        <?= e($flash['msg']) ?>
    </div>
<?php endif; ?>

<!-- ============ Add / Edit task form ============ -->
<div class="card">
    <h2 class="mt-0"><?= $edit_task ? 'Edit task' : 'Add a new task' ?></h2>
    <form method="post" action="tasks.php" data-task-form novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $edit_task ? 'update' : 'create' ?>">
        <?php if ($edit_task): ?>
            <input type="hidden" name="id" value="<?= (int) $edit_task['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="title">Title <span class="muted">(required)</span></label>
            <input type="text" id="title" name="title" maxlength="200" required
                   value="<?= e($edit_task['title'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= e($edit_task['description'] ?? '') ?></textarea>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <?php
                    $cur_pri = $edit_task['priority'] ?? 'medium';
                    foreach (['low', 'medium', 'high'] as $p):
                    ?>
                        <option value="<?= $p ?>" <?= $cur_pri === $p ? 'selected' : '' ?>>
                            <?= ucfirst($p) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="due_date">Due date</label>
                <input type="date" id="due_date" name="due_date"
                       value="<?= e($edit_task['due_date'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn"><?= $edit_task ? 'Save changes' : 'Add task' ?></button>
        <?php if ($edit_task): ?>
            <a href="tasks.php" class="btn btn-outline">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- ============ Search / filter / sort ============ -->
<div class="card">
    <div class="section-head">
        <h2 class="mt-0">My tasks (<?= count($tasks) ?>)</h2>
    </div>

    <form method="get" action="tasks.php" id="filterForm" class="filter-bar">
        <div class="form-group">
            <label for="q">Search by title</label>
            <input type="search" id="q" name="q" placeholder="Type a keyword..."
                   value="<?= e($search) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="all"     <?= $status_filter === 'all' ? 'selected' : '' ?>>All</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="done"    <?= $status_filter === 'done' ? 'selected' : '' ?>>Done</option>
            </select>
        </div>
        <div class="form-group">
            <label for="sort">Sort by</label>
            <select id="sort" name="sort">
                <option value="created_desc" <?= $sort === 'created_desc' ? 'selected' : '' ?>>Newest first</option>
                <option value="due_asc"      <?= $sort === 'due_asc' ? 'selected' : '' ?>>Due date (soonest)</option>
                <option value="due_desc"     <?= $sort === 'due_desc' ? 'selected' : '' ?>>Due date (latest)</option>
                <option value="priority"     <?= $sort === 'priority' ? 'selected' : '' ?>>Priority (high first)</option>
            </select>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-sm">Apply</button>
            <a href="tasks.php" class="btn btn-sm btn-outline">Reset</a>
        </div>
    </form>

    <?php if (!$tasks): ?>
        <p class="empty-state">No tasks found. <?= $search || $status_filter !== 'all' ? 'Try clearing the filters.' : 'Add your first task above!' ?></p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                        <tr class="<?= $t['status'] === 'done' ? 'done' : '' ?>">
                            <td>
                                <span class="task-title"><strong><?= e($t['title']) ?></strong></span>
                                <?php if (!empty($t['description'])): ?>
                                    <div class="muted" style="font-size:.85rem;"><?= e($t['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= e($t['priority']) ?>"><?= e($t['priority']) ?></span></td>
                            <td><span class="badge badge-<?= e($t['status']) ?>"><?= e($t['status']) ?></span></td>
                            <td><?= $t['due_date'] ? e($t['due_date']) : '<span class="muted">—</span>' ?></td>
                            <td>
                                <div class="row-actions">
                                    <form method="post" action="tasks.php">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <?= $t['status'] === 'done' ? 'Mark pending' : 'Mark done' ?>
                                        </button>
                                    </form>
                                    <a href="tasks.php?edit=<?= (int) $t['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                                    <form method="post" action="tasks.php"
                                          data-confirm="Delete this task? This cannot be undone.">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
