<?php
/**
 * Callback OAuth de Shopify — intercambia el código por un access token permanente
 */

$api_key = getenv('SHOPIFY_API_KEY') ?: file_get_contents(__DIR__ . '/cache/shopify_api_key.txt');
$api_secret = getenv('SHOPIFY_API_SECRET') ?: file_get_contents(__DIR__ . '/cache/shopify_api_secret.txt');
$shop = 'x23gng-2u.myshopify.com';

$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if (!$code) {
    echo '<h2>Error: no se recibió código de autorización</h2>';
    exit;
}

// Exchange code for permanent access token
$url = "https://{$shop}/admin/oauth/access_token";
$params = http_build_query([
    'client_id' => trim($api_key),
    'client_secret' => trim($api_secret),
    'code' => $code,
]);

$ctx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $params,
        'timeout' => 15,
    ]
]);

$response = file_get_contents($url, false, $ctx);
$data = json_decode($response, true);

if (isset($data['access_token'])) {
    $token = htmlspecialchars($data['access_token']);
    echo "<h2>Shopify conectado</h2>";
    echo "<p><strong>Access Token:</strong></p>";
    echo "<pre style='background:#1a1d27;color:#10b981;padding:1rem;border-radius:8px;font-size:1.2rem;'>{$token}</pre>";
    echo "<p>Copia este token y pásalo a Matías.</p>";

    // Also save it to a file for quick access
    file_put_contents(__DIR__ . '/cache/shopify_token.txt', $data['access_token']);
} else {
    echo "<h2>Error</h2>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
