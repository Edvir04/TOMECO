<?php

/**
 * SMS Diagnostic Script
 * Run this to test SMS functionality and identify issues
 * 
 * Usage: php test_sms.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\IprogSmsService;
use Illuminate\Support\Facades\Log;

echo "=== SMS Diagnostic Test ===\n\n";

// 1. Check environment variables
echo "1. Checking Environment Variables:\n";
$apiToken = env('IPROG_SMS_API_TOKEN');
$apiUrl = env('IPROG_SMS_API_URL', 'https://www.iprogsms.com/api/v1/sms_messages');

echo "   IPROG_SMS_API_TOKEN: " . ($apiToken ? "✓ Set (length: " . strlen($apiToken) . ")" : "✗ NOT SET") . "\n";
echo "   IPROG_SMS_API_URL: " . ($apiUrl ? "✓ Set ($apiUrl)" : "✗ NOT SET") . "\n\n";

if (!$apiToken) {
    echo "❌ ERROR: IPROG_SMS_API_TOKEN is not set in .env file!\n";
    echo "   Please add: IPROG_SMS_API_TOKEN=your_token_here\n\n";
    exit(1);
}

// 2. Check SMS Service initialization
echo "2. Testing SMS Service:\n";
try {
    $smsService = new IprogSmsService();
    echo "   ✓ SMS Service initialized successfully\n\n";
} catch (\Exception $e) {
    echo "   ✗ Failed to initialize SMS Service: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. Check recent ticket with driver contact
echo "3. Checking Recent Tickets:\n";
use App\Models\Ticket;

$recentTicket = Ticket::whereNotNull('driver_contact')
    ->where('driver_contact', '!=', '')
    ->orderBy('created_at', 'desc')
    ->first();

if ($recentTicket) {
    echo "   ✓ Found recent ticket with contact number:\n";
    echo "     - Citation: {$recentTicket->citation_number}\n";
    echo "     - Driver: {$recentTicket->driver_firstname} {$recentTicket->driver_lastname}\n";
    echo "     - Contact: {$recentTicket->driver_contact}\n";
    echo "     - Created: {$recentTicket->created_at}\n\n";
    
    // 4. Test SMS sending
    echo "4. Testing SMS Sending:\n";
    echo "   Attempting to send test SMS to: {$recentTicket->driver_contact}\n";
    
    try {
        $result = $smsService->sendTicketCreatedNotification($recentTicket);
        if ($result) {
            echo "   ✓ SMS sent successfully!\n\n";
        } else {
            echo "   ✗ SMS sending failed (check logs for details)\n\n";
        }
    } catch (\Exception $e) {
        echo "   ✗ Exception: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "   ⚠ No recent tickets with driver contact numbers found\n";
    echo "   This might be why SMS is not working - tickets need driver_contact field\n\n";
}

// 5. Check Laravel logs
echo "5. Recent SMS Log Entries:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $smsLogs = [];
    
    // Extract last 20 lines containing "IPROG SMS"
    $lines = explode("\n", $logContent);
    $smsLines = array_filter($lines, function($line) {
        return stripos($line, 'IPROG SMS') !== false;
    });
    
    $recentSmsLogs = array_slice($smsLines, -10);
    
    if (!empty($recentSmsLogs)) {
        echo "   Recent SMS log entries:\n";
        foreach ($recentSmsLogs as $logLine) {
            // Extract timestamp and message
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*IPROG SMS: (.+)/', $logLine, $matches)) {
                echo "     [{$matches[1]}] {$matches[2]}\n";
            } else {
                echo "     " . substr($logLine, 0, 100) . "...\n";
            }
        }
    } else {
        echo "   ⚠ No SMS log entries found\n";
    }
} else {
    echo "   ⚠ Log file not found at: $logFile\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "\nNext Steps:\n";
echo "1. Check Laravel logs: tail -f storage/logs/laravel.log\n";
echo "2. Verify API credentials are correct in .env\n";
echo "3. Test with a ticket that has a valid driver_contact number\n";
echo "4. Check if IPROG SMS API is accessible from your server\n";

