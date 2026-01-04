<?php

/**
 * Test script to verify Sanctum token authentication
 * Usage: php test_token.php YOUR_TOKEN_HERE
 */

require __DIR__ . '/vendor/autoload.php';

$token = $argv[1] ?? null;

if (!$token) {
    echo "Usage: php test_token.php YOUR_TOKEN\n";
    exit(1);
}

echo "Testing Sanctum Token Authentication...\n\n";
echo "Token length: " . strlen($token) . "\n";
echo "Token preview: " . substr($token, 0, 20) . "...\n\n";

// Test the token by making a request to the profile endpoint
$url = 'http://localhost:8000/api/mobile/profile';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n";

if ($httpCode === 200) {
    echo "\n✓ Token is valid!\n";
} else {
    echo "\n✗ Token is invalid or expired\n";
    echo "Please login again to get a new token.\n";
}

