# Deploy TOMECO App with Expo Go - Step-by-Step CMD Instructions

## ⚠️ Important Note About Expo Go

**Expo Go has limitations:**
- Some native modules may not work in Expo Go
- You need a **development build** for full functionality
- For production, use EAS Build instead

However, you can test basic functionality with Expo Go.

## Prerequisites

1. ✅ Node.js installed
2. ✅ Expo CLI installed globally
3. ✅ Expo Go app installed on your phone
4. ✅ Backend servers ready
5. ✅ ngrok Pro account with domains configured

---

## Step 1: Start Backend Servers

### Open CMD Window 1 - Admin Server

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
set APP_PORTAL_TYPE=admin
php artisan serve --port=8000
```

**Keep this window open!**

### Open CMD Window 2 - Violator Server (Optional)

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
set APP_PORTAL_TYPE=violator
php artisan serve --port=8001
```

**Keep this window open!**

### Open CMD Window 3 - Node.js Server (If using OCR)

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
node server.js
```

**Keep this window open!**

---

## Step 2: Start ngrok Tunnels

### Open CMD Window 4 - Start ngrok

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-ngrok-both.bat
```

**Or start individually:**

```cmd
REM Admin tunnel
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-ngrok-admin.bat
```

```cmd
REM Violator tunnel (in another window)
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-ngrok-violator.bat
```

**Verify ngrok is running:**
- Visit: `http://127.0.0.1:4040`
- Check that both domains are active:
  - `https://Tomeco.ngrok.dev`
  - `https://Tomeco-Violator.ngrok.dev`

---

## Step 3: Test API Endpoints

### Open CMD Window 5 - Test API

```cmd
REM Test Laravel health endpoint
curl https://Tomeco.ngrok.dev/api/mobile/health

REM Expected response:
REM {"success":true,"message":"API is accessible","timestamp":"..."}
```

If you get a response, your backend is accessible! ✅

---

## Step 4: Update API Configuration for Expo Go

Since Expo Go runs in development mode, we need to update the API config to use ngrok domains.

### Option A: Use ngrok domains in development (Recommended for Expo Go)

Edit `tomeco_app/config/api.js`:

```javascript
// For Expo Go testing with ngrok
const USE_NGROK = true; // Set to true to use ngrok domains
const NGROK_DOMAIN = 'Tomeco.ngrok.dev';

// Node.js Server URL
const API_BASE_URL = (__DEV__ && !USE_NGROK)
  ? `http://${DEV_HOST}:3000/api`  // Local development
  : `https://${NGROK_DOMAIN}/api`; // Use ngrok domain

// Laravel Backend URL
const LARAVEL_BASE_URL = (__DEV__ && !USE_NGROK)
  ? `http://${DEV_HOST}:8000`  // Local development
  : `https://${NGROK_DOMAIN}`; // Use ngrok domain
```

### Option B: Force production mode (Alternative)

Or simply use ngrok domains always:

```javascript
const API_BASE_URL = 'https://Tomeco.ngrok.dev/api';
const LARAVEL_BASE_URL = 'https://Tomeco.ngrok.dev';
```

---

## Step 5: Install Dependencies

### Open CMD Window 6 - Install App Dependencies

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
npm install
```

Wait for installation to complete.

---

## Step 6: Start Expo Development Server

### In the same CMD window (Window 6):

```cmd
npx expo start
```

**Or:**

```cmd
npm start
```

**You should see:**
```
› Metro waiting on exp://192.168.x.x:8081
› Scan the QR code above with Expo Go (Android) or the Camera app (iOS)
```

---

## Step 7: Connect with Expo Go

### On Your Phone:

1. **Open Expo Go app** (download from Play Store/App Store if needed)

2. **Scan the QR code** displayed in CMD:
   - **Android**: Use Expo Go app's scanner
   - **iOS**: Use Camera app (will open Expo Go)

3. **Wait for app to load** (may take a minute on first load)

---

## Step 8: Test the App

1. **Test Login**: Try logging in with enforcer credentials
2. **Test API Connection**: Check if tickets load
3. **Test Network**: Verify offline/online detection works

---

## Troubleshooting

### Issue: "Network request failed"

**Solution:**
1. Check ngrok is running: `http://127.0.0.1:4040`
2. Test API: `curl https://Tomeco.ngrok.dev/api/mobile/health`
3. Verify API config uses `https://Tomeco.ngrok.dev`
4. Make sure backend server is running on port 8000

### Issue: "Cannot connect to Expo"

**Solution:**
1. Make sure phone and computer are on same WiFi network
2. Check firewall isn't blocking port 8081
3. Try using tunnel mode: `npx expo start --tunnel`

### Issue: "Expo Go can't load app"

**Solution:**
1. Some native modules don't work in Expo Go
2. You may need a development build: `npx expo run:android`
3. Or use EAS Build for production

### Issue: "QR code not scanning"

**Solution:**
1. Use tunnel mode: `npx expo start --tunnel`
2. Or manually enter URL in Expo Go app

---

## Quick Reference Commands

### Start Everything (in separate CMD windows):

```cmd
REM Window 1: Admin Server
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
set APP_PORTAL_TYPE=admin
php artisan serve --port=8000

REM Window 2: ngrok
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-ngrok-both.bat

REM Window 3: Expo
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
npm start
```

### Stop Everything:

```cmd
REM Stop ngrok
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
stop-ngrok.bat

REM Stop servers: Press Ctrl+C in each CMD window
```

---

## Alternative: Use Tunnel Mode (If Same Network Issues)

If you have network issues, use Expo's tunnel:

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
npx expo start --tunnel
```

This uses Expo's servers to tunnel the connection (slower but more reliable).

---

## Next Steps

1. ✅ Test with Expo Go (basic functionality)
2. ⏭️ Create development build for full features: `npx expo run:android`
3. ⏭️ Build production APK: `eas build --platform android`

---

## Important Notes

- **Keep all CMD windows open** while testing
- **ngrok must stay running** for API to work
- **Backend servers must stay running**
- **Expo Go has limitations** - consider development build for full features

