# Complete Production Deployment Guide

## ✅ Yes, You Can Deploy!

Both the mobile app and Python OCR service can work in production. Here's how:

## Production Architecture

```
┌─────────────┐
│   Mobile    │
│    App      │ (APK installed on devices)
└──────┬──────┘
       │ HTTPS
       ▼
┌─────────────┐
│  Production │
│   Server    │ (Your server with public IP/domain)
│             │
│  ┌────────┐ │
│  │ Node.js│ │ Port 3000 (API Server)
│  │ Server │ │
│  └───┬────┘ │
│      │      │
│  ┌───▼────┐ │
│  │ Python │ │ Port 5000 (OCR Service)
│  │  OCR   │ │
│  └────────┘ │
│             │
│  ┌────────┐ │
│  │Laravel │ │ Port 8000 (Web Portal)
│  │ Server │ │
│  └────────┘ │
└─────────────┘
```

## Step 1: Prepare Production Server

### Requirements:
- **Public IP address** or **domain name** (e.g., `api.tomeco.com`)
- **SSL Certificate** (HTTPS required for production)
- **Firewall** configured to allow ports 3000, 5000, 8000
- **PM2** installed and configured (already done!)

### Server Setup:

1. **Get a domain name** (optional but recommended):
   - Register domain: `tomeco.com` or similar
   - Point DNS to your server's IP

2. **Set up SSL/HTTPS**:
   - Use Let's Encrypt (free SSL certificates)
   - Or use a service like Cloudflare

3. **Configure Firewall**:
   ```bash
   # Allow ports
   - 3000 (Node.js API)
   - 5000 (Python OCR - internal only, can be blocked from public)
   - 8000 (Laravel Web Portal)
   - 443 (HTTPS)
   - 80 (HTTP - redirect to HTTPS)
   ```

## Step 2: Update API Configuration for Production

Update `tomeco_app/config/api.js`:

```javascript
// Production configuration
const PRODUCTION_DOMAIN = 'api.tomeco.com'; // Your production domain
const USE_NGROK = false; // Disable ngrok in production

const API_BASE_URL = __DEV__ 
  ? `http://${DEV_HOST}:3000/api`  // Development
  : `https://${PRODUCTION_DOMAIN}/api`; // Production

const LARAVEL_BASE_URL = __DEV__
  ? `http://${DEV_HOST}:8000`  // Development
  : `https://${PRODUCTION_DOMAIN}`; // Production
```

## Step 3: Deploy Services to Production Server

### Option A: Same Server (Recommended for Start)

Deploy everything on one server:

1. **Upload code to server:**
   ```bash
   # Upload via FTP, SCP, or Git
   scp -r tomeco_app user@your-server:/var/www/
   scp -r Tc_ID_Card_OCR-main user@your-server:/var/www/
   ```

2. **Install dependencies:**
   ```bash
   # On server
   cd /var/www/tomeco_app
   npm install --production
   
   cd /var/www/Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main
   pip install -r requirements.txt
   ```

3. **Update ecosystem.config.js for production:**
   ```javascript
   env_production: {
     NODE_ENV: 'production',
     PORT: 3000,
     PYTHON_OCR_HOST: 'localhost', // Same server
     PYTHON_OCR_PORT: '5000'
   }
   ```

4. **Start with PM2:**
   ```bash
   pm2 start ecosystem.config.js --env production
   pm2 save
   pm2 startup
   ```

### Option B: Separate Servers

If you want to scale:

- **API Server**: Node.js + Python OCR
- **Web Server**: Laravel
- **Database Server**: PostgreSQL

## Step 4: Build Mobile App for Production

### Update API Config First:

1. **Edit `config/api.js`**:
   ```javascript
   const PRODUCTION_DOMAIN = 'api.tomeco.com'; // Your actual domain
   const USE_NGROK = false;
   ```

2. **Build APK**:
   ```bash
   cd tomeco_app
   npx eas-cli build --platform android --profile production
   ```

3. **Distribute APK**:
   - Download from Expo dashboard
   - Install on devices
   - Or publish to Google Play Store

## Step 5: Python OCR in Production

### ✅ Yes, Python OCR Will Work!

The Python OCR service will work in production if:

1. **It's running on the server** (via PM2)
2. **Node.js can reach it** (localhost or internal network)
3. **It has required dependencies** installed

### Production Considerations:

1. **Use Production WSGI Server** (instead of Flask dev server):
   
   Update `ocr_api.py`:
   ```python
   # For production, use gunicorn
   # Install: pip install gunicorn
   # Run: gunicorn -w 4 -b 0.0.0.0:5000 ocr_api:app
   ```

2. **Update PM2 config** for production:
   ```javascript
   {
     name: 'python-ocr-service',
     script: 'gunicorn',
     args: '-w 4 -b 0.0.0.0:5000 ocr_api:app',
     // ... rest of config
   }
   ```

3. **Resource Management**:
   - OCR is CPU/memory intensive
   - Monitor with `pm2 monit`
   - Consider load balancing if needed

## Step 6: Security Checklist

- [ ] Use HTTPS (SSL certificates)
- [ ] Set strong passwords for database
- [ ] Use environment variables for secrets
- [ ] Enable firewall
- [ ] Restrict Python OCR port (5000) to internal only
- [ ] Use reverse proxy (Nginx) for better security
- [ ] Regular backups
- [ ] Monitor logs

## Step 7: Reverse Proxy Setup (Nginx)

For better security and performance:

```nginx
# Node.js API Server
server {
    listen 443 ssl;
    server_name api.tomeco.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}

# Python OCR (Internal only - don't expose publicly)
# Or use internal network access only
```

## Testing Production Setup

1. **Test Node.js API:**
   ```
   https://api.tomeco.com/api/diagnostics
   ```

2. **Test Health Endpoint:**
   ```
   https://api.tomeco.com/health
   ```

3. **Test Mobile App:**
   - Install APK on device
   - Try login
   - Test OCR functionality

## Production Checklist

### Before Deployment:
- [ ] Update `config/api.js` with production domain
- [ ] Set `USE_NGROK = false`
- [ ] Test all endpoints work
- [ ] Build production APK
- [ ] Set up SSL certificates
- [ ] Configure firewall
- [ ] Set up monitoring

### After Deployment:
- [ ] Test mobile app login
- [ ] Test OCR functionality
- [ ] Monitor PM2 logs
- [ ] Check server resources
- [ ] Set up backups
- [ ] Document credentials securely

## Python OCR Production Notes

✅ **Will work if:**
- Python service is running via PM2
- All dependencies installed
- Server has enough resources (CPU/RAM)
- Node.js can reach Python service

⚠️ **Consider:**
- Using Gunicorn instead of Flask dev server
- Monitoring resource usage
- Scaling if needed (multiple Python workers)

## Quick Production Start

1. **Update config/api.js** with production domain
2. **Build APK**: `npx eas-cli build --platform android --profile production`
3. **Deploy to server** and start with PM2
4. **Install APK** on devices
5. **Test everything**

## Summary

✅ **Mobile App**: Can be built and deployed  
✅ **Python OCR**: Will work in production  
✅ **Node.js Server**: Ready for production  
✅ **PM2**: Already configured  

You're ready to deploy! 🚀

