# PayMongo Integration Setup Instructions

## Overview
This guide will help you set up PayMongo payment gateway for GCash payments in the TOMECO Violator Portal.

## ✅ What's Already Done
- ✅ PayMongo PHP SDK installed
- ✅ PaymentController updated with PayMongo integration
- ✅ PayMongoService class created
- ✅ Routes configured
- ✅ Payment views created

## 📋 Setup Steps

### Step 1: Get PayMongo API Keys

1. **Sign up for PayMongo Account**
   - Go to https://paymongo.com/
   - Click "Sign Up" and create an account
   - Complete the verification process

2. **Get Your API Keys**
   - Log in to your PayMongo dashboard
   - Go to **Settings** → **API Keys**
   - You'll see two keys:
     - **Public Key** (starts with `pk_test_` or `pk_live_`)
     - **Secret Key** (starts with `sk_test_` or `sk_live_`)

3. **Test vs Live Keys**
   - **Test keys** (starts with `test_`): Use for development/testing
   - **Live keys** (starts with `live_`): Use for production

### Step 2: Configure Environment Variables

1. **Open your `.env` file** in the project root

2. **Add the following lines:**
   ```env
   # PayMongo Configuration
   PAYMONGO_SECRET_KEY=sk_test_your_secret_key_here
   PAYMONGO_PUBLIC_KEY=pk_test_your_public_key_here
   PAYMONGO_WEBHOOK_SECRET=whsec_your_webhook_secret_here
   PAYMONGO_API_URL=https://api.paymongo.com/v1
   ```

3. **Replace the placeholder values:**
   - Replace `sk_test_your_secret_key_here` with your actual secret key
   - Replace `pk_test_your_public_key_here` with your actual public key
   - The webhook secret will be set up in Step 4

### Step 3: Test the Integration

1. **Start your Laravel server:**
   ```bash
   php artisan serve
   ```

2. **Test the payment flow:**
   - Go to `/violator-portal`
   - Search for a ticket using citation number
   - Click "Pay via GCash" button
   - You should be redirected to PayMongo checkout page

3. **Test Payment (using test mode):**
   - Use PayMongo test card numbers:
     - **Card Number:** `4242 4242 4242 4242`
     - **Expiry:** Any future date (e.g., `12/25`)
     - **CVC:** Any 3 digits (e.g., `123`)
   - Or use GCash test account if available

### Step 4: Set Up Webhooks (Important!)

Webhooks allow PayMongo to notify your application when payments are completed.

1. **Get Your Webhook URL:**
   - Your webhook URL should be: `https://yourdomain.com/gcash/payment/callback`
   - For production, use your Render service URL: `https://your-service.onrender.com/gcash/payment/callback`
   - For local testing, webhooks require a public URL (use Render or another hosting service)

2. **Configure Webhook in PayMongo:**
   - Go to PayMongo Dashboard → **Settings** → **Webhooks**
   - Click **Add Webhook**
   - Enter your webhook URL
   - Select events to listen to:
     - ✅ `payment.paid`
     - ✅ `payment.failed`
   - Click **Save**

3. **Get Webhook Secret:**
   - After creating the webhook, PayMongo will show a **Webhook Secret**
   - Copy this secret (starts with `whsec_`)
   - Add it to your `.env` file:
     ```env
     PAYMONGO_WEBHOOK_SECRET=whsec_your_webhook_secret_here
     ```

4. **Test Webhook:**
   - PayMongo dashboard allows you to send test webhook events
   - Use this to verify your webhook is working

### Step 5: Production Setup

1. **Switch to Live Keys:**
   - In PayMongo dashboard, generate **Live API Keys**
   - Update your `.env` file with live keys:
     ```env
     PAYMONGO_SECRET_KEY=sk_live_your_live_secret_key
     PAYMONGO_PUBLIC_KEY=pk_live_your_live_public_key
     ```

2. **Update Webhook URL:**
   - Create a new webhook in PayMongo with your production URL
   - Update `PAYMONGO_WEBHOOK_SECRET` in `.env`

3. **Verify SSL Certificate:**
   - Ensure your production site has a valid SSL certificate
   - PayMongo requires HTTPS for webhooks

## 🔧 Troubleshooting

### Issue: "Payment initiation failed"
**Solution:**
- Check if API keys are correctly set in `.env`
- Verify keys are active in PayMongo dashboard
- Check Laravel logs: `storage/logs/laravel.log`

### Issue: "Webhook not receiving events"
**Solution:**
- Verify webhook URL is accessible (use Render or other hosting service)
- Check webhook secret matches in `.env`
- Ensure webhook is enabled in PayMongo dashboard
- Check server logs for webhook requests
- **Note:** Webhooks won't work on localhost without a public URL

### Issue: "Payment successful but ticket not updated"
**Solution:**
- Verify webhook is properly configured
- Check `handlePaymentPaid()` method in `PaymentController`
- Add payment status field to tickets table if needed

## 📝 Additional Configuration

### Customize Payment Amount

Currently, the payment amount is hardcoded to ₱500.00. To customize:

1. **Add amount field to tickets table (optional):**
   ```bash
   php artisan make:migration add_fine_amount_to_tickets_table
   ```

2. **Update PaymentController:**
   ```php
   // In initiateGCashPayment method, replace:
   $amount = 500.00;
   
   // With:
   $amount = $ticket->fine_amount ?? 500.00;
   ```

### Store Payment Records

To track payments, create a payments table:

```bash
php artisan make:migration create_payments_table
```

Migration example:
```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
    $table->string('citation_number');
    $table->string('payment_method')->default('gcash');
    $table->decimal('amount', 10, 2);
    $table->string('payment_status')->default('pending');
    $table->string('paymongo_payment_intent_id')->nullable();
    $table->string('paymongo_checkout_session_id')->nullable();
    $table->json('payment_data')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});
```

## 📚 Resources

- **PayMongo Documentation:** https://developers.paymongo.com/
- **PayMongo API Reference:** https://developers.paymongo.com/reference
- **PayMongo Dashboard:** https://dashboard.paymongo.com/

## ✅ Checklist

- [ ] PayMongo account created
- [ ] API keys obtained (test and live)
- [ ] `.env` file updated with API keys
- [ ] Payment flow tested in test mode
- [ ] Webhook configured
- [ ] Webhook secret added to `.env`
- [ ] Webhook tested with test events
- [ ] Production keys configured (when ready)
- [ ] Production webhook configured (when ready)

## 🎉 You're All Set!

Once you've completed these steps, the GCash payment integration via PayMongo should be fully functional. Users can now pay their traffic violation tickets using GCash through the violator portal.

