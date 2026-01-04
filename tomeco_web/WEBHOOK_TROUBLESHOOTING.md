# Webhook Troubleshooting Guide

## Issue: Payment Recorded in PayMongo but Status Not Updated

If payments are recorded in PayMongo but ticket status doesn't update, follow these steps:

## Step 1: Check if Webhook is Being Received

### Check Laravel Logs
```bash
# View recent logs
Get-Content storage/logs/laravel.log -Tail 100

# Or search for webhook entries
Get-Content storage/logs/laravel.log | Select-String -Pattern "webhook|PayMongo|payment"
```

**Look for:**
- ✅ `PayMongo webhook received` - Webhook is reaching your server
- ❌ No webhook entries - Webhook not reaching server

### Check Server Logs
1. Check your Render service logs or local server logs
2. Look for requests to `/violator/payment/callback`
3. Check if PayMongo is sending webhooks

## Step 2: Verify Webhook Configuration

### Check PayMongo Webhook Settings
1. Go to: https://dashboard.paymongo.com/
2. Make sure you're in **Test Mode** (for test keys)
3. Navigate to: **Settings** → **Webhooks**
4. Verify webhook URL matches your server URL:
   ```
   https://your-server-url.onrender.com/violator/payment/callback
   ```
5. Check webhook events are enabled:
   - ✅ `checkout_session.payment_succeeded`
   - ✅ `payment.paid`

### Check .env File
```env
PAYMONGO_WEBHOOK_SECRET=whsec_your_webhook_secret
APP_URL=https://your-server-url.onrender.com
APP_PORTAL_TYPE=violator
```

**Important:** 
- Webhook secret must match PayMongo dashboard
- APP_URL must match your server URL (Render service URL for production)
- Clear config after changes: `php artisan config:clear`

## Step 3: Check Webhook Signature

If you see `Invalid PayMongo webhook signature` in logs:

1. **Get correct webhook secret:**
   - Go to PayMongo Dashboard → Settings → Webhooks
   - Click on your webhook
   - Copy the webhook secret (starts with `whsec_` or `hook_`)

2. **Update .env:**
   ```env
   PAYMONGO_WEBHOOK_SECRET=whsec_your_correct_secret
   ```

3. **Clear config:**
   ```bash
   php artisan config:clear
   ```

4. **Test again**

## Step 4: Check Metadata in Webhook

The webhook handler looks for:
- `ticket_id` in metadata
- `citation_number` in metadata

**Check logs for:**
- `Extracted metadata from webhook` - Shows what metadata was found
- `No ticket ID in payment webhook metadata` - Metadata missing

**If metadata is missing:**
- Check if metadata is being sent when creating checkout session
- Verify ticket_id is being passed correctly

## Step 5: Test Webhook Manually

### Option 1: Use PayMongo Dashboard
1. Go to PayMongo Dashboard → Settings → Webhooks
2. Click on your webhook
3. Click "Send Test Event"
4. Select `checkout_session.payment_succeeded`
5. Check Laravel logs for the webhook

### Option 2: Check Server Logs
1. Check your Render service logs or local server logs
2. Look for POST requests to `/violator/payment/callback`
3. Review log entries to see:
   - Request payload
   - Response status
   - Response body

## Step 6: Common Issues and Solutions

### Issue: "No webhook entries in logs"
**Solution:**
- Verify server is running and accessible (Render service or localhost)
- Check PayMongo webhook URL matches your server URL exactly
- Make sure violator server is running
- Check PayMongo dashboard shows webhook as "Active"
- **Note:** Webhooks won't work on localhost without a public URL tunnel

### Issue: "Invalid webhook signature"
**Solution:**
- Update webhook secret in .env
- Make sure you're using TEST webhook secret for test keys
- Clear config cache

### Issue: "No ticket ID in metadata"
**Solution:**
- Check if metadata is being passed when creating checkout session
- Verify ticket_id exists in the ticket
- Check PaymentController.php line 64 - metadata should include ticket_id

### Issue: "Ticket not found"
**Solution:**
- Verify ticket_id in metadata matches actual ticket ID
- Check database to confirm ticket exists
- Make sure you're using the same database for both servers

## Step 7: Enhanced Logging

I've added comprehensive logging to help debug. Check logs for:

1. **Webhook received:**
   ```
   PayMongo webhook received
   ```

2. **Event type:**
   ```
   Processing PayMongo webhook event: checkout_session.payment_succeeded
   ```

3. **Metadata extraction:**
   ```
   Extracted metadata from webhook
   ```

4. **Status update:**
   ```
   ✅ Payment successful - Ticket status updated
   ```

## Quick Debug Checklist

- [ ] Server is running and accessible (Render service or localhost)
- [ ] Violator server is running (port 8001 for localhost, or Render service)
- [ ] PayMongo webhook URL matches your server URL exactly
- [ ] Webhook secret in .env matches PayMongo dashboard
- [ ] APP_URL in .env matches your server URL
- [ ] Config cache cleared: `php artisan config:clear`
- [ ] Check Laravel logs for webhook entries
- [ ] Check server logs for incoming requests
- [ ] Test webhook from PayMongo dashboard

## Still Not Working?

1. **Check full webhook payload:**
   - Look in Laravel logs for `PayMongo webhook data structure`
   - This shows the exact structure PayMongo is sending

2. **Verify ticket exists:**
   - Check database: `SELECT * FROM tickets WHERE id = [ticket_id]`

3. **Test with a simple webhook:**
   - Create a test endpoint to see raw webhook data
   - Compare with expected structure

4. **Check PayMongo documentation:**
   - Verify webhook structure matches PayMongo's format
   - Check if there are any recent API changes

