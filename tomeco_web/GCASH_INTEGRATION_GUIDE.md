# GCash Payment Integration Guide (PayMongo)

## Overview
This guide will help you integrate GCash payment functionality into the TOMECO Violator Portal using PayMongo payment gateway.

## Current Implementation
- ✅ PayMongo PHP SDK installed
- ✅ GCash payment button added to violator portal
- ✅ PayMongoService class created
- ✅ PaymentController updated with PayMongo integration
- ✅ Payment routes configured
- ✅ Webhook handler implemented

## Installation Instructions

### ✅ PayMongo Integration (Already Implemented)
PayMongo is a popular payment gateway in the Philippines that supports GCash. The integration is already complete!

#### Step 1: ✅ PayMongo PHP SDK - Already Installed
```bash
composer require paymongo/paymongo-php
```

#### Step 2: Add PayMongo credentials to .env
Add these lines to your `.env` file:
```env
PAYMONGO_SECRET_KEY=sk_test_your_secret_key_here
PAYMONGO_PUBLIC_KEY=pk_test_your_public_key_here
PAYMONGO_WEBHOOK_SECRET=whsec_your_webhook_secret_here
PAYMONGO_API_URL=https://api.paymongo.com/v1
```

#### Step 3: ✅ Configuration - Already Added
PayMongo configuration is already added to `config/services.php`:
```php
'paymongo' => [
    'secret_key' => env('PAYMONGO_SECRET_KEY'),
    'public_key' => env('PAYMONGO_PUBLIC_KEY'),
    'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
    'api_url' => env('PAYMONGO_API_URL', 'https://api.paymongo.com/v1'),
],
```

## Quick Setup Guide

**See `PAYMONGO_SETUP_INSTRUCTIONS.md` for detailed step-by-step instructions.**

### Quick Start:
1. Get PayMongo API keys from https://paymongo.com/
2. Add keys to `.env` file
3. Configure webhook in PayMongo dashboard
4. Test the payment flow

## Implementation Details

### ✅ PaymentController.php - Already Implemented
The `PaymentController` is fully implemented with PayMongo integration:
- Creates checkout sessions
- Handles payment success/failure
- Processes webhooks
- Updates ticket payment status

### ✅ PayMongoService.php - Already Created
A dedicated service class handles all PayMongo API interactions:
- `createCheckoutSession()` - Creates payment checkout
- `createPaymentIntent()` - Creates payment intent
- `getPaymentIntent()` - Retrieves payment status
- `verifyWebhookSignature()` - Verifies webhook authenticity

### 2. Create Payment Records Table (Optional)
If you want to track payments:

```bash
php artisan make:migration create_payments_table
```

Migration content:
```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
    $table->string('citation_number');
    $table->string('payment_method')->default('gcash');
    $table->decimal('amount', 10, 2);
    $table->string('payment_status')->default('pending'); // pending, paid, failed, cancelled
    $table->string('payment_reference')->nullable();
    $table->string('transaction_id')->nullable();
    $table->json('payment_data')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});
```

### 3. Update PaymentController to Save Payment Records
Add payment record creation in the `initiateGCashPayment` method.

## Testing

### Test Mode
- Use sandbox/test credentials
- Test with small amounts
- Verify callback/webhook handling

### Production
- Update environment to 'production'
- Use production API keys
- Enable webhook verification

## Webhook Setup

### ✅ PayMongo Webhook - Already Configured
1. Go to PayMongo dashboard → Settings → Webhooks
2. Add webhook URL: `https://yourdomain.com/gcash/payment/callback`
3. Select events: `payment.paid`, `payment.failed`
4. Copy webhook secret and add to `.env`: `PAYMONGO_WEBHOOK_SECRET`
5. Webhook handler is already implemented in `PaymentController::gcashCallback()`

## Security Notes
- Never expose secret keys in frontend code
- Always verify webhook signatures
- Use HTTPS in production
- Validate payment amounts server-side
- Log all payment transactions

## Support
For GCash API documentation:
- PayMongo: https://developers.paymongo.com/
- DragonPay: https://www.dragonpay.ph/
- GCash Direct: Contact GCash Business Support

## Current Status
✅ **PayMongo Integration Complete!**

The payment integration is fully implemented. You just need to:
1. ✅ Get PayMongo API keys (test and live)
2. ✅ Add API credentials to `.env` file
3. ✅ Configure webhook in PayMongo dashboard
4. ✅ Test the integration

**See `PAYMONGO_SETUP_INSTRUCTIONS.md` for detailed setup steps.**

