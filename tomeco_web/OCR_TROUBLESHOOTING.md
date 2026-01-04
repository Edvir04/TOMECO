# OCR Troubleshooting Guide

## Network Request Failed Error

If you're getting "Network request failed" when trying to use OCR, check the following:

### 1. Laravel Server is Running

Make sure your Laravel server is running:

```bash
cd tomeco_web
php artisan serve
```

The server should be running on `http://localhost:8000` or your configured port.

### 2. Check API URL Configuration

Verify the API URL in `tomeco_app/config/api.js`:

```javascript
const LARAVEL_BASE_URL = __DEV__
  ? 'http://192.168.1.5:8000'  // Change this to your computer's IP
  : 'https://your-production-domain.com';
```

**Important:** 
- If testing on a physical device, use your computer's IP address (not localhost)
- If testing on an emulator:
  - Android: Use `10.0.2.2:8000` instead of `localhost:8000`
  - iOS Simulator: Use `localhost:8000` or `127.0.0.1:8000`

### 3. Find Your Computer's IP Address

**Windows:**
```bash
ipconfig
```
Look for "IPv4 Address" under your active network adapter.

**Mac/Linux:**
```bash
ifconfig
# or
ip addr show
```

### 4. Update API Configuration

1. Open `tomeco_app/config/api.js`
2. Replace `192.168.1.5` with your actual IP address
3. Make sure the port matches (default is 8000)
4. Restart the Expo app

### 5. Check Firewall

Windows Firewall might be blocking connections:

1. Open Windows Defender Firewall
2. Allow PHP through firewall, or
3. Temporarily disable firewall for testing

### 6. Verify Network Connection

- Make sure your mobile device/emulator is on the same network as your computer
- Try accessing `http://YOUR_IP:8000/api/mobile/ocr/scan-id` from a browser on your device

### 7. Test API Endpoint Manually

Test if the endpoint is accessible:

```bash
# From your computer
curl -X POST http://localhost:8000/api/mobile/ocr/scan-id \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@test_image.jpg"
```

### 8. Check Laravel Logs

Check for errors in Laravel logs:

```bash
cd tomeco_web
tail -f storage/logs/laravel.log
```

### 9. Common Issues

**Issue: "Network request failed" on physical device**
- **Solution:** Use your computer's IP address, not localhost

**Issue: "Network request failed" on Android emulator**
- **Solution:** Use `10.0.2.2:8000` instead of `localhost:8000`

**Issue: "Network request failed" on iOS simulator**
- **Solution:** Use `localhost:8000` or `127.0.0.1:8000`

**Issue: CORS errors**
- **Solution:** CORS is already configured in `bootstrap/app.php` and `config/cors.php`

**Issue: 401 Unauthorized**
- **Solution:** Make sure you're logged in and the auth token is valid

### 10. Quick Test

1. Start Laravel server: `php artisan serve`
2. Check if server is accessible: Open `http://localhost:8000` in browser
3. Update `tomeco_app/config/api.js` with correct IP
4. Restart Expo app
5. Try OCR again

## Still Having Issues?

1. Check the console logs in Expo for detailed error messages
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify Tesseract is installed: `tesseract --version`
4. Test OCR directly: `php test_ocr.php path/to/image.jpg`

