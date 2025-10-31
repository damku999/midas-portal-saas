# WhatsApp Integration Issue Analysis

## Problem Summary
WhatsApp onboarding message fails with "Invalid authentication token" error.

## Root Cause
The `whatsapp_auth_token` in the `app_settings` table is **double-encrypted** and cannot be properly decrypted.

### Investigation Results

**Database Value:**
```
Key: whatsapp_auth_token
Value: eyJpdiI6ImNqU1QxTE5XOUYwcTdzTjB1cHpDUnc9PSIsInZhbHVlIjoiaElsMjl1ZEhBT1ExQ1JYNHBXc0xoZnJ2RVV6TVR6bktIeTNnWnVyQ1Y1ZWZLblJTSDV5RnpSSDlHYWJ0Y3haWSIsIm1hYyI6ImVlMjE5NTA3Mjc2MjE1NDBhNmMxOGZjMWMwMzNiNDVhMzEzMWYyYjViZTJhOWNmNmNmZTBjOTZmYjFkYWEwNGYiLCJ0YWciOiIifQ==
is_encrypted: 1 (TRUE)
```

**After Model Decryption:**
```
Decrypted Value: eyJpdiI6ImNqU1QxTE5XOUYwcTdzTjB1cHpDUnc9PSIsInZhbHVlIjoiaElsMjl1ZEhBT1ExQ1JYNHBXc0xoZnJ2RVV6TVR6bktIeTNnWnVyQ1Y1ZWZLblJTSDV5RnpSSDlHYWJ0Y3haWSIsIm1hYyI6ImVlMjE5NTA3Mjc2MjE1NDBhNmMxOGZjMWMwMzNiNDVhMzEzMWYyYjViZTJhOWNmNmNmZTBjOTZmYjFkYWEwNGYiLCJ0YWciOiIifQ==
Length: 256 characters
Format: Still a Laravel encrypted payload (starts with "eyJ")
```

**Issue:**
- The decrypted value is IDENTICAL to the encrypted value
- This means the token was stored as an already-encrypted string
- It was likely encrypted with a different `APP_KEY`
- Cannot decrypt further with current APP_KEY (MAC invalid)

### WhatsApp API Test Result

**Request:**
```
URL: https://api.botmastersender.com/api/v1/?action=send
Method: POST
senderId: 919727793123
authToken: eyJpdiI6ImNqU1QxTE5XOUYwcTdzTjB1cHpDUnc9PSIsInZhbHVlIjoiaElsMjl1ZEhBT1ExQ1JYNHBXc0xoZnJ2RVV6TVR6bktIeTNnWnVyQ1Y1ZWZLblJTSDV5RnpSSDlHYWJ0Y3haWSIsIm1hYyI6ImVlMjE5NTA3Mjc2MjE1NDBhNmMxOGZjMWMwMzNiNDVhMzEzMWYyYjViZTJhOWNmNmNmZTBjOTZmYjFkYWEwNGYiLCJ0YWciOiIifQ==
messageText: Test message
receiverId: 919727793123
```

**Response:**
```json
{
  "success": false,
  "message": "Invalid authentication token",
  "error": "Access denied due to account status"
}
```

**HTTP Status:** 400

## WhatsApp Configuration

### Current Settings
```
whatsapp_sender_id: 919727793123
whatsapp_base_url: https://api.botmastersender.com/api/v1/
whatsapp_auth_token: [DOUBLE ENCRYPTED - INVALID]
whatsapp_notifications_enabled: true
```

### Implementation Details

**Trait:** `app/Traits/WhatsAppApiTrait.php`
- Method: `whatsAppSendMessage()` at line 52
- Retrieves config: `config('whatsapp.auth_token')`
- Loaded by: `DynamicConfigServiceProvider.php:66`

**Flow:**
```
1. DynamicConfigServiceProvider loads from database
   ↓
2. AppSetting model decrypts (first decryption)
   ↓
3. config('whatsapp.auth_token') returns decrypted value
   ↓
4. WhatsAppApiTrait sends to BotMasterSender API
   ↓
5. API rejects: "Invalid authentication token"
```

## Solution

### Option 1: Update Auth Token (Recommended)
Replace the double-encrypted token with the actual BotMasterSender auth token:

```php
$tokenSetting = \App\Models\AppSetting::where('key', 'whatsapp_auth_token')->first();
$tokenSetting->value = 'YOUR_ACTUAL_BOTMASTER_TOKEN_HERE'; // Will auto-encrypt
$tokenSetting->save();

\Artisan::call('config:clear');
\Artisan::call('cache:clear');
```

### Option 2: Check BotMasterSender Dashboard
1. Login to https://botmastersender.com/
2. Go to API Settings
3. Copy the Auth Token
4. Update in app_settings table

### Option 3: Store as Non-Encrypted
If the token is a public API key:

```php
$tokenSetting = \App\Models\AppSetting::where('key', 'whatsapp_auth_token')->first();
$tokenSetting->is_encrypted = false;
$tokenSetting->value = 'YOUR_ACTUAL_TOKEN';
$tokenSetting->save();
```

## BotMasterSender Requirements

### Expected Token Format
- UUID format (e.g., `53eb1f03-90be-49ce-9dbe-b23fe982b31f`)
- Or custom API key from dashboard
- Should be 36-40 characters typically

### Account Status
The error message "Access denied due to account status" suggests:
1. Invalid/expired token
2. Account suspended/inactive
3. Token from different account/environment

## Testing After Fix

```php
// Test WhatsApp API connection
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.botmastersender.com/api/v1/?action=send',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'senderId' => '919727793123',
        'authToken' => 'YOUR_ACTUAL_TOKEN',
        'messageText' => 'Test message',
        'receiverId' => '919727793123',
    ],
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
```

**Expected Success Response:**
```json
[{
  "success": true,
  "message": "Message sent successfully"
}]
```

## Files Referenced

1. `app/Traits/WhatsAppApiTrait.php:52` - Main WhatsApp sending logic
2. `app/Services/CustomerService.php:297` - `sendOnboardingMessage()`
3. `app/Providers/DynamicConfigServiceProvider.php:66` - WhatsApp config loading
4. `app/Models/AppSetting.php:67` - Decryption accessor
5. Database: `app_settings.whatsapp_auth_token`

## Next Steps

1. ✅ Identified issue: Double-encrypted/invalid auth token
2. ⏳ **Required**: Provide actual BotMasterSender auth token
3. ⏳ Update token in database with correct value
4. ⏳ Clear caches
5. ⏳ Test WhatsApp API connection
6. ⏳ Test customer onboarding message

## Additional Notes

- SMTP email system is working correctly with Hostinger
- WhatsApp notifications are enabled globally
- Sender ID (919727793123) appears valid
- API endpoint (botmastersender.com) is correct
- Only the auth token needs to be fixed
