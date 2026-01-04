# Mobile App Access Setup

## Problem
By default, `php artisan serve` binds to `127.0.0.1` (localhost only), which means it's only accessible from the same computer. Mobile devices on the same network cannot connect to it.

## Solution
Start the server bound to `0.0.0.0` (all network interfaces) so it's accessible from other devices.

## Quick Start

### Option 1: Use the Mobile-Access Batch File
```bash
cd tomeco_web
start-admin-server-mobile.bat
```

### Option 2: Manual Command
```bash
cd tomeco_web
set APP_PORTAL_TYPE=admin
php artisan serve --host=0.0.0.0 --port=8000
```

## Verify Server is Accessible

1. **Find your computer's IP address:**
   ```bash
   ipconfig
   ```
   Look for "IPv4 Address" (e.g., `192.168.1.5`)

2. **Test from browser on your computer:**
   - `http://localhost:8000/api/mobile/health`
   - Should return: `{"success":true,"message":"API is accessible",...}`

3. **Test from browser on your mobile device (same network):**
   - `http://YOUR_IP:8000/api/mobile/health`
   - Example: `http://192.168.1.5:8000/api/mobile/health`
   - Should return the same JSON response

4. **Update mobile app config:**
   - Open `tomeco_app/config/api.js`
   - Update `LARAVEL_BASE_URL` with your actual IP:
   ```javascript
   const LARAVEL_BASE_URL = __DEV__
     ? 'http://192.168.1.5:8000'  // Replace with your IP
     : 'https://your-production-domain.com';
   ```

## Security Note

⚠️ **Warning:** Binding to `0.0.0.0` makes your server accessible to any device on your local network. This is fine for development, but:
- Only use this on trusted networks
- Don't use this in production without proper security measures
- Consider using a firewall to restrict access if needed

## Troubleshooting

### Still can't access from mobile device?

1. **Check Windows Firewall:**
   - Windows might be blocking port 8000
   - Go to Windows Defender Firewall → Allow an app
   - Allow PHP or add port 8000 exception

2. **Verify server is running:**
   ```bash
   netstat -ano | findstr :8000
   ```
   Should show the server is listening

3. **Check IP address:**
   - Make sure you're using the correct IP
   - Both devices must be on the same network (same WiFi)

4. **Test with curl:**
   ```bash
   curl http://YOUR_IP:8000/api/mobile/health
   ```

### Android Emulator Special Case

If using Android emulator, use:
- `http://10.0.2.2:8000` instead of your IP address
- This is a special IP that the emulator uses to access the host machine

### iOS Simulator

iOS Simulator can use:
- `http://localhost:8000` or
- `http://127.0.0.1:8000`

