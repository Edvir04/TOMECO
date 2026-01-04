# Fix "Network request failed" Error

## The Problem
Expo Go doesn't support the `usesCleartextTraffic` setting in `app.json`. This means HTTP requests are blocked on Android.

## Solution 1: Use Android Emulator IP (Quick Test)

If you're using **Android Emulator**, the config is already set to use `10.0.2.2`:

1. **Make sure Laravel server is running**:
   ```bash
   cd tomeco_web
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Restart your app** - it should work now!

## Solution 2: Create Development Build (Required for Physical Device)

If you're using a **physical Android device** or Expo Go, you MUST create a development build:

### Step 1: Install Development Build Dependencies

```bash
cd tomeco_app
npm install expo-dev-client
```

### Step 2: Update app.json

Add the dev client plugin (if not already there):

```json
{
  "expo": {
    "plugins": [
      "expo-dev-client"
    ]
  }
}
```

### Step 3: Generate Native Code

```bash
npx expo prebuild --clean
```

### Step 4: Build and Run

```bash
# This will build and install on your device/emulator
npx expo run:android
```

### Step 5: Start Development Server

After the build completes:

```bash
npx expo start --dev-client
```

## Solution 3: Update IP for Physical Device

If you're using a **physical device** (not emulator):

1. **Find your computer's IP**:
   ```bash
   # Windows
   ipconfig
   # Look for "IPv4 Address" (e.g., 192.168.1.5)
   ```

2. **Update `config/api.js`**:
   ```javascript
   const DEV_HOST = '192.168.1.5'; // Your actual IP
   ```

3. **Make sure you have a development build** (Solution 2) - Expo Go won't work!

## Verify Server is Running

Test if your server is accessible:

1. **From your computer browser**:
   ```
   http://192.168.1.5:8000/api/mobile/health
   ```
   Should return: `{"success":true,"message":"API is accessible",...}`

2. **From Android Emulator browser**:
   ```
   http://10.0.2.2:8000/api/mobile/health
   ```

3. **From Physical Device browser** (same network):
   ```
   http://192.168.1.5:8000/api/mobile/health
   ```

## Current Configuration

- ✅ `app.json` has `usesCleartextTraffic: true` (only works in dev build)
- ✅ `config/api.js` is set to `10.0.2.2` for emulator
- ⚠️ You need a development build for this to work!

## Quick Checklist

- [ ] Laravel server running: `php artisan serve --host=0.0.0.0 --port=8000`
- [ ] Using Android Emulator? → IP should be `10.0.2.2` ✅
- [ ] Using Physical Device? → Need development build + correct IP
- [ ] Using Expo Go? → Must create development build (won't work otherwise)
- [ ] Server accessible in browser? → Test the health endpoint

## Still Not Working?

1. **Check Windows Firewall** - Allow port 8000
2. **Verify same network** - Device and computer must be on same Wi-Fi
3. **Check Laravel logs** - Look for errors in `tomeco_web/storage/logs`
4. **Try the health endpoint** - Test in browser first

