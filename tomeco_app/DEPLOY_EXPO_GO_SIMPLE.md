# Deploy TOMECO App with Expo Go - Simple Instructions

## 📋 Prerequisites Checklist

- [ ] Node.js installed
- [ ] Expo Go app installed on your phone
- [ ] Backend servers ready to start
- [ ] ngrok Pro account with domains configured

---

## 🚀 Step-by-Step Instructions

### **Open 3 Separate CMD Windows**

---

### **CMD Window 1: Start Admin Server**

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
set APP_PORTAL_TYPE=admin
php artisan serve --port=8000
```

**✅ You should see:**
```
Starting Laravel development server: http://127.0.0.1:8000
```

**⚠️ Keep this window open!**

---

### **CMD Window 2: Start ngrok Tunnels**

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-ngrok-both.bat
```

**✅ You should see:**
```
Forwarding: https://tomeco.ngrok.dev -> http://127.0.0.1:8000
Forwarding: https://Tomeco-Violator.ngrok.dev -> http://127.0.0.1:8001
```

**✅ Verify ngrok is running:**
- Open browser: `http://127.0.0.1:4040`
- Check both tunnels are active

**⚠️ Keep this window open!**

---

### **CMD Window 3: Start Expo App**

#### Step 3a: Install Dependencies (First time only)

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
npm install
```

Wait for installation to complete.

#### Step 3b: Start Expo Development Server

```cmd
npx expo start
```

**✅ You should see:**
```
› Metro waiting on exp://192.168.x.x:8081
› Scan the QR code above with Expo Go (Android) or the Camera app (iOS)
```

**A QR code will appear in the terminal!**

---

## 📱 Connect with Expo Go on Your Phone

1. **Open Expo Go app** on your phone
   - Download from Play Store (Android) or App Store (iOS) if needed

2. **Scan the QR code** from CMD Window 3:
   - **Android**: Use Expo Go app's built-in scanner
   - **iOS**: Use Camera app (will automatically open Expo Go)

3. **Wait for app to load** (may take 1-2 minutes on first load)

4. **Check the console** in CMD Window 3 - you should see:
   ```
   API Configuration: {
     USE_NGROK: true,
     LARAVEL_BASE_URL: 'https://tomeco.ngrok.dev',
     ...
   }
   ```

---

## ✅ Verify Everything is Working

### Test 1: Check API Endpoint (Optional)

Open a new CMD window:

```cmd
curl https://tomeco.ngrok.dev/api/mobile/health
```

**Expected response:**
```json
{"success":true,"message":"API is accessible","timestamp":"..."}
```

### Test 2: Test in App

1. Try to **login** with enforcer credentials
2. Check if **tickets** load
3. Verify **network status** indicator works

---

## 🔧 Troubleshooting

### ❌ Problem: "Network request failed"

**Solutions:**
1. Check ngrok is running: Visit `http://127.0.0.1:4040`
2. Test API: `curl https://tomeco.ngrok.dev/api/mobile/health`
3. Verify backend is running on port 8000
4. Check `config/api.js` has `USE_NGROK = true`

### ❌ Problem: "Cannot connect to Expo"

**Solutions:**
1. Make sure phone and computer are on **same WiFi network**
2. Try tunnel mode: `npx expo start --tunnel`
3. Check firewall isn't blocking port 8081

### ❌ Problem: "QR code not scanning"

**Solutions:**
1. Use tunnel mode: `npx expo start --tunnel`
2. Manually enter URL in Expo Go app
3. Make sure phone camera has permission

### ❌ Problem: "API Configuration shows wrong URL"

**Check:**
1. Open `tomeco_app/config/api.js`
2. Verify: `const USE_NGROK = true;`
3. Verify: `const NGROK_DOMAIN = 'tomeco.ngrok.dev';`
4. Restart Expo: Press `r` in the Expo terminal

---

## 📝 Quick Reference Commands

### Start Everything (3 separate CMD windows):

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
npm install
npx expo start
```

### Stop Everything:

```cmd
REM In each CMD window, press: Ctrl+C

REM Stop ngrok separately:
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
stop-ngrok.bat
```

---

## ⚠️ Important Notes

1. **Keep all 3 CMD windows open** while testing
2. **ngrok must stay running** - if it stops, the app won't connect
3. **Backend server must stay running** on port 8000
4. **Expo Go has limitations** - some native features may not work
5. **For production**, use EAS Build instead of Expo Go

---

## 🎯 Current Configuration

Your API is configured to use:
- **Domain**: `https://tomeco.ngrok.dev`
- **USE_NGROK**: `true` ✅
- **Logging**: Enabled in development mode ✅

---

## 📞 Next Steps

1. ✅ Test with Expo Go (basic functionality)
2. ⏭️ Create development build for full features: `npx expo run:android`
3. ⏭️ Build production APK: `eas build --platform android`

---

## 🆘 Still Having Issues?

1. Check all 3 CMD windows are running
2. Verify ngrok web interface: `http://127.0.0.1:4040`
3. Check Expo console for error messages
4. Verify API config: Look for "API Configuration" log in Expo console
5. Test API endpoint manually with curl

