<?php

/**
 * Test script to verify Sanctum token creation
 * Usage: php test_token_creation.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Sanctum Token Creation...\n\n";

$enforcer = \App\Models\TomecoEnforcer::first();

if (!$enforcer) {
    echo "✗ No enforcer found in database\n";
    exit(1);
}

echo "Enforcer found: {$enforcer->username}\n";
echo "Creating token...\n\n";

$token = $enforcer->createToken('test-token')->plainTextToken;

echo "Token created successfully!\n";
echo "Token length: " . strlen($token) . "\n";
echo "Token preview: " . substr($token, 0, 30) . "...\n";
echo "Token format: " . substr($token, 0, 2) . " (should be like '1|')\n\n";

if (strlen($token) < 80) {
    echo "⚠ WARNING: Token is shorter than expected (should be 80+ characters)\n";
} else {
    echo "✓ Token length looks correct\n";
}

if (!str_contains($token, '|')) {
    echo "⚠ WARNING: Token doesn't contain '|' separator\n";
} else {
    echo "✓ Token format looks correct\n";
}

