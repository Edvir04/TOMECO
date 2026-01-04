# Network Connection Troubleshooting

## Problem: "Network request failed" on Android

This error occurs because Android blocks cleartext HTTP traffic by default. Here are the solutions:

## Solution 1: Use Development Build (Recommended)

**Expo Go doesn't support `usesCleartextTraffic` setting.** You need to create a development build:

### Steps:

1. **Install Expo CLI globally** (if not already installed):
   ```bash
   npm install -g expo-cli
   ```

2. **Create development build**:
   ```bash
   cd tomeco_app
   npx expo prebuild --clean
   npx expo run:android
   ```

   This will:
   - Generate native Android code
   - Apply the `usesCleartextTraffic: true` setting from `app.json`
   - Build and install the app on your device/emulator

3. **Start the development server**:
   ```bash
   npx expo start --dev-client
   ```

## Solution 2: Use Android Emulator with Special IP

If you're using **Android Emulator**, you need to use a special IP address:

1. **Update `config/api.js`**:
   ```javascript
   const DEV_HOST = '10.0.2.2'; // Use this for Android Emulator
   ```

   `10.0.2.2` is the emulator's alias for your host machine's localhost.

2. **For Physical Device**, use your computer's IP:
   ```javascript
   const DEV_HOST = '192.168.1.5'; // Your computer's local IP
   ```

## Solution 3: Verify Server is Running

1. **Check Laravel server is running**:
   ```bash
   cd tomeco_web
   php artisan serve --host=0.0.0.0 --port=8000
   ```
   
   The `--host=0.0.0.0` flag allows connections from other devices on your network.

2. **Test the endpoint in browser**:
   - Open: `http://192.168.1.5:8000/api/mobile/health`
   - You should see: `{"success":true,"message":"API is accessible",...}`

3. **Check Windows Firewall**:
   - Allow port 8000 through Windows Firewall
   - Or temporarily disable firewall to test

## Solution 4: Check Network Configuration

### For Physical Android Device:

1. **Find your computer's IP address**:
   ```bash
   # Windows
   ipconfig
   # Look for "IPv4 Address" under your active network adapter
   ```

2. **Update `config/api.js`** with the correct IP:
   ```javascript
   const DEV_HOST = 'YOUR_ACTUAL_IP'; // e.g., '192.168.1.5'
   ```

3. **Ensure device and computer are on the same Wi-Fi network**

### For Android Emulator:

1. **Always use `10.0.2.2`**:
   ```javascript
   const DEV_HOST = '10.0.2.2';
   ```

2. **No need to worry about network - emulator can always reach host**

## Quick Test

To quickly test if the server is accessible:

1. **From your computer**, test in browser:
   ```
   http://192.168.1.5:8000/api/mobile/health
   ```

2. **From Android device/emulator**, you can test using:
   - Browser app: `http://192.168.1.5:8000/api/mobile/health` (physical device)
   - Browser app: `http://10.0.2.2:8000/api/mobile/health` (emulator)

## Current Configuration

Your `app.json` already has:
```json
"android": {
  "usesCleartextTraffic": true
}
```

**But this only works with a development build, not Expo Go!**

## Recommended Next Steps

1. **If using Expo Go**: Switch to development build (Solution 1)
2. **If using Emulator**: Change IP to `10.0.2.2` in `config/api.js`
3. **If using Physical Device**: Verify IP address and network connection
4. **Always verify**: Server is running and accessible

## Still Having Issues?

1. Check Laravel server logs for errors
2. Verify CORS is configured correctly in `tomeco_web/config/cors.php`
3. Check that the route exists: `tomeco_web/routes/api.php`
4. Try accessing the health endpoint directly from device browser

