<?php
/**
 * AI request endpoint — calls OpenAI API via cURL.
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user = currentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

if (!isSubscriptionActive($user)) {
    http_response_code(402);
    echo json_encode(['error' => 'Your subscription has lapsed. Please start recurring billing to use the AI Assistant.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$prompt = trim($input['prompt'] ?? $_POST['prompt'] ?? '');

if ($prompt === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

if (strlen($prompt) > 4000) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt must be 4000 characters or less']);
    exit;
}

if (!canMakeAiRequest($user)) {
    http_response_code(429);
    echo json_encode(['error' => 'Daily Allowance exhausted. Please wait until tomorrow or upgrade your Subscription Plan for more AI Requests.']);
    exit;
}

$apiKey = env('OPENAI_API_KEY', '');
$model = env('OPENAI_MODEL', 'gpt-3.5-turbo');

if ($apiKey === '' || str_starts_with($apiKey, 'sk-your-')) {
    // Demo mode when no real API key is configured
    $responseText = "[Demo mode] Live inference is not configured because the platform's API Key is missing from the environment.\n\n"
        . "Your Prompt was:\n" . $prompt . "\n\n"
        . "Please add the OPENAI_API_KEY in the .env file to receive live AI Responses.";
}
} else {
    $responseText = callOpenAI($apiKey, $model, $prompt);
    if ($responseText === null) {
        http_response_code(502);
        echo json_encode(['error' => 'AI service unavailable. Try again later.']);
        exit;
    }
}

logUsage((int) $user['id']);
saveAiHistory((int) $user['id'], $prompt, $responseText);

$usageToday = getTodayUsageCount((int) $user['id']);

echo json_encode([
    'success'  => true,
    'response' => $responseText,
    'usage'    => [
        'today'     => $usageToday,
        'limit'     => (int) $user['daily_limit'],
        'remaining' => max(0, (int) $user['daily_limit'] - $usageToday),
    ],
]);

/**
 * Call OpenAI Chat Completions API using cURL.
 */
function callOpenAI(string $apiKey, string $model, string $prompt): ?string
{
    $payload = json_encode([
        'model'    => $model,
        'messages' => [
            [
                'role'    => 'system',
                'content' => 'You are a helpful AI assistant for a SaaS text tool. Be clear and concise.',
            ],
            [
                'role'    => 'user',
                'content' => $prompt,
            ],
        ],
        'max_tokens' => 1024,
    ]);

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 60,
    ]);

    $result = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($result === false || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $data = json_decode($result, true);
    return $data['choices'][0]['message']['content'] ?? null;
}
