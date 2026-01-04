# Quick Fix: Can't See QR Code

## Your Current Situation

You're running in **development build mode**, which might not show the QR code properly.

## Quick Solutions

### Solution 1: Switch to Expo Go (Easiest) ✅

In the terminal where Expo is running, **press `s`** to switch to Expo Go mode.

Then you should see:
- QR code displayed in terminal
- Option to scan with Expo Go app

### Solution 2: Use Tunnel Mode

Stop the current server (Ctrl+C), then run:
```bash
cd tomeco_app
expo start --tunnel
```

This will:
- Show QR code clearly
- Work even on different networks
- Be more reliable

### Solution 3: Manual URL Entry

From your terminal output, the URL is:
```
exp+tomecoapp://expo-development-client/?url=http%3A%2F%2F192.168.1.10%3A8081
```

**In Expo Go app:**
1. Open Expo Go
2. Tap "Enter URL manually"
3. Enter: `exp://192.168.1.10:8081`
4. Connect

### Solution 4: Use Development Build (If Installed)

If you have a development build installed on your device:
1. Make sure device and computer are on same WiFi
2. The app should automatically connect
3. Or open the development build app manually

## Recommended Steps

1. **Press `s` in the terminal** to switch to Expo Go mode
2. **Look for QR code** in terminal
3. **Scan with Expo Go app**

If QR code still doesn't show:
- Try tunnel mode: `expo start --tunnel`
- Or manually enter URL: `exp://192.168.1.10:8081`

## Alternative: Use Android Emulator

If you have Android Studio installed:
1. Start Android emulator
2. In Expo terminal, **press `a`**
3. App will open in emulator automatically

## Still Not Working?

Try these commands:

```bash
# Stop current server (Ctrl+C)

# Clear cache and start with tunnel:
cd tomeco_app
expo start --clear --tunnel

# Or start with LAN:
expo start --clear --lan
```

