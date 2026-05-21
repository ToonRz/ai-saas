<?php
require_once __DIR__ . '/includes/auth.php';
$user = requireLogin();

$message = '';
$error = '';

$plansStmt = getDb()->query('SELECT id, name, daily_limit, price FROM subscriptions ORDER BY price ASC');
$plans = $plansStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planId = (int) ($_POST['plan_id'] ?? 0);

    $planStmt = getDb()->prepare('SELECT id, name FROM subscriptions WHERE id = ?');
    $planStmt->execute([$planId]);
    $plan = $planStmt->fetch();

    if (!$plan) {
        $error = 'Invalid plan selected.';
    } elseif ($planId === (int) $user['subscription_id']) {
        $error = 'You are already on this plan.';
    } else {
        // Mock payment: instantly upgrade subscription
        $update = getDb()->prepare('UPDATE users SET subscription_id = ? WHERE id = ?');
        $update->execute([$planId, $user['id']]);

        logUsage((int) $user['id'], 'plan_upgrade');

        $message = 'Successfully upgraded to ' . htmlspecialchars($plan['name']) . ' (mock payment).';
        header('Location: dashboard.php?upgraded=1');
        exit;
    }
}

$pageTitle = 'Upgrade plan';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-4">Subscription plans</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<p class="text-muted">Mock payment — no real card charged. Select a plan to upgrade instantly.</p>

<div class="row g-4">
    <?php foreach ($plans as $plan): ?>
        <?php
        $isCurrent = (int) $plan['id'] === (int) $user['subscription_id'];
        $cardClass = $isCurrent ? 'border-primary' : '';
        ?>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 <?= $cardClass ?>">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($plan['name']) ?></h5>
                    <p class="display-6">
                        $<?= number_format((float) $plan['price'], 2) ?>
                        <span class="fs-6 text-muted">/mo</span>
                    </p>
                    <ul class="list-unstyled flex-grow-1">
                        <li><?= (int) $plan['daily_limit'] ?> AI requests per day</li>
                        <li>History saved</li>
                        <li>Email support</li>
                    </ul>
                    <?php if ($isCurrent): ?>
                        <span class="btn btn-outline-primary disabled">Current plan</span>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                            <button type="submit" class="btn btn-primary w-100">
                                Upgrade to <?= htmlspecialchars($plan['name']) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
