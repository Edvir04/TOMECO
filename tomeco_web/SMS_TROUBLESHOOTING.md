# SMS Troubleshooting Guide

## Quick Diagnosis

Run the diagnostic script:
```bash
php test_sms.php
```

This will check:
- ✅ Environment variables are set
- ✅ SMS service is working
- ✅ Recent tickets have contact numbers
- ✅ Recent SMS log entries

## Common Issues

### 1. Missing API Credentials

**Symptom:** Log shows "IPROG SMS: API token not configured"

**Solution:** Add to `.env` file:
```env
IPROG_SMS_API_TOKEN=your_api_token_here
IPROG_SMS_API_URL=https://www.iprogsms.com/api/v1/sms_messages
```

Then restart Laravel:
```bash
php artisan config:clear
php artisan cache:clear
```

### 2. Missing Driver Contact Number

**Symptom:** Log shows "No driver contact number provided for ticket #..."

**Solution:** 
- Ensure the ticket form includes `driver_contact` field
- Verify the field is being sent in the API request
- Check that the field is not empty when creating tickets

### 3. Invalid Phone Number Format

**Symptom:** Log shows "Invalid phone number provided"

**Solution:** 
- Phone numbers should be in Philippine format: `09XXXXXXXXX` or `+639XXXXXXXXX`
- The service normalizes to `63XXXXXXXXXX` format
- Ensure the number has at least 10 digits after country code

### 4. API Connection Issues

**Symptom:** Log shows "Connection error" or timeout

**Solution:**
- Check if IPROG SMS API is accessible from your server
- Verify firewall/network settings
- Check if API URL is correct
- Test API connectivity: `curl https://www.iprogsms.com/api/v1/sms_messages`

### 5. API Response Errors

**Symptom:** Log shows API response with error status

**Solution:**
- Check API response in logs for specific error messages
- Verify API token is valid and has credits
- Check IPROG SMS account status
- Review API documentation for required parameters

## Checking Logs

### View Recent SMS Logs
```bash
tail -f storage/logs/laravel.log | grep "IPROG SMS"
```

### View All Ticket Creation Logs
```bash
tail -f storage/logs/laravel.log | grep "Ticket created"
```

### View SMS Errors Only
```bash
tail -f storage/logs/laravel.log | grep -E "IPROG SMS.*(error|failed|exception)" -i
```

## Testing SMS Manually

### Option 1: Use Tinker
```bash
php artisan tinker
```

Then:
```php
$ticket = App\Models\Ticket::find(1); // Replace 1 with ticket ID
$smsService = new App\Services\IprogSmsService();
$result = $smsService->sendTicketCreatedNotification($ticket);
var_dump($result); // Should return true if successful
```

### Option 2: Use Test Script
```bash
php test_sms.php
```

## Debugging Steps

1. **Check if SMS is being called:**
   ```bash
   grep "Attempting to send notification" storage/logs/laravel.log | tail -5
   ```

2. **Check if driver contact exists:**
   ```bash
   php artisan tinker
   ```
   ```php
   $ticket = App\Models\Ticket::latest()->first();
   echo "Contact: " . ($ticket->driver_contact ?? 'NULL') . "\n";
   ```

3. **Check API credentials:**
   ```bash
   php artisan tinker
   ```
   ```php
   echo "Token: " . (env('IPROG_SMS_API_TOKEN') ? 'SET' : 'NOT SET') . "\n";
   echo "URL: " . env('IPROG_SMS_API_URL', 'DEFAULT') . "\n";
   ```

4. **Test API directly:**
   ```bash
   curl -X POST https://www.iprogsms.com/api/v1/sms_messages \
     -d "api_token=YOUR_TOKEN" \
     -d "phone_number=639123456789" \
     -d "message=Test message"
   ```

## Expected Log Flow

When SMS works correctly, you should see:
```
[timestamp] IPROG SMS: Attempting to send notification for ticket #TOMECO-2025-0001
[timestamp] IPROG SMS: Sending message
[timestamp] IPROG SMS: API Response
[timestamp] IPROG SMS: Message sent successfully
[timestamp] SMS notification sent successfully for ticket #TOMECO-2025-0001
```

When SMS fails, you'll see:
```
[timestamp] IPROG SMS: Attempting to send notification for ticket #TOMECO-2025-0001
[timestamp] IPROG SMS: [ERROR MESSAGE]
[timestamp] SMS notification failed for ticket #TOMECO-2025-0001
```

## Contact Information

If issues persist:
1. Check IPROG SMS account dashboard
2. Verify API token is active
3. Check account balance/credits
4. Review IPROG SMS API documentation
