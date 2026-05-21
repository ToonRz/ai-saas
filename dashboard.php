<?php
require_once __DIR__ . '/includes/auth.php';
$user = requireLogin();

$usageToday = getTodayUsageCount((int) $user['id']);
$limit = (int) $user['daily_limit'];
$remaining = max(0, $limit - $usageToday);

$historyStmt = getDb()->prepare(
    'SELECT id, prompt, response, created_at FROM ai_history
     WHERE user_id = ? ORDER BY created_at DESC LIMIT 10'
);
$historyStmt->execute([$user['id']]);
$history = $historyStmt->fetchAll();

$totalStmt = getDb()->prepare('SELECT COUNT(*) AS cnt FROM ai_history WHERE user_id = ?');
$totalStmt->execute([$user['id']]);
$totalRequests = (int) $totalStmt->fetch()['cnt'];

$upgraded = isset($_GET['upgraded']);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($upgraded): ?>
    <div class="alert alert-success">Plan upgraded successfully!</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col">
        <h1 class="h3">Welcome, <?= htmlspecialchars($user['name']) ?></h1>
        <p class="text-muted mb-0">Your AI text assistant dashboard</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Today's usage</h6>
                <p class="display-6 mb-0"><?= $usageToday ?> <span class="fs-6 text-muted">/ <?= $limit ?></span></p>
                <p class="small text-muted mb-0"><?= $remaining ?> requests remaining today</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Subscription</h6>
                <p class="display-6 mb-0"><?= htmlspecialchars($user['plan_name']) ?></p>
                <p class="small text-muted mb-0">
                    $<?= number_format((float) $user['price'], 2) ?>/mo —
                    <a href="upgrade.php">Upgrade</a>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">All-time AI requests</h6>
                <p class="display-6 mb-0"><?= $totalRequests ?></p>
                <p class="small text-muted mb-0">Saved in your history</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong>AI Assistant</strong>
            </div>
            <div class="card-body">
                <form id="ai-form">
                    <div class="mb-3">
                        <label for="prompt" class="form-label">Your prompt</label>
                        <textarea class="form-control" id="prompt" name="prompt" rows="6"
                                  placeholder="Ask anything: summarize this text, write an email, brainstorm ideas..."
                                  <?= $remaining <= 0 ? 'disabled' : '' ?> required></textarea>
                    </div>
                    <?php if ($remaining <= 0): ?>
                        <div class="alert alert-warning">
                            Daily limit reached. <a href="upgrade.php">Upgrade your plan</a> for more requests.
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary" id="ai-submit"
                            <?= $remaining <= 0 ? 'disabled' : '' ?>>
                        <span class="submit-text">Send to AI</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </form>
                <div id="ai-error" class="alert alert-danger mt-3 d-none"></div>
                <div id="ai-response-wrap" class="mt-4 d-none">
                    <h6 class="text-muted">Response</h6>
                    <div id="ai-response" class="ai-response p-3 bg-light rounded border"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Recent history</strong>
            </div>
            <div class="card-body p-0">
                <?php if (empty($history)): ?>
                    <p class="text-muted p-3 mb-0">No requests yet. Try the AI assistant!</p>
                <?php else: ?>
                    <div class="list-group list-group-flush history-list">
                        <?php foreach ($history as $item): ?>
                            <div class="list-group-item">
                                <p class="mb-1 small text-truncate-2">
                                    <strong>Prompt:</strong> <?= htmlspecialchars(substr($item['prompt'], 0, 120)) ?>
                                </p>
                                <p class="mb-1 small text-muted">
                                    <?= htmlspecialchars(date('M j, Y g:i A', strtotime($item['created_at']))) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
