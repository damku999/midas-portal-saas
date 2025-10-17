# SMS and Push Notifications - Quick Reference

## Quick Start

### 1. Enable SMS Notifications

```bash
# .env
SMS_NOTIFICATIONS_ENABLED=true
SMS_PROVIDER=twilio
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

### 2. Enable Push Notifications

```bash
# .env
PUSH_NOTIFICATIONS_ENABLED=true
FCM_SERVER_KEY=your_fcm_server_key
FCM_SENDER_ID=your_sender_id
```

### 3. Run Migrations

```bash
php artisan migrate
php artisan db:seed --class=SmsAndPushSettingsSeeder
```

---

## Common Operations

### Send SMS

```php
use App\Services\SmsService;
use App\Services\Notification\NotificationContext;

$smsService = app(SmsService::class);
$context = NotificationContext::fromCustomerId($customerId);

// With template
$smsService->sendTemplatedSms(
    to: '+919876543210',
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context,
    customerId: $customerId
);

// Plain SMS
$smsService->sendPlainSms(
    to: '+919876543210',
    message: 'Your policy expires soon',
    customerId: $customerId
);
```

### Send Push Notification

```php
use App\Services\PushNotificationService;

$pushService = app(PushNotificationService::class);

// To all customer devices
$pushService->sendToCustomer(
    customer: $customer,
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context
);

// To specific device
$pushService->sendTemplatedPush(
    deviceToken: 'fcm_device_token',
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context,
    customerId: $customer->id
);
```

### Multi-Channel Sending

```php
use App\Services\Notification\ChannelManager;

$channelManager = app(ChannelManager::class);

// Send to all channels
$channelManager->sendToAllChannels(
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context,
    channels: ['push', 'whatsapp', 'sms', 'email'],
    customer: $customer
);

// Send with fallback (Push → WhatsApp → SMS → Email)
$channelManager->sendWithFallback(
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context,
    customer: $customer
);
```

### Register Device for Push

```php
$pushService->registerDevice(
    customerId: $customer->id,
    deviceToken: 'fcm_token_from_mobile_app',
    deviceType: 'android', // or 'ios', 'web'
    deviceInfo: [
        'device_name' => 'Samsung Galaxy S21',
        'os_version' => 'Android 13',
        'app_version' => '2.1.0'
    ]
);
```

---

## Notification Templates

### Create SMS Template

```sql
INSERT INTO notification_templates (
    notification_type_id,
    channel,
    template_content,
    is_active
) VALUES (
    1,
    'sms',
    'Your {{policy_type}} expires in {{days_remaining}} days. Policy: {{policy_no}}',
    1
);
```

### Create Push Templates

```sql
-- Title
INSERT INTO notification_templates (
    notification_type_id,
    channel,
    template_content,
    is_active
) VALUES (
    1,
    'push_title',
    'Policy Renewal Reminder',
    1
);

-- Body
INSERT INTO notification_templates (
    notification_type_id,
    channel,
    template_content,
    is_active
) VALUES (
    1,
    'push',
    'Dear {{customer_name}}, your {{policy_type}} policy expires in {{days_remaining}} days.',
    1
);
```

---

## Customer Preferences

### Set Notification Preferences

```php
$customer->update([
    'notification_preferences' => [
        'channels' => ['whatsapp', 'email', 'sms', 'push'],
        'quiet_hours' => [
            'start' => '22:00',
            'end' => '08:00'
        ],
        'opt_out_types' => ['birthday_wish']
    ]
]);
```

### Check Preferences

```php
// Get enabled channels
$channels = $customer->notification_preferences['channels'] ?? ['whatsapp', 'email'];

// Check if opted out
$optedOut = in_array(
    'birthday_wish',
    $customer->notification_preferences['opt_out_types'] ?? []
);
```

---

## Notification Logs

### Query Logs

```php
use App\Models\NotificationLog;

// Get all SMS logs for customer
$smsLogs = NotificationLog::where('customer_id', $customerId)
    ->where('channel', 'sms')
    ->get();

