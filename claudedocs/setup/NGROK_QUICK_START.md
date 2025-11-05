# 🚀 ngrok Quick Start Guide

## ✅ Setup Complete!

I've created **3 different batch files** for starting ngrok. Try them in order:

---

## 📁 Available Batch Files

### 1. **start-ngrok-direct.bat** ⭐ (RECOMMENDED - Start with this)
- **Simplest and most reliable**
- Uses direct command: `ngrok http 8085`
- No configuration needed
- Just double-click and it works!

### 2. **start-ngrok-simple.bat**
- Uses the config file
- More user-friendly output
- Shows error messages if it fails

### 3. **start-ngrok.bat**
- Original file with full configuration
- Most detailed output

---

## 🎯 How to Use

### Step 1: Start XAMPP
Make sure Apache is running on port **8085**

Test locally:
```
http://localhost:8085
http://midastech.testing.in:8085
```

### Step 2: Start ngrok

**Double-click**: `start-ngrok-direct.bat`

You should see output like:
```
ngrok

Session Status                online
Account                       (Plan: Free)
Version                       3.24.0
Region                        United States (us)
Latency                       -
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123.ngrok-free.app -> http://localhost:8085

Connections                   ttl     opn     rt1     rt5     p50     p90
                              0       0       0.00    0.00    0.00    0.00
```

### Step 3: Copy Your Webhook URL

From the output above, you'll see something like:
```
Forwarding  https://abc123.ngrok-free.app -> http://localhost:8085
```

**Your webhook URL is**:
```
https://abc123.ngrok-free.app/webhooks/payments/razorpay
```

Copy this URL!

### Step 4: Add to Razorpay Dashboard

1. Go to: https://dashboard.razorpay.com/app/webhooks
2. Click "Add New Webhook"
3. Paste: `https://abc123.ngrok-free.app/webhooks/payments/razorpay`
4. Select events:
   - ✅ payment.captured
   - ✅ payment.failed
   - ✅ payment.authorized
   - ✅ order.paid
   - ✅ refund.created
5. Copy the webhook secret
6. Add to `.env`: `RAZORPAY_WEBHOOK_SECRET=whsec_xxxxx`

### Step 5: Test!

Open ngrok dashboard: http://127.0.0.1:4040

Try the webhook:
```cmd
curl https://abc123.ngrok-free.app/webhooks/payments/razorpay
```

You should see the request in ngrok dashboard!

---

## 🐛 Troubleshooting

### Issue: CMD window closes immediately

**Try**: `start-ngrok-simple.bat` or `start-ngrok-direct.bat`

These files have `pause` commands to keep the window open.

### Issue: "address already in use"

Another ngrok instance is running.

**Solution**: Close all ngrok windows and try again.

### Issue: "failed to listen on localhost:8085"

XAMPP is not running on port 8085.

**Solution**:
1. Start XAMPP Apache
2. Check port: http://localhost:8085
3. Try ngrok again

### Issue: Can't access through ngrok URL

1. Test locally first: http://localhost:8085
2. Check ngrok dashboard: http://127.0.0.1:4040
3. Look for errors in ngrok output

### Issue: ngrok URL keeps changing

This is normal with free ngrok!

**Solution**: Update Razorpay webhook URL each time you restart ngrok.

---

## 🎛️ ngrok Dashboard

While ngrok is running:
- **URL**: http://127.0.0.1:4040
- **Features**:
  - See all requests in real-time
  - View request/response details
  - Replay requests
  - Inspect headers

---

## 📋 Quick Commands

```cmd
# Start ngrok (easiest)
start-ngrok-direct.bat

# Or use command directly
ngrok http 8085

# Check if ngrok is running
curl http://127.0.0.1:4040/api/tunnels

# Test webhook route
curl https://your-ngrok-url.ngrok-free.app/webhooks/payments/razorpay
```

---

## 🔗 Important URLs

| Service | URL | Notes |
|---------|-----|-------|
| **Local site** | http://localhost:8085 | Test first |
| **Local domain** | http://midastech.testing.in:8085 | Alternative |
| **ngrok URL** | https://xxxxx.ngrok-free.app | Changes each restart |
| **ngrok Dashboard** | http://127.0.0.1:4040 | Real-time monitoring |
| **Webhook route** | /webhooks/payments/razorpay | Add to ngrok URL |

---

## ✅ Complete Workflow

1. ✅ Start XAMPP Apache (port 8085)
2. ✅ Test: http://localhost:8085
3. ✅ Run: `start-ngrok-direct.bat`
4. ✅ Copy ngrok URL from output
5. ✅ Add webhook to Razorpay: `https://xxxxx.ngrok-free.app/webhooks/payments/razorpay`
6. ✅ Copy webhook secret to `.env`
7. ✅ Test payment flow!

---

## 💡 Pro Tips

1. **Keep ngrok window open** while testing webhooks
2. **Watch ngrok dashboard** to see incoming requests
3. **Check Laravel logs** for webhook processing:
   ```cmd
   php artisan mcp__laravel-boost__read-log-entries --entries=50
   ```
4. **Bookmark ngrok dashboard**: http://127.0.0.1:4040

---

## 🆘 Still Not Working?

Try the absolute simplest command:

```cmd
# Open Command Prompt
# Navigate to project
cd C:\xampp\htdocs\webmonks\midas-portal

# Run ngrok directly
ngrok http 8085
```

If this works, the issue is with the batch files.
If this doesn't work, check if XAMPP is running.

---

**Setup completed**: 2025-11-05
**ngrok version**: 3.24.0
**Ready to test!** 🎉
