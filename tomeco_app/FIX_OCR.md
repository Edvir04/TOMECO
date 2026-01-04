# Fix OCR - Step by Step

## Changes Made

1. ✅ **Updated Python OCR connection** to use `localhost` instead of IP
2. ✅ **Added health check** before OCR request
3. ✅ **Improved error handling** with detailed messages
4. ✅ **Added timeout handling** (60 seconds)
5. ✅ **Added test endpoint** `/api/test-python-ocr`
6. ✅ **Updated PM2 config** to use localhost

## Restart Services

**IMPORTANT**: You need to restart PM2 for changes to take effect:

```bash
# Stop all services
pm2 delete all

# Start with updated config
pm2 start ecosystem.config.js

# Save configuration
pm2 save

# Check status
pm2 list
```

## Test OCR Now

### 1. Test Python OCR Service

Open browser:
```
http://localhost:5000/health
```

Should return: `{"status": "healthy", "service": "ID Card OCR API"}`

### 2. Test Node.js Connection

Open browser:
```
http://localhost:3000/api/test-python-ocr
```

Should show Python OCR is accessible.

### 3. Test in Mobile App

1. Open your mobile app
2. Go to OCR scan
3. Capture/select an ID card image
4. Process OCR

## If Still Not Working

### Check Logs

```bash
# View Node.js server logs
pm2 logs tomeco-node-server --lines 50

# View Python OCR logs
pm2 logs python-ocr-service --lines 50
```

### Common Issues

1. **Python OCR not running**
   - Check: `pm2 list`
   - Restart: `pm2 restart python-ocr-service`

2. **Connection refused**
   - Verify Python OCR is on port 5000
   - Check: `netstat -ano | findstr :5000`

3. **File upload issues**
   - Check multer is working
   - Verify image file is being received

4. **Timeout errors**
   - OCR can take 10-30 seconds
   - Check Python OCR logs for processing time

## Debug Endpoints

- **Health Check**: `http://localhost:3000/health`
- **Test Python OCR**: `http://localhost:3000/api/test-python-ocr`
- **Diagnostics**: `http://localhost:3000/api/diagnostics`

## Next Steps

After restarting PM2, test OCR in your mobile app. If it still doesn't work, check the logs and share the error messages.

