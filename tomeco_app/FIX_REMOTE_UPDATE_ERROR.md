# Fix: "failed to download remote update" Error

## The Problem

This error occurs when using a development build that tries to download OTA (Over-The-Air) updates but fails.

## Quick Solutions

### Solution 1: Switch to Expo Go Mode (Easiest) ✅

In the terminal where Expo is running, **press `s`** to switch to Expo Go mode.

This will:
- Use Expo Go app instead of development build
- Avoid the remote update issue
- Show QR code properly

### Solution 2: Use Tunnel Mode

Stop the server (Ctrl+C) and run:
```bash
cd tomeco_app
expo start --tunnel
```

### Solution 3: Disable OTA Updates in Development

Add this to your `app.json`:

```json
{
  "expo": {
    "updates": {
      "enabled": false
    }
  }
}
```

### Solution 4: Clear Cache and Restart

```bash
# Stop server (Ctrl+C)
cd tomeco_app
expo start --clear
```

### Solution 5: Rebuild Development Build

If you need to use development build:

1. **Stop the server**
2. **Rebuild the app:**
   ```bash
   expo run:android
   # or
   eas build --profile development --platform android
   ```
3. **Install the new build on your device**
4. **Start server:**
   ```bash
   expo start --dev-client
   ```

## Recommended: Use Expo Go for Development

For development, Expo Go is easier:

1. **Press `s` in terminal** to switch to Expo Go
2. **Open Expo Go app** on your phone
3. **Scan QR code** or enter URL manually: `exp://192.168.1.10:8081`

## Alternative: Use Android Emulator

If you have Android Studio:

1. **Start Android emulator**
2. **In Expo terminal, press `a`**
3. **App opens automatically** in emulator

## Why This Happens

Development builds try to fetch OTA updates by default. If:
- Network is slow/unstable
- Update server is unreachable
- Development build is outdated

You'll get this error.

## Best Practice

For development:
- Use **Expo Go** (press `s`)
- Or use **Android emulator** (press `a`)

For production/testing:
- Use development build
- Or build standalone APK

