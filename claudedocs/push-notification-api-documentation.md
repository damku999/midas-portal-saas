# Push Notification API Documentation
## Midas Portal Mobile App Integration

**Version:** 1.0
**Last Updated:** 2025-10-31
**Base URL:** `https://your-domain.com/api`

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [API Endpoints](#api-endpoints)
4. [Integration Guide](#integration-guide)
5. [Error Handling](#error-handling)
6. [Testing](#testing)
7. [FAQ](#faq)

---

## Overview

The Push Notification API allows mobile apps (Android, iOS, Web) to register devices for receiving push notifications via Firebase Cloud Messaging (FCM).

**Key Features:**
- Device registration and unregistration
- Multi-device support (one customer can have multiple devices)
- Automatic device cleanup (inactive devices after 90 days)
- Heartbeat mechanism to keep devices active
- Device management (view, update, deactivate)

---

## Authentication

All API endpoints require **Laravel Sanctum** authentication.

### Getting Authentication Token

Customer must first log in via the authentication API (not documented here - use existing customer login flow).

Once logged in, include the token in all API requests:

```http
Authorization: Bearer {sanctum_token}
```

**Example:**
```http
POST /api/customer/device/register
Authorization: Bearer 1|aBcD...eFgH
Content-Type: application/json
```

---

## API Endpoints

### 1. Register Device

**Endpoint:** `POST /api/customer/device/register`

Register a new device or update existing device for push notifications.

#### Request Headers
```http
Content-Type: application/json
Authorization: Bearer {sanctum_token}
```

#### Request Body
```json
{
  "device_token": "FCM_TOKEN_HERE",
  "device_type": "android",
  "device_name": "John's Phone",
  "device_model": "Samsung Galaxy S21",
  "os_version": "Android 13",
  "app_version": "1.0.0"
}
```

#### Parameters

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `device_token` | string | ✅ Yes | FCM device token (max 500 chars) |
| `device_type` | string | ✅ Yes | Device platform: `android`, `ios`, or `web` |
| `device_name` | string | ❌ No | Human-readable device name |
| `device_model` | string | ❌ No | Device hardware model |
| `os_version` | string | ❌ No | Operating system version |
| `app_version` | string | ❌ No | Mobile app version |

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Device registered successfully",
  "device": {
    "id": 1,
    "customer_id": 123,
    "device_type": "android",
    "device_token": "FCM_TOKEN...",
    "device_name": "John's Phone",
    "device_model": "Samsung Galaxy S21",
    "os_version": "Android 13",
    "app_version": "1.0.0",
    "is_active": true,
    "last_active_at": "2025-10-31T10:00:00.000000Z",
    "created_at": "2025-10-31T10:00:00.000000Z"
  }
}
```

#### Error Responses

**422 Unprocessable Entity** (Validation Failed)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "device_token": ["The device token field is required."],
    "device_type": ["The device type must be android, ios, or web."]
  }
}
```

**422 Unprocessable Entity** (Device Limit Reached)
```json
{
  "success": false,
  "message": "Maximum 5 devices allowed per customer",
  "error": "device_limit_reached"
}
```

---

### 2. Unregister Device

**Endpoint:** `POST /api/customer/device/unregister`

Remove device token from push notifications. Call when user logs out or uninstalls app.

#### Request Body
```json
{
  "device_token": "FCM_TOKEN_HERE"
}
```

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Device unregistered successfully"
}
```

#### Error Response (404 Not Found)
```json
{
  "success": false,
  "message": "Device not found or does not belong to you"
}
```

---

### 3. Update Device Information

**Endpoint:** `PUT /api/customer/device/update`

Update device metadata without changing the device token.

#### Request Body
```json
{
  "device_token": "FCM_TOKEN_HERE",
  "device_name": "John's New Phone",
  "os_version": "Android 14",
  "app_version": "1.1.0"
}
```

#### Parameters

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `device_token` | string | ✅ Yes | FCM device token to update |
| `device_name` | string | ❌ No | New device name |
| `device_model` | string | ❌ No | Device model |
| `os_version` | string | ❌ No | Updated OS version |
| `app_version` | string | ❌ No | Updated app version |

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Device updated successfully",
  "device": {
    "id": 1,
    "customer_id": 123,
    "device_type": "android",
    "device_name": "John's New Phone",
    "os_version": "Android 14",
    "app_version": "1.1.0",
    "is_active": true,
    "last_active_at": "2025-10-31T10:30:00.000000Z"
  }
}
```

---

### 4. Get Customer's Devices

**Endpoint:** `GET /api/customer/devices`

Fetch all registered devices for the authenticated customer.

#### Request Headers
```http
Authorization: Bearer {sanctum_token}
```

#### Success Response (200 OK)
```json
{
  "success": true,
  "devices": [
    {
      "id": 1,
      "device_type": "android",
      "device_name": "John's Phone",
      "device_model": "Samsung Galaxy S21",
      "os_version": "Android 13",
      "app_version": "1.0.0",
      "is_active": true,
      "last_active_at": "2025-10-31T10:00:00.000000Z",
      "created_at": "2025-10-31T09:00:00.000000Z"
    },
    {
      "id": 2,
      "device_type": "ios",
      "device_name": "John's iPad",
      "device_model": "iPad Pro",
      "os_version": "iOS 16.2",
      "app_version": "1.0.0",
      "is_active": true,
      "last_active_at": "2025-10-30T18:00:00.000000Z",
      "created_at": "2025-10-29T12:00:00.000000Z"
    }
  ],
  "total": 2
}
```

---

### 5. Deactivate Device

**Endpoint:** `POST /api/customer/device/{device_id}/deactivate`

Mark a specific device as inactive to stop receiving notifications.

#### URL Parameters
- `{device_id}` - Device ID (integer)

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Device deactivated successfully"
}
```

#### Error Response (403 Forbidden)
```json
{
  "success": false,
  "message": "Unauthorized - device does not belong to you"
}
```

---

### 6. Send Heartbeat

**Endpoint:** `POST /api/customer/device/heartbeat`

Update device's `last_active_at` timestamp to prevent automatic cleanup.

**Recommended:** Call this every time the app opens or every 24 hours while in use.

#### Request Body
```json
{
  "device_token": "FCM_TOKEN_HERE"
}
```

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Heartbeat recorded",
  "last_active_at": "2025-10-31T10:45:00.000000Z"
}
```

---

## Integration Guide

### Android (Kotlin)

#### Step 1: Initialize Firebase
```kotlin
// build.gradle (app)
dependencies {
    implementation 'com.google.firebase:firebase-messaging:23.2.1'
}

// Add google-services.json to app/
```

#### Step 2: Get FCM Token
```kotlin
import com.google.firebase.messaging.FirebaseMessaging

class MainActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        // Get FCM token
        FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
            if (task.isSuccessful) {
                val token = task.result
                registerDevice(token)
            }
        }
    }

    private fun registerDevice(fcmToken: String) {
        val apiService = RetrofitClient.create()
        val deviceInfo = DeviceRegistrationRequest(
            device_token = fcmToken,
            device_type = "android",
            device_name = Build.MODEL,
            device_model = Build.DEVICE,
            os_version = "Android ${Build.VERSION.RELEASE}",
            app_version = BuildConfig.VERSION_NAME
        )

        apiService.registerDevice("Bearer $authToken", deviceInfo)
            .enqueue(object : Callback<DeviceResponse> {
                override fun onResponse(call: Call, response: Response) {
                    if (response.isSuccessful) {
                        Log.d("FCM", "Device registered successfully")
                    }
                }
                override fun onFailure(call: Call, t: Throwable) {
                    Log.e("FCM", "Registration failed: ${t.message}")
                }
            })
    }
}
```

#### Step 3: Handle Push Notifications
```kotlin
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class MyFirebaseMessagingService : FirebaseMessagingService() {

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        // Handle notification
        val title = remoteMessage.notification?.title
        val body = remoteMessage.notification?.body
        val data = remoteMessage.data

        // Show notification
        showNotification(title, body, data)
    }

    override fun onNewToken(token: String) {
        // Token refreshed - re-register with backend
        registerDevice(token)
    }

    private fun showNotification(title: String?, body: String?, data: Map<String, String>) {
        // Create notification with deep link handling
        val notificationTypeCode = data["notification_type"]
        val deepLink = data["deep_link"]

        // Use deep link to navigate to appropriate screen
        // Example: app://insurance/123 → InsuranceDetailActivity
    }
}
```

---

### iOS (Swift)

#### Step 1: Initialize Firebase
```swift
// AppDelegate.swift
import Firebase
import UserNotifications

@UIApplicationMain
class AppDelegate: UIResponder, UIApplicationDelegate {

    func application(_ application: UIApplication,
                     didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {

        // Initialize Firebase
        FirebaseApp.configure()

        // Request notification permission
        UNUserNotificationCenter.current().requestAuthorization(options: [.alert, .badge, .sound]) { granted, _ in
            if granted {
                DispatchQueue.main.async {
                    application.registerForRemoteNotifications()
                }
            }
        }

        return true
    }

    func application(_ application: UIApplication,
                     didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data) {
        // Get FCM token
        Messaging.messaging().apnsToken = deviceToken
    }
}
```

#### Step 2: Get FCM Token and Register
```swift
import FirebaseMessaging

extension AppDelegate: MessagingDelegate {

    func messaging(_ messaging: Messaging, didReceiveRegistrationToken fcmToken: String?) {
        guard let token = fcmToken else { return }

        // Register device with backend
        registerDevice(fcmToken: token)
    }

    private func registerDevice(fcmToken: String) {
        let deviceInfo = [
            "device_token": fcmToken,
            "device_type": "ios",
            "device_name": UIDevice.current.name,
            "device_model": UIDevice.current.model,
            "os_version": "iOS \(UIDevice.current.systemVersion)",
            "app_version": Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "1.0"
        ]

        API.shared.registerDevice(deviceInfo) { result in
            switch result {
            case .success:
                print("Device registered successfully")
            case .failure(let error):
                print("Registration failed: \(error)")
            }
        }
    }
}
```

---

### React Native

#### Step 1: Install Dependencies
```bash
npm install @react-native-firebase/app @react-native-firebase/messaging
```

#### Step 2: Register Device
```javascript
import messaging from '@react-native-firebase/messaging';
import { Platform } from 'react-native';
import DeviceInfo from 'react-native-device-info';

async function registerDeviceForPushNotifications() {
  try {
    // Request permission
    const authStatus = await messaging().requestPermission();
    const enabled = authStatus === messaging.AuthorizationStatus.AUTHORIZED
                 || authStatus === messaging.AuthorizationStatus.PROVISIONAL;

    if (!enabled) {
      console.log('Push notification permission denied');
      return;
    }

    // Get FCM token
    const fcmToken = await messaging().getToken();

    // Get device info
    const deviceInfo = {
      device_token: fcmToken,
      device_type: Platform.OS, // 'ios' or 'android'
      device_name: await DeviceInfo.getDeviceName(),
      device_model: DeviceInfo.getModel(),
      os_version: DeviceInfo.getSystemVersion(),
      app_version: DeviceInfo.getVersion()
    };

    // Register with backend
    const response = await fetch('https://your-domain.com/api/customer/device/register', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(deviceInfo)
    });

    const result = await response.json();
    console.log('Device registered:', result);

  } catch (error) {
    console.error('FCM registration error:', error);
  }
}

// Call on app startup after user login
registerDeviceForPushNotifications();
```

#### Step 3: Handle Notifications
```javascript
import messaging from '@react-native-firebase/messaging';
import { Linking } from 'react-native';

// Background/quit state message handler
messaging().setBackgroundMessageHandler(async remoteMessage => {
  console.log('Message handled in the background!', remoteMessage);
});

// Foreground message handler
messaging().onMessage(async remoteMessage => {
  const { title, body } = remoteMessage.notification;
  const { notification_type, deep_link } = remoteMessage.data;

  // Show in-app notification or update UI
  showInAppNotification(title, body);
});

// Handle notification opened (user tapped)
messaging().onNotificationOpenedApp(remoteMessage => {
  const { deep_link } = remoteMessage.data;

  // Navigate to appropriate screen
  // Example: app://insurance/123 → InsuranceDetailScreen
  if (deep_link) {
    Linking.openURL(deep_link);
  }
});

// Check if app was opened from notification (quit state)
messaging()
  .getInitialNotification()
  .then(remoteMessage => {
    if (remoteMessage) {
      const { deep_link } = remoteMessage.data;
      // Navigate to appropriate screen
      if (deep_link) {
        Linking.openURL(deep_link);
      }
    }
  });
```

---

## Error Handling

All API responses follow a consistent format:

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error": "error_code",
  "errors": { ... } // Validation errors
}
```

### Common Error Codes

| HTTP Status | Error Code | Description |
|-------------|------------|-------------|
| 401 | `unauthenticated` | Invalid or missing authentication token |
| 403 | `forbidden` | Resource does not belong to authenticated user |
| 404 | `not_found` | Device or resource not found |
| 422 | `validation_failed` | Request validation failed |
| 422 | `device_limit_reached` | Maximum devices per customer exceeded |
| 500 | `server_error` | Internal server error |

---

## Testing

### Using Postman

1. **Get Authentication Token** (via login API)
2. **Register Device:**
   ```
   POST https://your-domain.com/api/customer/device/register
   Authorization: Bearer YOUR_TOKEN_HERE
   Content-Type: application/json

   {
     "device_token": "test_fcm_token_12345",
     "device_type": "android",
     "device_name": "Test Device",
     "os_version": "Android 13",
     "app_version": "1.0.0"
   }
   ```

3. **Get Devices:**
   ```
   GET https://your-domain.com/api/customer/devices
   Authorization: Bearer YOUR_TOKEN_HERE
   ```

### Testing Push Notifications

Use Firebase Console to send test notifications:

1. Go to Firebase Console → Cloud Messaging
2. Click "Send your first message"
3. Enter notification title and text
4. Click "Send test message"
5. Enter your FCM token
6. Click "Test"

---

## FAQ

### Q: How many devices can one customer register?
**A:** Maximum 5 devices per customer (configurable in `config/push.php`)

### Q: What happens to inactive devices?
**A:** Devices inactive for 90+ days are automatically deactivated. They can be reactivated by calling the register endpoint again.

### Q: How do I prevent my device from being deactivated?
**A:** Call the heartbeat endpoint (`POST /api/customer/device/heartbeat`) every time the app opens or every 24 hours.

### Q: Can I register the same FCM token for multiple customers?
**A:** No. One FCM token can only be associated with one customer. If a token is registered for a different customer, it will be updated to the new customer.

### Q: What data is included in push notifications?
**A:** Notifications include:
- `title` and `body` (rendered from templates)
- `notification_type` - Code for app routing (e.g., "renewal_7_days")
- `deep_link` - App URI for navigation (e.g., "app://insurance/123")
- `insurance_id` / `quotation_id` / `claim_id` - Related entity IDs

### Q: How do I handle deep links?
**A:** Extract the `deep_link` from notification data and use your app's navigation system to open the appropriate screen.

---

## Support

For technical support or questions:
- **Email:** support@midasportal.com
- **Documentation:** https://your-domain.com/docs
- **Admin Panel:** https://your-domain.com/admin/customer-devices

---

**Document Version:** 1.0
**Last Updated:** 2025-10-31
