# Deploying Mobile App with Localhost Backend

## ⚠️ Important: Localhost Won't Work for Deployed Apps

When you deploy the mobile app and install it on a device, **`localhost` refers to the device itself**, not your development machine. The app won't be able to reach your local Laravel backend.

## Solutions

### Option 1: Use ngrok (Recommended for Testing) ✅

**ngrok** creates a secure tunnel to your localhost, making it accessible from anywhere.

#### Setup Steps:

1. **Install ngrok:**
   ```bash
   # Download from https://ngrok.com/download
   # Or use npm:
   npm install -g ngrok
   ```

2. **Start your Laravel backend:**
   ```bash
   cd tomeco_web
   php artisan serve
   # Server runs on http://localhost:8000
   ```

3. **Create ngrok tunnel:**
   ```bash
   ngrok http 8000
   ```

4. **Copy the HTTPS URL:**
   ```
   Forwarding: https://abc123.ngrok.io -> http://localhost:8000
   ```

5. **Update `config/api.js`:**
   ```javascript
   const PROD_LARAVEL_BASE_URL = 'https://abc123.ngrok.io'; // Your ngrok URL
   ```

6. **For Node.js server (if using):**
   ```bash
   # In another terminal
   ngrok http 3000
   # Update API_BASE_URL with the ngrok URL
   ```

**Note:** Free ngrok URLs change each time you restart. For production, use a paid plan with a fixed domain.

---

### Option 2: Use Local Network IP (Same WiFi Only) ⚠️

This only works when the device and computer are on the **same WiFi network**.

#### Setup Steps:

1. **Find your computer's local IP:**
   ```bash
   # Windows:
   ipconfig
   # Look for "IPv4 Address" (e.g., 192.168.1.16)
   
   # Mac/Linux:
   ifconfig
   # Look for inet address
   ```

2. **Update `config/api.js`:**
   ```javascript
   // For development/testing
   const DEV_HOST = '192.168.1.16'; // Your computer's IP
   
   // For production build (still using local network)
   const PROD_LARAVEL_BASE_URL = `http://192.168.1.16:8000`;
   const PROD_API_BASE_URL = `http://192.168.1.16:3000/api`;
   ```

3. **Ensure Laravel accepts connections:**
   ```bash
   # Start Laravel on all interfaces (not just localhost)
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. **Update Node.js server (server.js):**
   ```javascript
   // Already configured to listen on 0.0.0.0
   app.listen(PORT, '0.0.0.0', () => {
     // This allows connections from network
   });
   ```

**Limitations:**
- Only works on same WiFi network
- IP may change if you reconnect to WiFi
- Not suitable for production/distribution

---

### Option 3: Use Environment-Based Configuration

Create different configs for development and production.

#### Setup:

1. **Update `config/api.js`:**
   ```javascript
   // Development - use local IP or ngrok
   const DEV_HOST = '192.168.1.16'; // Or ngrok URL
   
   // Production - use your actual domain
   const PROD_API_BASE_URL = process.env.EXPO_PUBLIC_API_URL || 'https://api.yourdomain.com/api';
   const PROD_LARAVEL_BASE_URL = process.env.EXPO_PUBLIC_LARAVEL_URL || 'https://api.yourdomain.com';
   
   // Use __DEV__ flag to switch
   const API_BASE_URL = __DEV__ 
     ? `http://${DEV_HOST}:3000/api`
     : PROD_API_BASE_URL;
   
   const LARAVEL_BASE_URL = __DEV__
     ? `http://${DEV_HOST}:8000`
     : PROD_LARAVEL_BASE_URL;
   ```

2. **For testing with local backend:**
   - Keep `__DEV__ = true` in development
   - Use local IP or ngrok URL
   - Build with development config

---

## Recommended Approach for Your Situation

Since you're still using localhost backend, I recommend:

### Step 1: Use ngrok for Testing
1. Set up ngrok tunnel
2. Update config with ngrok URL
3. Test the app thoroughly
4. Build and deploy

### Step 2: Update Config File

I'll create an updated config that supports this workflow.

---

## Quick Start with ngrok

```bash
# Terminal 1: Start Laravel
cd tomeco_web
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Start ngrok
ngrok http 8000

# Terminal 3: Start Node.js (if using)
cd tomeco_app
node server.js

# Terminal 4: Start ngrok for Node.js
ngrok http 3000
```

Then update your config with the ngrok URLs.

---

## Building the App

Once you have the backend accessible (via ngrok or local IP):

```bash
# Development build (uses __DEV__ config)
expo build:android --type apk

# Or with EAS
eas build --platform android --profile development
```

---

## Important Notes

1. **ngrok free tier:**
   - URLs change on restart
   - Limited connections
   - Not for production

2. **Local IP:**
   - Only works on same network
   - IP may change
   - Firewall may block

3. **For production:**
   - Deploy backend to a server
   - Use proper domain with HTTPS
   - Update config with production URLs

---

## Next Steps

1. Choose your approach (ngrok recommended)
2. Update `config/api.js` with the URLs
3. Test connectivity
4. Build and deploy app
5. Test on physical device

