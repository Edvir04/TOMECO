# 🚀 Quick Production Deployment

## Ready to Deploy? Follow These Steps:

### Step 1: Start Production Services (5 minutes)

**Option A: Use the batch file (Easiest)**
```bash
# Double-click or run:
START_PRODUCTION.bat
```

**Option B: Manual commands**
```bash
cd tomeco_app
pm2 start ecosystem.config.js --env production
pm2 save
pm2 list
```

### Step 2: Start ngrok Tunnel (Required for Production)

**Option A: Use the batch file**
```bash
# Double-click or run:
START_NGROK.bat
```

**Option B: Manual command**
```bash
ngrok http 3000 --domain=tomeco.ngrok.dev
```

**⚠️ Keep ngrok running!** Don't close this window.

### Step 3: Verify Services (2 minutes)

Open these URLs in your browser:

1. **Node.js Server**: `https://tomeco.ngrok.dev/api/diagnostics`
   - Should return JSON with database info

2. **Health Check**: `https://tomeco.ngrok.dev/health`
   - Should show all services status

3. **Python OCR Test**: `https://tomeco.ngrok.dev/api/test-python-ocr`
   - Should show Python OCR is accessible

### Step 4: Build Production APK (15-20 minutes)

```bash
cd tomeco_app

# Login to Expo (if not already)
npx eas-cli login

# Build production APK
npx eas-cli build --platform android --profile production
```

**What happens:**
- Your code uploads to Expo servers
- APK builds in the cloud
- You get a download link when done

### Step 5: Install APK on Your Phone

1. **Download APK** from Expo dashboard (link provided after build)
2. **Transfer to phone** (USB, email, or cloud storage)
3. **Enable "Install from Unknown Sources"**:
   - Settings → Security → Unknown Sources (enable)
4. **Install APK** by tapping the file
5. **Open app** and test!

## ✅ Testing Checklist

After installing, test:
- [ ] Login works
- [ ] Profile loads correctly
- [ ] OCR scan works (capture ID card)
- [ ] Ticket creation works
- [ ] All features functional

## 🔧 Troubleshooting

### Services not starting?
```bash
pm2 logs
pm2 restart all
```

### ngrok not working?
- Check if ngrok is authenticated: `ngrok config check`
- Verify domain: `tomeco.ngrok.dev` matches your ngrok account
- For free ngrok, remove `--domain` flag

### APK build fails?
- Check Expo dashboard for error details
- Verify all dependencies are installed
- Make sure you're logged in: `npx eas-cli login`

### App can't connect?
- Verify ngrok is running
- Check PM2 services: `pm2 list`
- Test endpoints in browser first

## 📱 Current Setup

- **API Server**: `https://tomeco.ngrok.dev/api`
- **Node.js**: Running on port 3000 (via PM2)
- **Python OCR**: Running on port 5000 (via PM2)
- **Laravel**: Port 8000 (if needed)

## 🎯 You're Ready!

1. ✅ Run `START_PRODUCTION.bat`
2. ✅ Run `START_NGROK.bat` (keep it running)
3. ✅ Build APK: `npx eas-cli build --platform android --profile production`
4. ✅ Install on phone and test!

**That's it! Your app is now ready for real-life use! 🎉**

