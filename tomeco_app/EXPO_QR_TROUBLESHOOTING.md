# Expo QR Code Troubleshooting Guide

## Common Issues and Solutions

### Issue 1: QR Code Not Showing

**Solution:**
1. Make sure you're running:
   ```bash
   npm start
   # or
   expo start
   ```

2. Try with tunnel mode:
   ```bash
   expo start --tunnel
   ```

3. Try with LAN mode:
   ```bash
   expo start --lan
   ```

### Issue 2: Can't Scan QR Code

**Solutions:**

#### Option A: Use Tunnel Mode (Recommended)
```bash
expo start --tunnel
```
This creates a tunnel that works even if devices are on different networks.

#### Option B: Use LAN Mode
```bash
expo start --lan
```
This uses your local network IP address.

#### Option C: Manual Connection
1. Check the terminal output for the connection URL
2. Look for something like: `exp://192.168.1.16:8081`
3. In Expo Go app, tap "Enter URL manually"
4. Enter the URL from terminal

### Issue 3: Network Connection Issues

**Check:**
1. **Same WiFi Network:**
   - Computer and phone must be on the same WiFi network
   - Check WiFi name matches

2. **Firewall:**
   - Windows Firewall might be blocking Expo
   - Allow Expo/Node.js through firewall
   - Or temporarily disable firewall to test

3. **Antivirus:**
   - Some antivirus software blocks connections
   - Add exception for Expo/Node.js

### Issue 4: Port Already in Use

**Solution:**
```bash
# Kill process on port 8081 (default Expo port)
# Windows:
netstat -ano | findstr :8081
taskkill /PID <PID> /F

# Then restart:
expo start
```

### Issue 5: Expo Go App Issues

**Solutions:**
1. **Update Expo Go:**
   - Make sure you have the latest Expo Go app
   - Update from App Store/Play Store

2. **Clear Expo Go Cache:**
   - In Expo Go app: Settings > Clear Cache
   - Or reinstall Expo Go

3. **Check Expo SDK Version:**
   - Make sure Expo Go version matches your project SDK
   - Check `app.json` for SDK version

### Issue 6: Development Build vs Expo Go

**If using Development Build:**
- QR code won't work with Expo Go
- You need to build and install the app
- Or use `expo start --dev-client`

## Quick Fixes

### Method 1: Tunnel Mode (Easiest)
```bash
cd tomeco_app
expo start --tunnel
```
- Works on any network
- Slower but most reliable
- QR code will work

### Method 2: LAN Mode
```bash
cd tomeco_app
expo start --lan
```
- Faster than tunnel
- Requires same WiFi network
- Check terminal for IP address

### Method 3: Manual URL Entry
1. Start Expo:
   ```bash
   expo start
   ```
2. Look for URL in terminal (e.g., `exp://192.168.1.16:8081`)
3. In Expo Go app:
   - Tap "Enter URL manually"
   - Type the URL
   - Connect

### Method 4: Use Development Build
If you have a development build installed:
```bash
expo start --dev-client
```

## Step-by-Step Troubleshooting

### Step 1: Check Expo CLI
```bash
npx expo --version
# Should show version like 0.x.x
```

### Step 2: Clear Cache
```bash
expo start --clear
```

### Step 3: Check Network
```bash
# Windows - Check your IP:
ipconfig
# Look for IPv4 Address (e.g., 192.168.1.16)

# Make sure phone and computer are on same WiFi
```

### Step 4: Try Different Modes
```bash
# Try tunnel (works anywhere):
expo start --tunnel

# Try LAN (same network):
expo start --lan

# Try localhost (emulator only):
expo start --localhost
```

### Step 5: Check Firewall
- Windows: Allow Node.js through firewall
- Or temporarily disable firewall to test

### Step 6: Check Expo Go App
- Update to latest version
- Clear cache in app
- Try scanning QR code again

## Alternative: Use Physical Device with USB

### Android:
```bash
# Enable USB debugging on phone
# Connect via USB
expo start
# Press 'a' to open on Android device
```

### iOS:
```bash
# Connect iPhone via USB
# Make sure iTunes/Apple Configurator recognizes device
expo start
# Press 'i' to open on iOS device
```

## Alternative: Use Emulator

### Android Emulator:
```bash
expo start
# Press 'a' to open in Android emulator
```

### iOS Simulator (Mac only):
```bash
expo start
# Press 'i' to open in iOS simulator
```

## Still Not Working?

1. **Check Expo Status:**
   ```bash
   npx expo-doctor
   ```

2. **Reinstall Dependencies:**
   ```bash
   rm -rf node_modules
   npm install
   ```

3. **Update Expo CLI:**
   ```bash
   npm install -g expo-cli@latest
   ```

4. **Check for Errors:**
   - Look at terminal output for error messages
   - Check Expo Go app for error messages
   - Check network connectivity

## Quick Commands Reference

```bash
# Start with tunnel (most reliable):
expo start --tunnel

# Start with LAN (faster, same network):
expo start --lan

# Start and clear cache:
expo start --clear

# Start with dev client:
expo start --dev-client

# Check Expo version:
npx expo --version

# Run doctor:
npx expo-doctor
```

## Common Error Messages

### "Unable to resolve module"
- Solution: Run `npm install` or `expo install`

### "Network request failed"
- Solution: Use `--tunnel` mode or check firewall

### "Port 8081 already in use"
- Solution: Kill process on port 8081 or use different port

### "Expo Go version mismatch"
- Solution: Update Expo Go app or match SDK version

