# TOMECO App Deployment Guide

This guide will help you deploy the TOMECO mobile app using your ngrok domains.

## Prerequisites

1. ✅ ngrok Pro account with static domains configured
2. ✅ Backend servers running (Laravel on port 8000, Node.js on port 3000 if used)
3. ✅ ngrok tunnels active (Tomeco.ngrok.dev and Tomeco-Violator.ngrok.dev)
4. ✅ Expo CLI installed
5. ✅ EAS CLI installed (for building production apps)

## Current Configuration

Your ngrok domains are configured as:
- **Admin Server**: `https://Tomeco.ngrok.dev` (port 8000)
- **Violator Server**: `https://Tomeco-Violator.ngrok.dev` (port 8001)
- **Mobile App API**: Uses `https://Tomeco.ngrok.dev` for Laravel endpoints

## Step 1: Verify Backend is Running

### Start Your Backend Servers

1. **Start Admin Server** (port 8000):
   ```bash
   cd tomeco_web
   set APP_PORTAL_TYPE=admin
   php artisan serve --port=8000
   ```

2. **Start Violator Server** (port 8001) - Optional for mobile app:
   ```bash
   cd tomeco_web
   set APP_PORTAL_TYPE=violator
   php artisan serve --port=8001
   ```

3. **Start Node.js Server** (port 3000) - If using OCR features:
   ```bash
   cd tomeco_app
   node server.js
   ```

### Start ngrok Tunnels

1. **Start ngrok for both servers**:
   ```bash
   cd tomeco_web
   start-ngrok-both.bat
   ```

   Or start individually:
   ```bash
   # Admin tunnel
   start-ngrok-admin.bat
   
   # Violator tunnel (in another terminal)
   start-ngrok-violator.bat
   ```

2. **Verify tunnels are active**:
   - Visit `http://127.0.0.1:4040` to see ngrok web interface
   - Check that both domains are active:
     - `https://Tomeco.ngrok.dev`
     - `https://Tomeco-Violator.ngrok.dev`

## Step 2: Test API Endpoints

Before building the app, test that your API endpoints are accessible:

```bash
# Test Laravel health endpoint
curl https://Tomeco.ngrok.dev/api/mobile/health

# Expected response:
# {"success":true,"message":"API is accessible","timestamp":"..."}
```

## Step 3: Update API Configuration (Already Done ✅)

The `config/api.js` file has been updated to use your ngrok domains:
- Production Laravel URL: `https://Tomeco.ngrok.dev`
- Production API URL: `https://Tomeco.ngrok.dev/api`

## Step 4: Build the App

### Option A: Using EAS Build (Recommended for Production)

1. **Install EAS CLI** (if not already installed):
   ```bash
   npm install -g eas-cli
   ```

2. **Login to Expo**:
   ```bash
   eas login
   ```

3. **Configure EAS Build**:
   ```bash
   cd tomeco_app
   eas build:configure
   ```

4. **Create/Update `eas.json`**:
   ```json
   {
     "build": {
       "development": {
         "developmentClient": true,
         "distribution": "internal"
       },
       "preview": {
         "distribution": "internal",
         "android": {
           "buildType": "apk"
         }
       },
       "production": {
         "android": {
           "buildType": "apk"
         }
       }
     }
   }
   ```

5. **Build for Android**:
   ```bash
   # Preview build (for testing)
   eas build --platform android --profile preview
   
   # Production build
   eas build --platform android --profile production
   ```

6. **Download and Install**:
   - EAS will provide a download link
   - Download the APK
   - Install on your Android device

### Option B: Local Build (Alternative)

1. **Install dependencies**:
   ```bash
   cd tomeco_app
   npm install
   ```

2. **Build APK locally**:
   ```bash
   expo build:android
   ```

   Note: This requires Android SDK and may take longer.

## Step 5: Test the Deployed App

1. **Install the APK** on your Android device
2. **Test Login**: Verify you can log in with enforcer credentials
3. **Test API Connection**: 
   - Create a ticket
   - Check ticket history
   - Verify profile loads
4. **Test Offline Features**:
   - Turn off WiFi/data
   - Verify offline indicator shows
   - Create ticket offline
   - Turn on connection
   - Verify sync works

## Step 6: Update CORS Settings (If Needed)

If you encounter CORS errors, update your Laravel CORS configuration:

**File: `tomeco_web/config/cors.php`**

```php
'allowed_origins' => [
    'https://Tomeco.ngrok.dev',
    'https://Tomeco-Violator.ngrok.dev',
    // Add your app's origin if needed
],
```

## Important Notes

### ngrok Considerations

1. **Keep ngrok Running**: 
   - ngrok must stay running while the app is in use
   - If ngrok stops, the app won't be able to connect to the backend

2. **Static Domains**: 
   - With ngrok Pro, your domains won't change
   - URLs are: `https://Tomeco.ngrok.dev` and `https://Tomeco-Violator.ngrok.dev`

3. **For Production**: 
   - Consider deploying to a real server instead of using ngrok
   - ngrok is great for development/testing but has limitations for production

### Node.js Server (OCR)

If you're using the OCR features that require the Node.js server:

1. **Set up ngrok for Node.js** (port 3000):
   ```bash
   ngrok http 3000 --domain=your-nodejs-domain.ngrok.dev
   ```

2. **Update `config/api.js`** to use the Node.js ngrok domain:
   ```javascript
   const API_BASE_URL = __DEV__ 
     ? `http://${DEV_HOST}:3000/api`
     : 'https://your-nodejs-domain.ngrok.dev/api';
   ```

## Troubleshooting

### "Network request failed"
- Check ngrok tunnels are running
- Verify domains are correct in `config/api.js`
- Test API endpoints with curl

### "CORS error"
- Update Laravel CORS configuration
- Ensure ngrok domains are in allowed origins

### "Cannot connect to backend"
- Verify backend servers are running
- Check ngrok tunnels are active
- Test endpoints manually with curl

### "Login fails"
- Verify API endpoint: `https://Tomeco.ngrok.dev/api/mobile/login`
- Check backend logs for errors
- Ensure database is accessible

## Next Steps

1. ✅ API configuration updated
2. ⏭️ Test API endpoints
3. ⏭️ Build the app
4. ⏭️ Test on device
5. ⏭️ Deploy to production server (optional)

## Production Deployment (Future)

For production, consider:
1. Deploy Laravel backend to a real server (AWS, DigitalOcean, etc.)
2. Use a real domain with SSL certificate
3. Update `config/api.js` with production URLs
4. Rebuild the app with production URLs

## Support

If you encounter issues:
1. Check ngrok web interface: `http://127.0.0.1:4040`
2. Review backend logs
3. Test API endpoints manually
4. Check app console logs

