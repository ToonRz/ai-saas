<?php
/**
 * Session-based authentication helpers.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/db.php';

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    $stmt = getDb()->prepare(
        'SELECT u.id, u.name, u.email, u.role, u.is_banned, u.subscription_id,
                s.name AS plan_name, s.daily_limit, s.price
         FROM users u
         JOIN subscriptions s ON s.id = u.subscription_id
         WHERE u.id = ?'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;

    if ($user && (int) $user['is_banned'] === 1) {
        logout();
        return null;
    }

    return $user;
}

function requireLogin(): array
{
    $user = currentUser();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function requireAdmin(): array
{
    $user = requireLogin();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Access denied.');
    }
    return $user;
}

function loginUser(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

function getTodayUsageCount(int $userId): int
{
    $stmt = getDb()->prepare(
        "SELECT COUNT(*) AS cnt FROM usage_logs
         WHERE user_id = ? AND action = 'ai_request'
         AND DATE(created_at) = CURDATE()"
    );
    $stmt->execute([$userId]);
    return (int) $stmt->fetch()['cnt'];
}

function canMakeAiRequest(array $user): bool
{
    return getTodayUsageCount((int) $user['id']) < (int) $user['daily_limit'];
}

function logUsage(int $userId, string $action = 'ai_request'): void
{
    $stmt = getDb()->prepare('INSERT INTO usage_logs (user_id, action) VALUES (?, ?)');
    $stmt->execute([$userId, $action]);
}

function saveAiHistory(int $userId, string $prompt, string $response): void
{
    $stmt = getDb()->prepare(
        'INSERT INTO ai_history (user_id, prompt, response) VALUES (?, ?, ?)'
    );
    $stmt->execute([$userId, $prompt, $response]);
}
