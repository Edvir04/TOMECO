# Physical Device Setup - Node.js Server

## Current Configuration

✅ **Your IP Address**: `192.168.1.16`  
✅ **Node.js Server**: Port `3000`  
✅ **Login Endpoint**: `http://192.168.1.16:3000/api/mobile/login`

## Step 1: Start Node.js Server

```bash
cd tomeco_app
node server.js
```

You should see:
```
Server is running on port 3000
Server accessible at:
  - http://localhost:3000
  - http://127.0.0.1:3000
  - Use your local network IP for mobile devices

API Endpoints:
  - POST /api/mobile/login
  - GET  /api/mobile/profile
  - GET  /api/diagnostics
```

## Step 2: Test Server from Computer

Open your browser and test:
- **Diagnostics**: `http://192.168.1.16:3000/api/diagnostics`
- Should show database connection status

## Step 3: Test from Physical Device Browser

On your Android device, open a browser and try:
- `http://192.168.1.16:3000/api/diagnostics`

If this works, the server is accessible. If not:
- Check Windows Firewall (allow port 3000)
- Verify device and computer are on same Wi-Fi network
- Make sure server is running with `node server.js`

## Step 4: Fix Android HTTP Issue

**IMPORTANT**: Android blocks HTTP by default. You have 2 options:

### Option A: Create Development Build (Recommended)

```bash
cd tomeco_app
npm install expo-dev-client
npx expo prebuild --clean
npx expo run:android
```

Then start with:
```bash
npx expo start --dev-client
```

### Option B: Use Expo Go (Limited - May Not Work)

Expo Go doesn't fully support `usesCleartextTraffic`. You might still get network errors.

## Step 5: Verify Configuration

Check `config/api.js`:
- `DEV_HOST` should be `'192.168.1.16'`
- `LOGIN` should point to Node.js server: `http://192.168.1.16:3000/api/mobile/login`

## Troubleshooting

### Server Not Detecting Requests

1. **Check server is listening on all interfaces**:
   - Server.js should have: `app.listen(PORT, '0.0.0.0', ...)`
   - ✅ Already configured correctly!

2. **Check Windows Firewall**:
   - Allow Node.js through firewall
   - Or allow port 3000

3. **Verify IP address**:
   ```bash
   ipconfig | findstr IPv4
   ```
   - Update `DEV_HOST` in `config/api.js` if IP changed

4. **Test server directly**:
   - From computer: `http://localhost:3000/api/diagnostics`
   - From device browser: `http://192.168.1.16:3000/api/diagnostics`

### Network Request Failed Error

This means Android is blocking HTTP. You MUST create a development build (Option A above).

### Server Running But No Logs

If server.js is running but you don't see "=== Login Attempt ===" logs:
- Check the IP address is correct
- Verify device can reach the server (test in browser first)
- Make sure you're using development build, not Expo Go

## Current Status

- ✅ Server configured to listen on `0.0.0.0` (all interfaces)
- ✅ Config points to correct IP: `192.168.1.16`
- ✅ Config points to Node.js server: port `3000`
- ⚠️ Need development build for Android HTTP to work

