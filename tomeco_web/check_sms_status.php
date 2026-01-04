<?php

/**
 * Check SMS Delivery Status
 * 
 * Usage: php check_sms_status.php [message_id]
 * Example: php check_sms_status.php iSms-AGPOLK
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

$messageId = $argv[1] ?? null;
$apiToken = env('IPROG_SMS_API_TOKEN');

if (!$messageId) {
    echo "Usage: php check_sms_status.php [message_id]\n";
    echo "Example: php check_sms_status.php iSms-AGPOLK\n\n";
    
    // Try to get recent message ID from logs
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        if (preg_match('/"message_id":"([^"]+)"/', $logContent, $matches)) {
            $messageId = $matches[1];
            echo "Found recent message ID from logs: $messageId\n";
            echo "Checking status...\n\n";
        }
    }
    
    if (!$messageId) {
        exit(1);
    }
}

if (!$apiToken) {
    echo "ERROR: IPROG_SMS_API_TOKEN not set in .env\n";
    exit(1);
}

$statusUrl = "https://www.iprogsms.com/api/v1/sms_messages/status";
$params = [
    'api_token' => $apiToken,
    'message_id' => $messageId,
];

echo "Checking SMS delivery status...\n";
echo "Message ID: $messageId\n";
echo "API URL: $statusUrl\n\n";

try {
    $response = Http::timeout(30)
        ->withoutVerifying()
        ->get($statusUrl, $params);
    
    $responseData = $response->json();
    
    echo "Response Status: " . $response->status() . "\n";
    echo "Response Data:\n";
    print_r($responseData);
    
    if (isset($responseData['status'])) {
        echo "\n✓ SMS Status: " . $responseData['status'] . "\n";
    }
    
    if (isset($responseData['delivery_status'])) {
        echo "✓ Delivery Status: " . $responseData['delivery_status'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

