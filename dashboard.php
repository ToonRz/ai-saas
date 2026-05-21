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
$cancelled = isset($_GET['cancelled']);
$failed = isset($_GET['failed']);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($upgraded): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4">✨ Recurring Billing started successfully! Your Active Subscription has cleared the Paywall.</div>
<?php endif; ?>
<?php if ($cancelled): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4">ℹ️ Recurring Billing cancelled. You have entered Subscription Lapse.</div>
<?php endif; ?>
<?php if ($failed): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4">⚠️ Simulated Billing Failure triggered. Your subscription has entered Subscription Lapse.</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col">
        <h1 class="h3 fw-bold">Welcome, <?= htmlspecialchars($user['name']) ?></h1>
        <p class="text-muted mb-0">Manage your subscription and run Prompts with the AI Assistant</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Today's Usage Stats Card -->
    <div class="col-md-4">
        <div class="card card-premium h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Today's Usage</h6>
                <p class="display-6 fw-bold mb-0">
                    <?= $usageToday ?> <span class="fs-6 text-muted">/ <?= $limit ?></span>
                </p>
                <p class="small text-muted mb-0"><?= $remaining ?> AI Requests remaining today</p>
            </div>
        </div>
    </div>
    
    <!-- Subscription Card -->
    <div class="col-md-4">
        <div class="card card-premium h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold">Subscription Plan</h6>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fs-4 fw-bold text-dark"><?= htmlspecialchars($user['plan_name']) ?></span>
                        <?php if (isSubscriptionActive($user)): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active Subscription</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Subscription Lapsed</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <p class="small text-muted mb-0 mt-2">
                        ฿<?= number_format((float) $user['price'], 0) ?> / Billing Period —
                        <a href="upgrade.php" class="text-primary fw-bold text-decoration-none">Manage Subscription</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- All-Time Requests Card -->
    <div class="col-md-4">
        <div class="card card-premium h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">All-time AI Requests</h6>
                <p class="display-6 fw-bold mb-0"><?= $totalRequests ?></p>
                <p class="small text-muted mb-0">Saved in your history</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: AI Assistant Card or Paywall -->
    <div class="col-lg-7">
        <div class="card card-premium h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0">AI Assistant</h5>
            </div>
            
            <div class="card-body p-4">
                <?php if (!isSubscriptionActive($user)): ?>
                    <!-- PAYWALL INTERFACE -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <span class="display-1">🔒</span>
                        </div>
                        <h4 class="fw-bold mb-2">Paywall: Managed AI Access Blocked</h4>
                        <p class="text-muted px-md-5 mb-4">
                            Your subscription billing is currently inactive or lapsed. To begin or resume using the AI Assistant, please start recurring billing for the Launch Plan.
                        </p>
                        <form action="upgrade.php" method="post" class="d-inline">
                            <input type="hidden" name="plan_id" value="4">
                            <input type="hidden" name="action" value="activate">
                            <button type="submit" class="btn btn-primary-custom px-4 py-2">
                                Start Recurring Billing (฿249 / Month)
                            </button>
                        </form>
                        <div class="mt-3">
                            <a href="upgrade.php" class="text-decoration-none small text-muted">View checkout options</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- ACTIVE ASSISTANT INTERFACE -->
                    <form id="ai-form">
                        <div class="mb-3">
                            <label for="prompt" class="form-label fw-bold">Your Prompt</label>
                            <textarea class="form-control" id="prompt" name="prompt" rows="6"
                                      placeholder="Submit a Prompt: summarize a text, draft an email, brainstorm..."
                                      <?= $remaining <= 0 ? 'disabled' : '' ?> required></textarea>
                        </div>
                        
                        <?php if ($remaining <= 0): ?>
                            <div class="alert alert-danger shadow-sm border-0 mb-3">
                                <strong>Daily Allowance exhausted.</strong> You have used all <?= $limit ?> AI Requests allowed for the current calendar day under your Subscription Plan. The AI Assistant will refuse new Prompts until tomorrow.
                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary-custom w-100" id="ai-submit"
                                <?= $remaining <= 0 ? 'disabled' : '' ?>>
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                            <span class="submit-text">Send Prompt to AI Assistant</span>
                        </button>
                    </form>
                    
                    <div id="ai-error" class="alert alert-danger mt-3 d-none"></div>
                    
                    <div id="ai-response-wrap" class="mt-4 d-none">
                        <h6 class="text-muted fw-bold">AI Response</h6>
                        <div id="ai-response" class="ai-response p-3 bg-light rounded border border-light-subtle"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column: History -->
    <div class="col-lg-5">
        <div class="card card-premium h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0">AI Request History</h5>
            </div>
            <div class="card-body p-4">
                <?php if (empty($history)): ?>
                    <p class="text-muted py-3 mb-0">No AI Requests yet. Submit a Prompt to get started!</p>
                <?php else: ?>
                    <div class="list-group list-group-flush history-list">
                        <?php foreach ($history as $item): ?>
                            <div class="list-group-item bg-transparent px-0 py-3">
                                <div class="mb-1 small text-truncate-2">
                                    <strong>Prompt:</strong> <?= htmlspecialchars($item['prompt']) ?>
                                </div>
                                <div class="mb-2 small text-muted text-truncate-2">
                                    <strong>AI Response:</strong> <?= htmlspecialchars($item['response']) ?>
                                </div>
                                <div class="small text-muted d-flex justify-content-between">
                                    <span><?= htmlspecialchars(date('M j, Y g:i A', strtotime($item['created_at']))) ?></span>
                                    <span class="badge bg-light text-dark">1 AI Request</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
