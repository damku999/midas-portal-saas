<?php

/**
 * Test Script for Notification Logging
 *
 * This script tests the notification logging system by sending test messages
 * to the specified WhatsApp number and email address.
 *
 * Usage: php artisan tinker < test-notification-logging.php
 * Or copy-paste the commands into tinker manually
 */

// Test Contact Details
$testPhone = '8000071413';
$testEmail = 'damku999@gmail.com';

echo "=== Notification Logging Test Script ===\n\n";

// Test 1: Find or create a test customer
echo "1. Creating/Finding test customer...\n";
$customer = \App\Models\Customer::firstOrCreate(
    ['mobile_number' => $testPhone],
    [
        'name' => 'Test Customer',
        'email' => $testEmail,
        'status' => 1,
    ]
);
echo "Customer ID: {$customer->id}\n\n";

// Test 2: Send onboarding message (uses NotificationLoggerService)
echo "2. Testing onboarding message...\n";
try {
    $customerService = app(\App\Services\CustomerService::class);
    $result = $customerService->sendOnboardingMessage($customer);
    echo 'Onboarding message result: '.($result ? 'SUCCESS' : 'FAILED')."\n";
} catch (\Exception $e) {
    echo "Onboarding message ERROR: {$e->getMessage()}\n";
}
echo "\n";

// Test 3: Send marketing message
echo "3. Testing marketing WhatsApp message...\n";
try {
    $marketingService = app(\App\Services\MarketingWhatsAppService::class);
    $result = $marketingService->sendTextMessage(
        '🎉 This is a test marketing message from Midas Portal notification logging system!',
        $testPhone,
        $customer->id
    );
    echo 'Marketing message result: '.($result ? 'SUCCESS' : 'FAILED')."\n";
} catch (\Exception $e) {
    echo "Marketing message ERROR: {$e->getMessage()}\n";
}
echo "\n";

// Test 4: Test with invalid phone (should fail and log)
echo "4. Testing failed notification (invalid phone)...\n";
try {
    $marketingService = app(\App\Services\MarketingWhatsAppService::class);
    $result = $marketingService->sendTextMessage(
        'This should fail - invalid phone test',
        '1234567890', // Invalid phone
        $customer->id
    );
    echo 'Invalid phone test result: '.($result ? 'SUCCESS' : 'FAILED (Expected)')."\n";
} catch (\Exception $e) {
    echo "Invalid phone ERROR (Expected): {$e->getMessage()}\n";
}
echo "\n";

// Test 5: Check notification logs
echo "5. Checking notification logs...\n";
$logs = \App\Models\NotificationLog::where('recipient', $testPhone)
    ->orWhere('recipient', $testEmail)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Found {$logs->count()} notification log entries:\n";
foreach ($logs as $log) {
    echo "  - [{$log->id}] {$log->channel} | {$log->status} | {$log->created_at->format('Y-m-d H:i:s')}\n";
    echo '    Type: '.($log->notificationType->name ?? 'N/A')."\n";
    echo '    Template: '.($log->template->name ?? 'No template')."\n";
    if ($log->error_message) {
        echo "    Error: {$log->error_message}\n";
    }
    echo "\n";
}

echo "\n=== Test Complete ===\n";
echo "View all logs at: /admin/notification-logs\n";
echo "View details at: /admin/notification-logs/{id}\n";
