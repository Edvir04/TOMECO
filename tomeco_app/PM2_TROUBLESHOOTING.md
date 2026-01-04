# PM2 Troubleshooting Guide

## Current Issues

### Issue 1: Node.js Server Restarting Too Frequently

**Symptoms:**
- Server starts successfully
- Then exits with code 0 via SIGINT
- PM2 restarts it repeatedly
- PM2 stops it after 10 restarts ("errored")

**Solutions:**

1. **Check if port 3000 is already in use:**
   ```bash
   netstat -ano | findstr :3000
   ```
   If something is using port 3000, either:
   - Stop that process
   - Change PORT in ecosystem.config.js

2. **Check PM2 status:**
   ```bash
   pm2 list
   pm2 logs tomeco-node-server --lines 50
   ```

3. **Restart with updated config:**
   ```bash
   pm2 delete all
   pm2 start ecosystem.config.js
   pm2 save
   ```

4. **If still having issues, try starting manually first:**
   ```bash
   node server.js
   ```
   If it runs fine manually, the issue is with PM2 configuration.

### Issue 2: Python OCR Service Restarting

**Symptoms:**
- Python service starts but restarts frequently
- Logs show Flask starting multiple times

**Solutions:**

1. **Check if port 5000 is already in use:**
   ```bash
   netstat -ano | findstr :5000
   ```

2. **Check Python service logs:**
   ```bash
   pm2 logs python-ocr-service --lines 50
   ```

3. **Test Python service manually:**
   ```bash
   cd Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main
   card_id_ocr_venv\Scripts\activate
   python ocr_api.py
   ```

### Issue 3: PM2 wmic Error (Non-Critical)

**Symptoms:**
- `Error: spawn wmic ENOENT` in PM2 logs
- This is a Windows-specific issue with PM2 trying to get process stats

**Solution:**
- This is a warning, not a critical error
- PM2 will still work, just without detailed process statistics
- Can be ignored for now

## Quick Fixes

### Reset PM2 and Start Fresh

```bash
# Stop all processes
pm2 delete all

# Clear PM2 logs
pm2 flush

# Start services
pm2 start ecosystem.config.js

# Save configuration
pm2 save

# Check status
pm2 list
pm2 logs
```

### Check What's Running on Ports

```bash
# Check port 3000 (Node.js)
netstat -ano | findstr :3000

# Check port 5000 (Python OCR)
netstat -ano | findstr :5000
```

### Manual Start (For Testing)

If PM2 is having issues, you can start services manually to test:

**Terminal 1 - Node.js:**
```bash
cd tomeco_app
node server.js
```

**Terminal 2 - Python OCR:**
```bash
cd Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main
card_id_ocr_venv\Scripts\activate
python ocr_api.py
```

## Updated Configuration

The `ecosystem.config.js` has been updated with:
- Increased `min_uptime` to 30 seconds
- Increased `max_restarts` to 15
- Added `restart_delay` of 4 seconds
- Added signal handling in server.js

## Next Steps

1. **Delete and restart PM2:**
   ```bash
   pm2 delete all
   pm2 start ecosystem.config.js
   ```

2. **Monitor for a few minutes:**
   ```bash
   pm2 monit
   ```

3. **Check if services stay online:**
   ```bash
   pm2 list
   ```

If services are still restarting, check the logs for specific errors.

