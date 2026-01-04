# Webhook Not Reaching Server - Debug Steps

## Issue Found
❌ **No webhook entries in Laravel logs** - This means PayMongo webhooks are NOT reaching your server.

## Step-by-Step Debugging

### Step 1: Verify Server is Accessible

1. **Check if your server is accessible:**
   - For production: Verify your Render service URL is accessible
   - For localhost: Make sure your server is running and accessible
   - Test the webhook endpoint directly to ensure it's reachable

### Step 2: Verify PayMongo Webhook Configuration

1. **Go to PayMongo Dashboard:**
   - https://dashboard.paymongo.com/
   - Make sure you're in **Test Mode** (toggle in top right)
   
2. **Check Webhook Settings:**
   - Go to: Settings → Webhooks
   - Verify webhook exists and is **Active**
   - Check webhook URL matches your server URL exactly:
     ```
     https://your-server-url.onrender.com/violator/payment/callback
     ```
   - Make sure events are enabled:
     - ✅ `checkout_session.payment_succeeded`
     - ✅ `payment.paid`

3. **Test Webhook from PayMongo:**
   - Click on your webhook
   - Click "Send Test Event"
   - Select `checkout_session.payment_succeeded`
   - Check Laravel logs for webhook entry

### Step 3: Verify Server URL Matches

1. **Get current server URL:**
   - For production: Use your Render service URL (e.g., `https://tomeco-web-violator.onrender.com`)
   - For localhost: Use your local server URL (webhooks won't work on localhost without a tunnel)

2. **Check .env file:**
   ```env
   APP_URL=https://your-server-url.onrender.com
   ```
   (Must match your server URL exactly)

3. **Check PayMongo webhook URL:**
   - Must be: `https://your-server-url.onrender.com/violator/payment/callback`
   - Must match exactly (no trailing slash, correct path)

### Step 4: Check Violator Server is Running

1. **Verify server is running:**
   ```bash
   # Should be running on port 8001
   start-violator-server.bat
   ```

2. **Test if server is accessible:**
   - Open: `https://your-server-url.onrender.com/violator/portal`
   - Should load your violator portal

### Step 5: Test Webhook Route Directly

1. **Test webhook endpoint:**
   - Use Postman or curl to send a test POST request:
   ```bash
   curl -X POST https://your-server-url.onrender.com/violator/payment/callback \
     -H "Content-Type: application/json" \
     -d '{"test": "data"}'
   ```

2. **Check Laravel logs:**
   - Should see webhook entry even if it fails validation
   - This confirms the route is accessible

### Step 6: Check Webhook Secret

1. **Verify webhook secret in .env:**
   ```env
   PAYMONGO_WEBHOOK_SECRET=whsec_your_secret
   ```

2. **Get correct secret from PayMongo:**
   - Go to webhook settings
   - Copy the webhook secret
   - Update .env if different

3. **Clear config:**
   ```bash
   php artisan config:clear
   ```

## Common Issues

### Issue 1: Server URL Changed
**Symptom:** Webhook was working, then stopped
**Solution:**
- If using Render, verify your service URL hasn't changed
- Update PayMongo webhook URL with correct server URL
- Update APP_URL in .env
- Clear config cache

### Issue 2: Webhook Not Configured in PayMongo
**Symptom:** No webhook entries in logs
**Solution:**
- Create webhook in PayMongo dashboard
- Make sure it's in Test Mode (for test keys)
- Verify webhook is Active

### Issue 3: Wrong Webhook URL
**Symptom:** PayMongo shows webhook as failed
**Solution:**
- Check webhook URL in PayMongo matches your server URL exactly
- Must include `/violator/payment/callback`
- Must use HTTPS (not HTTP)

### Issue 4: Webhook Events Not Enabled
**Symptom:** Webhook exists but no events received
**Solution:**
- Enable these events in PayMongo webhook:
  - `checkout_session.payment_succeeded`
  - `payment.paid`

## Quick Test Checklist

- [ ] Server is running and accessible (Render service or localhost)
- [ ] Violator server is running (port 8001 for localhost, or Render service)
- [ ] PayMongo webhook URL matches your server URL exactly
- [ ] Webhook is Active in PayMongo dashboard
- [ ] Webhook events are enabled
- [ ] APP_URL in .env matches your server URL
- [ ] Webhook secret in .env matches PayMongo
- [ ] Config cache cleared
- [ ] Test webhook from PayMongo dashboard
- [ ] Check Laravel logs for webhook entries

## Next Steps

1. **Verify server accessibility** - Make sure your server URL is reachable
2. **Test webhook from PayMongo** - This will send a test event
3. **Check Laravel logs** - Should see webhook entry after test
4. **If still no webhook entries** - The webhook URL is likely wrong or webhook is not configured

**Note:** For localhost development, webhooks require a public URL. Use Render or another hosting service for webhook testing.

