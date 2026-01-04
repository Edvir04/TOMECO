# OCR Service Verification Checklist

## ✅ What Will Work

After running `python ocr_api.py`, the OCR for ID cards **WILL work** if:

### 1. Python Service is Running ✅
- Service starts on `http://localhost:5000`
- Health endpoint responds: `curl http://localhost:5000/health`

### 2. Tesseract OCR is Installed ✅
- **System requirement**: Tesseract OCR must be installed on your computer
- Windows: Download from https://github.com/UB-Mannheim/tesseract/wiki
- Verify: `tesseract --version` should work in command prompt

### 3. Laravel Configuration ✅
- Add to `tomeco_web/.env`:
  ```env
  PYTHON_OCR_URL=http://localhost:5000
  ```

### 4. Complete Flow ✅

```
Mobile App (Scan ID)
    ↓
Laravel API (OCRController)
    ↓
Python OCR Service (ocr_api.py) ← You're here!
    ↓
Tesseract OCR (extracts text)
    ↓
Laravel (parses text, extracts fields)
    ↓
Mobile App (receives: lastname, firstname, middlename, address)
```

## 🔍 How to Verify It's Working

### Step 1: Test Python Service Directly

```bash
# In another terminal, test the health endpoint
curl http://localhost:5000/health
```

Should return:
```json
{
  "status": "healthy",
  "service": "ID Card OCR API"
}
```

### Step 2: Test OCR Endpoint (Optional)

You can test the OCR endpoint directly with a test image:

```bash
curl -X POST -F "image=@path/to/test_id_card.jpg" http://localhost:5000/ocr/scan-id
```

### Step 3: Test from Mobile App

1. Open your mobile app
2. Go to "Issue New Ticket"
3. Choose "Scan ID Card (OCR)"
4. Take/select an ID card image
5. Check if fields are extracted

## ⚠️ Important Notes

### What the Python Service Does:
- ✅ Receives image from Laravel
- ✅ Performs OCR using Tesseract
- ✅ Returns raw extracted text
- ✅ Handles image preprocessing (perspective correction, rotation)

### What Laravel Does:
- ✅ Receives raw text from Python service
- ✅ Parses text to find field labels:
  - "Apelyido/Last Name" → `driver_lastname`
  - "Mga Pangalan/Given Name" → `driver_firstname`
  - "Gitnang Apelyido/Middle Name" → `driver_middlename`
  - "Tirahan/Address" → `driver_address`
- ✅ Validates and cleans extracted data
- ✅ Returns structured data to mobile app

### Fallback Behavior:
- If Python service is **not running** → Uses PHP Tesseract (still works!)
- If Python service **fails** → Falls back to PHP Tesseract
- If Python service **returns empty text** → Falls back to PHP Tesseract

## 🚀 Quick Start Commands

```bash
# 1. Start Python OCR Service
cd "Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main"
python ocr_api.py

# 2. In another terminal, verify it's running
curl http://localhost:5000/health

# 3. Make sure Laravel .env has:
# PYTHON_OCR_URL=http://localhost:5000

# 4. Test from mobile app!
```

## 📝 Expected Behavior

### When Python Service is Running:
- Mobile app scans ID card
- Laravel sends image to Python service
- Python service processes image and extracts text
- Laravel parses text and extracts fields
- Mobile app receives populated form fields

### When Python Service is NOT Running:
- Mobile app scans ID card
- Laravel detects Python service unavailable
- Laravel uses PHP Tesseract directly
- Still works, but may have slightly lower accuracy

## ✅ Summary

**YES, the OCR will work!** The system is designed to:
1. Use Python OCR when available (better accuracy)
2. Fall back to PHP Tesseract if Python service is down
3. Always provide OCR functionality

Just make sure:
- ✅ Python service is running (`python ocr_api.py`)
- ✅ Tesseract OCR is installed on your system
- ✅ Laravel `.env` has `PYTHON_OCR_URL=http://localhost:5000`

