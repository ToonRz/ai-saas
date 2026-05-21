<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($userId > 0) {
        if ($action === 'ban') {
            $stmt = getDb()->prepare('UPDATE users SET is_banned = 1 WHERE id = ? AND role != ?');
            $stmt->execute([$userId, 'admin']);
            $message = 'User banned.';
        } elseif ($action === 'unban') {
            $stmt = getDb()->prepare('UPDATE users SET is_banned = 0 WHERE id = ?');
            $stmt->execute([$userId]);
            $message = 'User unbanned.';
        }
    }
}

$usersStmt = getDb()->query(
    'SELECT u.id, u.name, u.email, u.role, u.is_banned, u.created_at,
            s.name AS plan_name,
            (SELECT COUNT(*) FROM usage_logs ul WHERE ul.user_id = u.id AND ul.action = \'ai_request\') AS total_usage
     FROM users u
     JOIN subscriptions s ON s.id = u.subscription_id
     ORDER BY u.created_at DESC'
);
$users = $usersStmt->fetchAll();

$logsStmt = getDb()->query(
    'SELECT ul.id, ul.action, ul.created_at, u.name, u.email
     FROM usage_logs ul
     JOIN users u ON u.id = ul.user_id
     ORDER BY ul.created_at DESC
     LIMIT 50'
);
$logs = $logsStmt->fetchAll();

$pageTitle = 'Admin';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-4">Admin panel</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-users">Users</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-logs">Usage logs</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-users">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Role</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int) $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['plan_name']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span></td>
                                <td><?= (int) $u['total_usage'] ?></td>
                                <td>
                                    <?php if ((int) $u['is_banned']): ?>
                                        <span class="badge bg-danger">Banned</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['role'] !== 'admin'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                            <?php if ((int) $u['is_banned']): ?>
                                                <input type="hidden" name="action" value="unban">
                                                <button type="submit" class="btn btn-sm btn-outline-success">Unban</button>
                                            <?php else: ?>
                                                <input type="hidden" name="action" value="ban">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Ban this user?')">Ban</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-logs">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= (int) $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['name']) ?> (<?= htmlspecialchars($log['email']) ?>)</td>
                                <td><code><?= htmlspecialchars($log['action']) ?></code></td>
                                <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($log['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