// Get failed notifications
$failed = NotificationLog::failed()
    ->where('customer_id', $customerId)
    ->get();

// Get recent notifications
$recent = NotificationLog::where('customer_id', $customerId)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### Check Status

```php
$log = NotificationLog::find($id);

echo $log->status; // pending, sent, delivered, failed
echo $log->error_message; // if failed
echo $log->sent_at; // timestamp

if ($log->canRetry()) {
    // Retry failed notification
}
```

---

## Device Management

### Get Customer Devices

```php
use App\Models\CustomerDevice;

// Get all active devices
$devices = CustomerDevice::where('customer_id', $customerId)
    ->where('is_active', true)
    ->get();

// Get by type
$androidDevices = CustomerDevice::where('customer_id', $customerId)
    ->where('device_type', 'android')
    ->active()
    ->get();
```

### Deactivate Device

```php
$device = CustomerDevice::where('device_token', $token)->first();
$device->deactivate();

// Or
CustomerDevice::where('device_token', $token)
    ->update(['is_active' => false]);
```

---

## Configuration

### App Settings (via Admin Panel)

Navigate to `/app-settings` and configure:

**SMS Settings:**
- `sms_notifications_enabled` - Enable/disable SMS
- `sms_provider` - Provider (twilio, nexmo, sns)
- `sms_twilio_account_sid` - Twilio Account SID (encrypted)
- `sms_twilio_auth_token` - Twilio Auth Token (encrypted)
- `sms_twilio_from_number` - Twilio phone number

**Push Settings:**
- `push_notifications_enabled` - Enable/disable Push
- `push_fcm_server_key` - FCM Server Key (encrypted)
- `push_fcm_sender_id` - FCM Sender ID
- `push_default_icon` - Default notification icon URL
- `push_default_sound` - Default notification sound

**Multi-Channel:**
- `quiet_hours_enabled` - Enable quiet hours
- `quiet_hours_start` - Start time (HH:MM)
- `quiet_hours_end` - End time (HH:MM)
- `fallback_chain` - Channel priority (comma-separated)

---

## Testing

### Test SMS

```php
php artisan tinker

$sms = app(\App\Services\SmsService::class);
$result = $sms->sendPlainSms('+919876543210', 'Test SMS', null);
var_dump($result);
```

### Test Push

```php
php artisan tinker

$push = app(\App\Services\PushNotificationService::class);

// Register test device first
$device = $push->registerDevice(
    customerId: 1,
    deviceToken: 'test_token',
    deviceType: 'android'
);

// Send test push
$customer = \App\Models\Customer::find(1);
$context = \App\Services\Notification\NotificationContext::fromCustomerId(1);
$result = $push->sendToCustomer($customer, 'test_notification', $context);
var_dump($result);
```

### Run Tests

```bash
# Run notification tests
php artisan test --filter=NotificationChannelsTest

# Run specific test
php artisan test --filter=it_can_send_sms_notification
```

---

## Troubleshooting

### SMS Not Sending

```bash
# Check logs
tail -f storage/logs/laravel.log | grep SMS

# Check settings
php artisan tinker
>>> config('notifications.sms_enabled')
>>> config('notifications.twilio_account_sid')

# Check notification logs
>>> \App\Models\NotificationLog::where('channel', 'sms')->latest()->first()
```

### Push Not Sending

```bash
# Check settings
php artisan tinker
>>> config('notifications.push_enabled')
>>> config('notifications.fcm_server_key')

# Check active devices
>>> \App\Models\CustomerDevice::where('customer_id', 1)->active()->count()

# Check logs
>>> \App\Models\NotificationLog::where('channel', 'push')->latest()->first()
```

### Invalid Token Errors

```php
// Clean up invalid tokens
$pushService = app(\App\Services\PushNotificationService::class);

// This happens automatically when push fails with invalid token
// Manual cleanup:
\App\Models\CustomerDevice::where('is_active', false)
    ->where('updated_at', '<', now()->subDays(30))
    ->delete();
```

