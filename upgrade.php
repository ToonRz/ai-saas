<?php
require_once __DIR__ . '/includes/auth.php';
$user = requireLogin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'activate') {
        // Set user to Launch Plan and mark active
        $stmt = getDb()->prepare('UPDATE users SET subscription_id = 4, subscription_active = 1 WHERE id = ?');
        $stmt->execute([$user['id']]);
        
        logUsage((int) $user['id'], 'recurring_billing_start');
        header('Location: dashboard.php?upgraded=1');
        exit;
    } elseif ($action === 'cancel') {
        // Cancel subscription: transitions immediately into Subscription Lapse for testing
        $stmt = getDb()->prepare('UPDATE users SET subscription_active = 0 WHERE id = ?');
        $stmt->execute([$user['id']]);
        
        logUsage((int) $user['id'], 'recurring_billing_cancel');
        header('Location: dashboard.php?cancelled=1');
        exit;
    } elseif ($action === 'billing_failure') {
        // Simulate billing failure: transitions immediately into Subscription Lapse
        $stmt = getDb()->prepare('UPDATE users SET subscription_active = 0 WHERE id = ?');
        $stmt->execute([$user['id']]);
        
        logUsage((int) $user['id'], 'billing_failure');
        header('Location: dashboard.php?failed=1');
        exit;
    } else {
        $error = 'Invalid action selected.';
    }
}

// Fetch user subscription again after potential modifications
$stmt = getDb()->prepare(
    'SELECT u.subscription_id, u.subscription_active, s.name AS plan_name, s.daily_limit, s.price
     FROM users u
     JOIN subscriptions s ON s.id = u.subscription_id
     WHERE u.id = ?'
);
$stmt->execute([$user['id']]);
$subInfo = $stmt->fetch();

$pageTitle = 'Manage Subscription';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-premium p-4 shadow-sm">
            <h2 class="fw-bold mb-3 text-center">Manage Subscription</h2>
            <p class="text-muted text-center mb-4">
                Configure your payment standing and simulate billing states for the Launch Plan.
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger mb-3 border-0"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="border rounded p-3 bg-light mb-4">
                <h6 class="fw-bold text-muted text-uppercase mb-2 small">Current Standing</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark">Subscription Plan:</span>
                    <span><?= htmlspecialchars($subInfo['plan_name']) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark">Daily Allowance:</span>
                    <span><?= (int) $subInfo['daily_limit'] ?> AI Requests</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark">Billing Price:</span>
                    <span>฿<?= number_format((float) $subInfo['price'], 0) ?> / Month</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Payment Status:</span>
                    <?php if ((int) $subInfo['subscription_active'] === 1): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active Subscription</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Subscription Lapsed</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ACTION CONTROLS -->
            <div class="d-grid gap-3">
                <?php if ((int) $subInfo['subscription_active'] !== 1): ?>
                    <form method="post" class="w-100">
                        <input type="hidden" name="action" value="activate">
                        <button type="submit" class="btn btn-primary-custom w-100 py-3">
                            Start Recurring Billing (฿249 / Month)
                        </button>
                    </form>
                    <div class="alert alert-info py-2 small mb-0 mt-2">
                        ℹ️ Clearing the Paywall grants <strong>Managed AI Access</strong> for all your Prompts.
                    </div>
                <?php else: ?>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <form method="post">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn btn-outline-warning w-100 py-2.5 small"
                                        onclick="return confirm('Simulate cancellation? This will lapse your active subscription.')">
                                    Simulate Cancellation
                                </button>
                            </form>
                        </div>
                        <div class="col-sm-6">
                            <form method="post">
                                <input type="hidden" name="action" value="billing_failure">
                                <button type="submit" class="btn btn-outline-danger w-100 py-2.5 small"
                                        onclick="return confirm('Simulate billing failure? This will lapse your subscription immediately.')">
                                    Simulate Billing Failure
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="alert alert-success py-2 small mb-0 mt-2 text-center">
                        ✓ Your recurring billing is active. Live inference is enabled.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
