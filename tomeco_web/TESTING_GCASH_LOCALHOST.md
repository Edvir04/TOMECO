# Testing GCash Payments on Localhost

## Quick Answer
**Yes, you can test GCash payments on localhost**, but with limitations:

✅ **What Works:**
- Payment initiation (redirect to PayMongo)
- User can complete payment on PayMongo
- User returns to your success page

❌ **What Doesn't Work:**
- Webhooks (PayMongo can't reach localhost)
- Automatic ticket status updates via webhook

## Solution 1: Deploy to Render (Recommended for Full Testing)

### Step 1: Deploy to Render
Follow the deployment guide in `RENDER_DEPLOYMENT.md` to deploy your application to Render.

### Step 2: Get Your Render Service URL
After deployment, you'll get a service URL like:
- `https://tomeco-web-violator.onrender.com`

### Step 3: Update your .env
```env
APP_URL=https://tomeco-web-violator.onrender.com
```

### Step 4: Update PayMongo Webhook
1. Go to PayMongo Dashboard → Settings → Webhooks
2. Update webhook URL to: `https://tomeco-web-violator.onrender.com/violator/payment/callback`
3. Copy the new webhook secret and update `.env`:
```env
PAYMONGO_WEBHOOK_SECRET=whsec_your_new_webhook_secret
```

### Step 5: Clear config cache
```bash
php artisan config:clear
```

**Note:** Render free tier services may spin down after inactivity. For consistent testing, consider upgrading to a paid plan or use Option 2.

## Solution 2: Manual Testing (Without Webhooks)

You can test the payment flow without webhooks:

1. **Initiate payment** - User clicks "Pay with GCash"
2. **Complete payment** - User pays on PayMongo
3. **Return to success page** - User sees success message
4. **Manually verify** - Check PayMongo dashboard to confirm payment

The ticket won't be automatically marked as paid, but you can manually update it or add a manual verification step.

## Solution 3: Use Test Keys for Development

For development/testing, you might want to use **TEST keys** instead of LIVE keys:

```env
PAYMONGO_SECRET_KEY=sk_test_your_test_key
PAYMONGO_PUBLIC_KEY=pk_test_your_test_key
```

Test keys allow you to:
- Test without real money
- Use test payment methods
- Test webhooks (with Render or other hosting)

Then switch to LIVE keys only when deploying to production.

## Current Configuration

Your current setup uses **LIVE keys**, which means:
- ⚠️ **Real money transactions** - Be careful!
- ✅ Can test with real GCash accounts
- ❌ Webhooks won't work on localhost

## Recommendation

1. **For Development:** Use TEST keys + Render (or other hosting service)
2. **For Production:** Use LIVE keys + HTTPS domain (Render or other hosting)

## Quick Test Checklist

- [ ] Server deployed to Render (or running locally)
- [ ] Server URL accessible and working
- [ ] APP_URL set correctly (Render service URL)
- [ ] PayMongo webhook configured with correct URL
- [ ] Config cache cleared (`php artisan config:clear`)

