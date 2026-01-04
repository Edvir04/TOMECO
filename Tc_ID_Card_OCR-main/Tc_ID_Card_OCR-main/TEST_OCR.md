# How to Test the OCR Service

## Method 1: Test Health Endpoint (Easiest)

```bash
curl http://localhost:5000/health
```

Or open in browser: http://localhost:5000/health

Should return:
```json
{
  "status": "healthy",
  "service": "ID Card OCR API"
}
```

## Method 2: Test OCR with Real Image File

### Step 1: Find or Create a Test Image

You need an actual image file. Options:
- Use an ID card image you have
- Take a photo of an ID card with your phone
- Use any text image for testing

### Step 2: Use the Correct Path

Replace `path/to/test_id_card.jpg` with your actual file path:

**Example:**
```bash
# If image is on Desktop
curl -X POST -F "image=@C:\Users\Charles Rosete\Desktop\id_card.jpg" http://localhost:5000/ocr/scan-id

# If image is in Downloads
curl -X POST -F "image=@C:\Users\Charles Rosete\Downloads\ID.jpg" http://localhost:5000/ocr/scan-id

# If image is in the OCR project folder
curl -X POST -F "image=@C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main\test_image.jpg" http://localhost:5000/ocr/scan-id
```

### Step 3: Check Response

You should get a JSON response like:
```json
{
  "success": true,
  "raw_text": "Extracted text from image...",
  "lines": ["Line 1", "Line 2", ...],
  "message": "OCR processing completed"
}
```

## Method 3: Test from Mobile App (Best Way)

This is the **recommended** way to test:

1. **Start Python OCR service:**
   ```bash
   python ocr_api.py
   ```

2. **Start Laravel server:**
   ```bash
   cd tomeco_web
   php artisan serve --host=0.0.0.0 --port=8000
   ```

3. **Open mobile app:**
   - Go to "Issue New Ticket"
   - Click "Scan ID Card (OCR)"
   - Take/select an ID card image
   - Check if fields are extracted

## Method 4: Use Postman or Browser Extension

If you have Postman or a REST client:

1. **Method:** POST
2. **URL:** http://localhost:5000/ocr/scan-id
3. **Body:** form-data
4. **Key:** `image` (type: File)
5. **Value:** Select your image file
6. **Send**

## Troubleshooting

### "Failed to open/read local data"
- Make sure the file path is correct
- Use full absolute path (e.g., `C:\Users\...`)
- Check if file exists: `dir "C:\path\to\file.jpg"`
- Make sure file is not locked by another program

### "Connection refused"
- Make sure Python service is running: `python ocr_api.py`
- Check if port 5000 is available
- Try: `curl http://localhost:5000/health` first

### "No such file or directory"
- Use forward slashes or escaped backslashes in curl
- Or use PowerShell instead of CMD:
  ```powershell
  curl -X POST -F "image=@C:/Users/Charles Rosete/Desktop/id_card.jpg" http://localhost:5000/ocr/scan-id
  ```

## Quick Test Script

Create a test script `test_ocr.bat`:

```batch
@echo off
echo Testing OCR Service...
echo.

REM Test health endpoint
echo [1/2] Testing health endpoint...
curl http://localhost:5000/health
echo.
echo.

REM Test OCR (replace with your image path)
echo [2/2] Testing OCR endpoint...
echo Replace "YOUR_IMAGE_PATH" with actual image file path
REM curl -X POST -F "image=@YOUR_IMAGE_PATH" http://localhost:5000/ocr/scan-id

pause
```

