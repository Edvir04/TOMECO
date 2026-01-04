# Test OCR Functionality

## Quick Test Steps

### 1. Verify Services Are Running

```bash
pm2 list
```

Both services should show "online":
- `tomeco-node-server`
- `python-ocr-service`

### 2. Test Python OCR Service Directly

Open browser and test:
```
http://localhost:5000/health
```

Should return: `{"status": "healthy", "service": "ID Card OCR API"}`

### 3. Test Node.js Connection to Python OCR

Open browser:
```
http://localhost:3000/api/test-python-ocr
```

Should return connection status.

### 4. Test OCR Endpoint (Requires Image)

Use Postman or curl to test:
```bash
curl -X POST http://localhost:3000/api/mobile/ocr/scan-id \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@/path/to/test-image.jpg"
```

### 5. Check PM2 Logs

```bash
pm2 logs tomeco-node-server
pm2 logs python-ocr-service
```

Look for:
- "Sending image to Python OCR service"
- "Python OCR completed"
- Any error messages

## Common Issues

### Issue: Python OCR not accessible
**Solution**: Check if Python service is running on port 5000
```bash
netstat -ano | findstr :5000
```

### Issue: Connection refused
**Solution**: Update PYTHON_OCR_HOST in ecosystem.config.js to 'localhost'

### Issue: File upload fails
**Solution**: Check multer is installed and uploads directory exists

### Issue: Timeout errors
**Solution**: OCR can take 10-30 seconds, increase timeout if needed

## Debugging

1. **Check server logs**: `pm2 logs`
2. **Test Python OCR health**: `http://localhost:5000/health`
3. **Test Node.js connection**: `http://localhost:3000/api/test-python-ocr`
4. **Check file upload**: Verify image file is being received

