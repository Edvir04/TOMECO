# Check PM2 Status

## Quick Status Check

Run this command to see if both services are online:

```bash
pm2 list
```

You should see both services with status "online".

## Expected Output

```
┌────┬────────────────────┬──────────┬──────┬───────────┬──────────┬──────────┐
│ id │ name               │ mode     │ ↺    │ status    │ cpu      │ memory   │
├────┼────────────────────┼──────────┼──────┼───────────┼──────────┼──────────┤
│ 0  │ tomeco-node-server │ fork     │ 0    │ online    │ 0%       │ XXmb     │
│ 1  │ python-ocr-service │ fork     │ 0    │ online    │ 0%       │ XXmb     │
└────┴────────────────────┴──────────┴──────┴───────────┴──────────┴──────────┘
```

## Test the Services

### Test Node.js Server:
Open browser: `http://localhost:3000/api/diagnostics`

Should return JSON with database info.

### Test Python OCR Service:
Open browser: `http://192.168.1.10:5000/health`

Should return: `{"status": "healthy", "service": "ID Card OCR API"}`

### Test Health Endpoint:
Open browser: `http://localhost:3000/health`

Should return status of all services.

## If Services Are Online

✅ **Everything is working!** You can now:
- Use the mobile app
- Test OCR functionality
- Both services will auto-restart if they crash

## About the wmic Errors

The `spawn wmic ENOENT` errors are harmless. They occur because:
- PM2 tries to get detailed process statistics on Windows
- `wmic` command might not be available or accessible
- **This does NOT affect functionality** - services still work fine

You can ignore these errors.