---

## API Endpoints (Optional)

If you create API endpoints for mobile app:

### Register Device

```php
// routes/api.php
Route::post('/devices/register', function (Request $request) {
    $pushService = app(PushNotificationService::class);

    $device = $pushService->registerDevice(
        customerId: auth()->id(),
        deviceToken: $request->device_token,
        deviceType: $request->device_type,
        deviceInfo: $request->device_info
    );

    return response()->json(['success' => true, 'device' => $device]);
});
```

### Unregister Device

```php
Route::post('/devices/unregister', function (Request $request) {
    $pushService = app(PushNotificationService::class);

    $success = $pushService->unregisterDevice($request->device_token);

    return response()->json(['success' => $success]);
});
```

### Get Notification Preferences

```php
Route::get('/preferences', function () {
    $customer = auth()->user();

    return response()->json([
        'preferences' => $customer->notification_preferences ?? [
            'channels' => ['whatsapp', 'email'],
        ]
    ]);
});
```

### Update Notification Preferences

```php
Route::post('/preferences', function (Request $request) {
    $customer = auth()->user();

    $customer->update([
        'notification_preferences' => $request->preferences
    ]);

    return response()->json(['success' => true]);
});
```

---

## Mobile App Integration

### React Native Example

```javascript
// Register device on app launch
import messaging from '@react-native-firebase/messaging';
import axios from 'axios';

async function registerForPushNotifications() {
    // Request permission
    const authStatus = await messaging().requestPermission();
    const enabled = authStatus === messaging.AuthorizationStatus.AUTHORIZED;

    if (enabled) {
        // Get FCM token
        const token = await messaging().getToken();

        // Register with backend
        await axios.post('/api/devices/register', {
            device_token: token,
            device_type: Platform.OS,
            device_info: {
                device_name: await DeviceInfo.getDeviceName(),
                device_model: DeviceInfo.getModel(),
                os_version: DeviceInfo.getSystemVersion(),
                app_version: DeviceInfo.getVersion()
            }
        });
    }
}

// Handle foreground messages
messaging().onMessage(async remoteMessage => {
    console.log('Foreground message:', remoteMessage);
    // Show local notification
});

// Handle background/quit state messages
messaging().setBackgroundMessageHandler(async remoteMessage => {
    console.log('Background message:', remoteMessage);
});

// Handle notification tap
messaging().onNotificationOpenedApp(remoteMessage => {
    console.log('Notification opened:', remoteMessage);

    // Navigate based on deep link
    const deepLink = remoteMessage.data.deep_link;
    if (deepLink) {
        navigation.navigate(parseDeepLink(deepLink));
    }
});
```

---

## Best Practices

### 1. Always Use Queues for Production

```php
// Create a job
php artisan make:job SendSmsNotification

// Dispatch job
dispatch(new SendSmsNotification($customer, $notificationTypeCode, $context));
```

### 2. Respect Customer Preferences

```php
// Always check before sending
if (!canSendToCustomer($customer, $notificationTypeCode, 'sms')) {
    return;
}
```

### 3. Handle Errors Gracefully

```php
try {
    $smsService->sendTemplatedSms(...);
} catch (\Exception $e) {
    Log::error('SMS failed', ['error' => $e->getMessage()]);
    // Fallback to another channel
}
```

### 4. Clean Up Inactive Devices

```php
// Schedule in app/Console/Kernel.php
$schedule->command('devices:cleanup')->daily();

// Create command
php artisan make:command CleanupInactiveDevices
```

### 5. Monitor Notification Logs

```php
// Check failed notifications daily
$failed = NotificationLog::failed()
    ->where('created_at', '>=', now()->subDay())
    ->count();

if ($failed > 100) {
    // Alert admin
}
```

---

## Support

For issues or questions:
1. Check `storage/logs/laravel.log`
2. Review `notification_logs` table
3. Verify API credentials in app settings
4. Test with Twilio/FCM console
5. Check customer notification preferences
