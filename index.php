<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row align-items-center min-vh-75 py-5">
    <div class="col-lg-6">
        <h1 class="display-5 fw-bold mb-3">AI-powered text assistant</h1>
        <p class="lead text-muted">
            Generate summaries, rewrite content, brainstorm ideas, and more —
            with daily usage limits and subscription plans built in.
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="register.php" class="btn btn-primary btn-lg">Get started free</a>
            <a href="login.php" class="btn btn-outline-secondary btn-lg">Login</a>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="card-title">What you get</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">✓ OpenAI-powered responses</li>
                    <li class="mb-2">✓ Request history saved to your account</li>
                    <li class="mb-2">✓ Free, Pro, and Premium plans</li>
                    <li class="mb-0">✓ Usage tracking and daily limits</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
