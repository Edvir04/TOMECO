# Deploy TOMECO App to Production - Complete Guide

## 🚀 Quick Start

Follow these steps to deploy your app for real-life use:

## Step 1: Set Up ngrok (Quick Production Solution)

Since you're already using ngrok (`tomeco.ngrok.dev`), we'll use it for production deployment.

### Start ngrok Tunnels

You need **TWO ngrok tunnels**:

1. **Node.js Server (Port 3000)**:
   ```bash
   ngrok http 3000 --domain=tomeco.ngrok.dev
   ```

2. **Laravel Server (Port 8000)** - if using:
   ```bash
   ngrok http 8000 --domain=tomeco.ngrok.dev
   ```

**Note**: If you have a paid ngrok account, you can use custom domains. Otherwise, use the free ngrok domains.

## Step 2: Update API Configuration

The `config/api.js` is already configured to use ngrok. Just make sure:

1. ✅ `USE_NGROK = true` (or set `PRODUCTION_DOMAIN` if you have a real domain)
2. ✅ `NGROK_DOMAIN = 'tomeco.ngrok.dev'` matches your ngrok domain

## Step 3: Start All Services with PM2

```bash
cd tomeco_app

# Start all services
pm2 start ecosystem.config.js --env production

# Save configuration
pm2 save

# Check status
pm2 list
```

You should see:
- ✅ `tomeco-node-server` - online
- ✅ `python-ocr-service` - online

## Step 4: Verify Services Are Running

### Test Node.js Server:
```
https://tomeco.ngrok.dev/api/diagnostics
```

### Test Python OCR:
```
https://tomeco.ngrok.dev/api/test-python-ocr
```

### Test Health:
```
https://tomeco.ngrok.dev/health
```

## Step 5: Build Production APK

```bash
cd tomeco_app

# Make sure you're logged in to Expo
npx eas-cli login

# Build production APK
npx eas-cli build --platform android --profile production
```

**This will:**
- Take 10-20 minutes
- Upload your code to Expo's servers
- Build the APK
- Give you a download link when done

## Step 6: Install APK on Devices

1. **Download the APK** from Expo dashboard
2. **Transfer to Android device** (via USB, email, or cloud)
3. **Enable "Install from Unknown Sources"** on Android
4. **Install the APK**
5. **Open the app** and test!

## Step 7: Keep Services Running

### Option A: Keep ngrok Running (Development/Testing)

Keep your terminal open with ngrok running, or use PM2 to manage it.

### Option B: Use Real Domain (Production)

For permanent production deployment:

1. **Get a domain name** (e.g., `tomeco.com`)
2. **Set up SSL certificate** (Let's Encrypt - free)
3. **Configure DNS** to point to your server
4. **Update `config/api.js`**:
   ```javascript
   const PRODUCTION_DOMAIN = 'api.tomeco.com';
   ```
5. **Rebuild APK** with new domain

## 🔧 Current Configuration

Your app is configured to use:
- **Node.js Server**: `https://tomeco.ngrok.dev/api`
- **Laravel Server**: `https://tomeco.ngrok.dev` (if needed)
- **Python OCR**: Internal (localhost:5000)

## 📱 Testing Checklist

After deployment, test:

- [ ] Login works
- [ ] Profile loads
- [ ] OCR scan works
- [ ] Ticket creation works
- [ ] All features functional

## 🚨 Important Notes

### ngrok Limitations:
- ⚠️ **Free ngrok**: Domain changes on restart (unless you have paid plan)
- ⚠️ **Free ngrok**: May have rate limits
- ✅ **Solution**: Use paid ngrok or get a real domain

### For Permanent Production:
1. Get a domain name
2. Set up SSL (HTTPS required)
3. Update `PRODUCTION_DOMAIN` in `config/api.js`
4. Rebuild APK
5. Deploy to server

## 🎯 Next Steps

1. **Start ngrok tunnels** (Step 1)
2. **Start PM2 services** (Step 3)
3. **Build APK** (Step 5)
4. **Install and test** (Step 6)

## 📞 Support

If you encounter issues:
- Check PM2 logs: `pm2 logs`
- Test endpoints in browser
- Verify ngrok tunnels are active
- Check mobile app logs

---

**You're ready to deploy! 🚀**

