# Push Notification Setup Guide
## Quick Start for Midas Portal

**Last Updated:** 2025-10-31

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Get Firebase Credentials

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create new project or select existing one
3. Go to **Project Settings** ⚙️
4. Click **Cloud Messaging** tab
5. Copy these values:
   - **Server Key** → Copy the entire key
   - **Sender ID** → Copy the number

### Step 2: Configure Laravel Backend

1. Open `.env` file in your Laravel project root
2. Add these lines (replace with your actual values):

```env
# Enable Push Notifications
PUSH_NOTIFICATIONS_ENABLED=true

# Firebase Cloud Messaging Credentials
FCM_SERVER_KEY=AAAAxxx...your_server_key_here
FCM_SENDER_ID=123456789...your_sender_id_here

# Optional: Customize notification appearance
PUSH_DEFAULT_ICON=/images/logo.png
PUSH_DEFAULT_SOUND=default
```

3. Save the file

### Step 3: Test the API

Use Postman or any API client:

**Test Endpoint:**
```http
GET https://your-domain.com/api/customer/devices
Authorization: Bearer YOUR_AUTH_TOKEN_HERE
```

**Expected Response:**
```json
{
  "success": true,
  "devices": [],
  "total": 0
}
```

If you see this response, the API is working! ✅

---

## 📱 Mobile App Integration

### For Android Developers

**Add to `build.gradle` (app-level):**
```gradle
dependencies {
    implementation 'com.google.firebase:firebase-messaging:23.2.1'
    implementation 'com.squareup.retrofit2:retrofit:2.9.0'
    implementation 'com.squareup.retrofit2:converter-gson:2.9.0'
}
```

**Download `google-services.json`:**
1. Go to Firebase Console → Project Settings
2. Under "Your apps" → Android app
3. Download `google-services.json`
4. Place it in `app/` directory

**Register Device:**
```kotlin
// After user logs in
FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
    val fcmToken = task.result

    // Call API to register device
    val request = DeviceRegistrationRequest(
        device_token = fcmToken,
        device_type = "android",
        device_name = Build.MODEL,
        os_version = "Android ${Build.VERSION.RELEASE}",
        app_version = BuildConfig.VERSION_NAME
    )

    apiService.registerDevice("Bearer $authToken", request)
}
```

### For iOS Developers

**Add Firebase to `Podfile`:**
```ruby
pod 'Firebase/Messaging'
```

**Download `GoogleService-Info.plist`:**
1. Go to Firebase Console → Project Settings
2. Under "Your apps" → iOS app
3. Download `GoogleService-Info.plist`
4. Add to Xcode project

**Register Device:**
```swift
// After user logs in
Messaging.messaging().token { token, error in
    guard let fcmToken = token else { return }

    // Call API to register device
    API.shared.registerDevice(
        deviceToken: fcmToken,
        deviceType: "ios",
        deviceName: UIDevice.current.name,
        osVersion: UIDevice.current.systemVersion,
        appVersion: Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String
    )
}
```

---

## 🔧 Configuration Options

### Laravel Configuration Files

**`config/notifications.php`** - Main notification settings
```php
'push_enabled' => env('PUSH_NOTIFICATIONS_ENABLED', false),
'fcm_server_key' => env('FCM_SERVER_KEY', ''),
'fcm_sender_id' => env('FCM_SENDER_ID', ''),
```

**`config/push.php`** - Detailed push notification settings
```php
'device_management' => [
    'max_devices_per_customer' => 5,      // Max devices per customer
    'token_expiry_days' => 90,             // Days before cleanup
    'auto_cleanup' => true,                // Auto-remove inactive devices
],
```

---

## 📊 Admin Panel

**View Registered Devices:**
- URL: `https://your-domain.com/admin/customer-devices`
- Features:
  - See all registered devices
  - Filter by device type (Android/iOS/Web)
  - Filter by status (Active/Inactive)
  - View device details and notification history
  - Deactivate devices
  - Cleanup inactive devices (90+ days)

**Statistics Available:**
- Total devices registered
- Active devices count
- Inactive devices count
- Breakdown by platform (Android, iOS, Web)

---

## 🧪 Testing Push Notifications

### Method 1: Firebase Console (Quick Test)

1. Go to Firebase Console → Cloud Messaging
2. Click **"Send your first message"**
3. Fill in:
   - **Notification title:** Test Notification
   - **Notification text:** This is a test
4. Click **"Send test message"**
5. Enter your FCM token (get from device)
6. Click **"Test"**

### Method 2: Backend Testing (Recommended)

Use Laravel Tinker to send push notification:

```php
php artisan tinker
```

```php
// Get a customer with registered device
$customer = \App\Models\Customer::find(1);

// Send push notification
$pushService = app(\App\Services\PushNotificationService::class);

$result = $pushService->sendToCustomer(
    $customer,
    'renewal_7_days',  // notification type code
    \App\Services\Notification\NotificationContext::fromInsurance(
        $customer->insurances()->first()
    )
);

// Check result
print_r($result);
```

