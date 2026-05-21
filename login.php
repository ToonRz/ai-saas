<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = getDb()->prepare('SELECT id, password_hash, is_banned FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, $row['password_hash'])) {
            $error = 'Invalid email or password.';
        } elseif ((int) $row['is_banned'] === 1) {
            $error = 'Your account has been suspended. Contact support.';
        } else {
            loginUser((int) $row['id']);
            header('Location: dashboard.php');
            exit;
        }
    }
}

$pageTitle = 'Login';
$hideNav = false;
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 mb-4">Login</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="text-center text-muted mt-3 mb-0">
                    No account? <a href="register.php">Register</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
