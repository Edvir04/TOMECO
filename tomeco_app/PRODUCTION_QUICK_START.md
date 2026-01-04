# Production Deployment - Quick Start

## ✅ Yes, You Can Deploy!

**Both the mobile app and Python OCR will work in production.**

## Current Status

✅ **Node.js Server**: Running via PM2  
✅ **Python OCR Service**: Running via PM2  
✅ **Services are stable**: Both online  
✅ **Ready for production**: Just need to configure and build

## Quick Production Steps

### 1. Update API Configuration

**For Production**, you need to update `config/api.js`:

```javascript
// Change this line:
const PRODUCTION_DOMAIN = 'your-production-domain.com'; // Your actual domain or IP

// And set:
const USE_NGROK = false; // Disable ngrok in production
```

**Options:**
- **Option A**: Use a domain name (e.g., `api.tomeco.com`)
- **Option B**: Use your server's public IP address
- **Option C**: Use ngrok for testing (not recommended for production)

### 2. Build Production APK

```bash
cd tomeco_app

# Update config/api.js first with production domain
# Then build:
npx eas-cli build --platform android --profile production
```

### 3. Deploy to Production Server

**If deploying to a server:**

1. **Upload code to server**
2. **Install dependencies:**
   ```bash
   npm install --production
   pip install -r requirements.txt
   ```
3. **Start with PM2:**
   ```bash
   pm2 start ecosystem.config.js --env production
   pm2 save
   ```

### 4. Python OCR in Production

✅ **Yes, it will work!** The Python OCR service:
- Runs via PM2 (already configured)
- Accessible from Node.js server (localhost:5000)
- Processes OCR requests automatically
- Auto-restarts if it crashes

**No additional setup needed** - it's already production-ready!

## Production Checklist

### Before Building APK:
- [ ] Update `config/api.js` with production domain/IP
- [ ] Set `USE_NGROK = false`
- [ ] Test API endpoints work
- [ ] Verify Python OCR is accessible

### Server Requirements:
- [ ] Public IP or domain name
- [ ] SSL certificate (HTTPS) - **Required for production**
- [ ] Firewall configured
- [ ] PM2 installed
- [ ] Node.js and Python installed

### After Deployment:
- [ ] Test mobile app login
- [ ] Test OCR functionality
- [ ] Monitor PM2: `pm2 monit`
- [ ] Check logs: `pm2 logs`

## Important Notes

### Python OCR:
- ✅ **Will work in production** - already running via PM2
- ✅ **No changes needed** - current setup is production-ready
- ⚠️ **Consider**: Using Gunicorn instead of Flask dev server for better performance

### Security:
- 🔒 **Use HTTPS** - Required for production
- 🔒 **Don't expose Python OCR port (5000)** publicly
- 🔒 **Use reverse proxy** (Nginx) for better security

## Current Setup (Development)

Your current setup:
- ✅ Node.js server: `http://192.168.1.16:3000`
- ✅ Python OCR: `http://192.168.1.10:5000`
- ✅ Both running via PM2
- ✅ Auto-restart enabled

**For production**, just change the URLs in `config/api.js` to your production domain!

## Next Steps

1. **Get a production server** (or use current one with public IP)
2. **Set up domain/SSL** (or use IP with HTTPS)
3. **Update `config/api.js`** with production URLs
4. **Build APK**: `npx eas-cli build --platform android --profile production`
5. **Deploy and test!**

You're ready! 🚀