**Expected Output:**
```php
Array
(
    [success] => 1
    [total] => 1
    [sent] => 1
    [failed] => 0
)
```

---

## 🐛 Troubleshooting

### Issue: "Push notifications disabled"

**Solution:** Check `.env` file:
```env
PUSH_NOTIFICATIONS_ENABLED=true  # Must be 'true', not 'false'
```

### Issue: "FCM server key not configured"

**Solution:** Ensure FCM_SERVER_KEY is set in `.env`:
```env
FCM_SERVER_KEY=AAAAxxx...your_actual_key_here
```

### Issue: "Device not receiving notifications"

**Checklist:**
1. ✅ Device is registered (check `/admin/customer-devices`)
2. ✅ Device status is "Active"
3. ✅ FCM token is valid (not expired)
4. ✅ Mobile app has notification permission granted
5. ✅ Firebase project is configured correctly

**Debug:**
```php
// Check if device exists
$device = \App\Models\CustomerDevice::where('device_token', 'YOUR_FCM_TOKEN')->first();
dd($device);

// Check if push is enabled
dd(config('notifications.push_enabled'));

// Check FCM credentials
dd([
    'server_key' => config('notifications.fcm_server_key'),
    'sender_id' => config('notifications.fcm_sender_id'),
]);
```

### Issue: "Device limit reached"

**Solution:** Customer has registered 5 devices (maximum allowed).
- Deactivate old devices via admin panel
- Or increase limit in `config/push.php`:
  ```php
  'max_devices_per_customer' => 10,  // Increase from 5 to 10
  ```

---

## 📚 Additional Resources

**Files Created:**
- ✅ `app/Http/Controllers/Api/CustomerDeviceApiController.php` - API controller
- ✅ `routes/api.php` - API routes
- ✅ `app/Services/PushNotificationService.php` - Push service (already existed)
- ✅ `app/Traits/PushNotificationTrait.php` - FCM integration (already existed)
- ✅ `claudedocs/push-notification-api-documentation.md` - Complete API docs
- ✅ `claudedocs/push-notification-setup-guide.md` - This file

**Database Tables:**
- `customer_devices` - Registered devices
- `notification_logs` - Notification history (channel='push')
- `notification_types` - Notification type definitions

**Admin Routes:**
- `/admin/customer-devices` - Device management
- `/admin/customer-devices/{id}` - Device details
- `/admin/notification-logs` - Notification logs

**API Routes (for mobile app):**
- `POST /api/customer/device/register` - Register device
- `POST /api/customer/device/unregister` - Unregister device
- `PUT /api/customer/device/update` - Update device info
- `GET /api/customer/devices` - Get customer's devices
- `POST /api/customer/device/{id}/deactivate` - Deactivate device
- `POST /api/customer/device/heartbeat` - Keep device active

---

## ✅ Implementation Checklist

### Backend Setup
- [x] Database tables created (`customer_devices`, `notification_logs`)
- [x] Models created (`CustomerDevice`, `NotificationLog`)
- [x] Services implemented (`PushNotificationService`)
- [x] API controller created (`CustomerDeviceApiController`)
- [x] API routes configured (`routes/api.php`)
- [x] Admin panel ready (`/admin/customer-devices`)
- [ ] Configure FCM credentials in `.env`
- [ ] Test API endpoints with Postman

### Mobile App Setup (Per Platform)
- [ ] Add Firebase SDK to mobile app
- [ ] Download Firebase config file (`google-services.json` or `GoogleService-Info.plist`)
- [ ] Implement device registration on login
- [ ] Implement push notification receiver
- [ ] Implement deep link handling
- [ ] Test push notification delivery
- [ ] Test deep link navigation

### Testing
- [ ] Register test device
- [ ] Send test push notification
- [ ] Verify notification appears on device
- [ ] Verify deep link navigation works
- [ ] Verify notification logged in admin panel

---

## 🎯 Next Steps

1. **Configure Firebase** (5 min)
   - Get FCM credentials
   - Add to `.env` file

2. **Test API** (5 min)
   - Use Postman to test endpoints
   - Verify responses

3. **Mobile App Integration** (varies)
   - Android: ~2 hours
   - iOS: ~2 hours
   - React Native: ~1 hour

4. **End-to-End Testing** (30 min)
   - Register device from mobile app
   - Send test notification from backend
   - Verify delivery and navigation

---

**Need Help?**
- View full API documentation: `claudedocs/push-notification-api-documentation.md`
- Check admin panel: `/admin/customer-devices`
- Review notification logs: `/admin/notification-logs`

**Status:** ✅ Backend implementation complete. Mobile app integration pending.
