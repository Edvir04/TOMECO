# Python OCR Integration Setup

This guide explains how to set up and use the advanced Python OCR service for better ID card recognition accuracy.

## Overview

The system now supports two OCR methods:
1. **Python OCR Service** (Recommended) - Uses advanced image preprocessing, face detection, and EasyOCR/Tesseract
2. **PHP Tesseract** (Fallback) - Basic OCR using PHP Tesseract wrapper

The Laravel application will automatically use Python OCR if available, otherwise it falls back to PHP Tesseract.

## Setup Instructions

### Step 1: Install Python

Download and install Python 3.8 or higher from [python.org](https://www.python.org/downloads/)

Verify installation:
```bash
python --version
```

### Step 2: Install Tesseract OCR

#### Windows:
1. Download installer from: https://github.com/UB-Mannheim/tesseract/wiki
2. Run installer
3. Add Tesseract to PATH (usually `C:\Program Files\Tesseract-OCR`)

#### Linux:
```bash
sudo apt-get update
sudo apt-get install tesseract-ocr
```

#### Mac:
```bash
brew install tesseract
```

### Step 3: Set Up Python OCR Service

1. Navigate to the OCR project directory:
```bash
cd Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main
```

2. Create and activate virtual environment:

**Windows:**
```bash
python -m venv card_id_ocr_venv
card_id_ocr_venv\Scripts\activate
```

**Linux/Mac:**
```bash
python3 -m venv card_id_ocr_venv
source card_id_ocr_venv/bin/activate
```

3. Install Python dependencies:
```bash
pip install -r requirements_api.txt
```

**Note:** EasyOCR installation may take several minutes as it downloads models.

### Step 4: Start Python OCR Service

#### Windows:
```bash
start_ocr_api.bat
```

#### Linux/Mac:
```bash
python ocr_api.py
```

The service will start on `http://localhost:5000`

### Step 5: Configure Laravel

Add the Python OCR service URL to your `.env` file:

```env
PYTHON_OCR_URL=http://localhost:5000
```

If running on a different machine or port, update accordingly:
```env
PYTHON_OCR_URL=http://192.168.1.5:5000
```

### Step 6: Test the Integration

1. Start the Python OCR service (see Step 4)
2. Start your Laravel server
3. Test OCR from the mobile app

The system will automatically:
- Check if Python OCR service is available
- Use Python OCR if available (better accuracy)
- Fall back to PHP Tesseract if Python service is unavailable

## Features

### Python OCR Service Advantages:
- ✅ **Better Image Preprocessing**: Automatic perspective correction
- ✅ **Face Detection**: Automatically orients ID cards based on face detection
- ✅ **EasyOCR Support**: More accurate than Tesseract for some text
- ✅ **Rotation Detection**: Handles rotated ID card images
- ✅ **Better Text Extraction**: Improved accuracy for complex layouts

### PHP Tesseract (Fallback):
- ✅ Always available (no additional service needed)
- ✅ Faster processing
- ✅ Good for simple, clear images

## Troubleshooting

### Python Service Not Starting

**Error: "Python is not installed"**
- Install Python 3.8+ and add to PATH

**Error: "Module not found"**
- Activate virtual environment: `card_id_ocr_venv\Scripts\activate` (Windows) or `source card_id_ocr_venv/bin/activate` (Linux/Mac)
- Install dependencies: `pip install -r requirements_api.txt`

**Error: "Port 5000 already in use"**
- Change port in `ocr_api.py`: `app.run(host='0.0.0.0', port=5001, ...)`
- Update `.env`: `PYTHON_OCR_URL=http://localhost:5001`

### Laravel Cannot Connect to Python Service

**Error: "Connection refused"**
- Ensure Python service is running
- Check `PYTHON_OCR_URL` in `.env` matches the service URL
- Test manually: `curl http://localhost:5000/health`

**Error: "Timeout"**
- Increase timeout in `PythonOCRService.php` (currently 30 seconds)
- Check if image file is too large

### OCR Accuracy Issues

1. **Improve Image Quality:**
   - Use good lighting
   - Ensure ID card is in focus
   - Avoid glare and shadows

2. **Install EasyOCR:**
   - EasyOCR provides better accuracy than Tesseract
   - Installation: `pip install easyocr` (included in requirements_api.txt)

3. **Check Tesseract Language:**
   - Ensure English language data is installed
   - For other languages, install language packs

## Running Both Services

You can run both Python OCR and PHP Tesseract simultaneously:
- Python OCR will be used if available
- PHP Tesseract will be used as fallback

This ensures OCR always works even if Python service is down.

## Performance Notes

- **Python OCR**: Slower but more accurate (2-5 seconds per image)
- **PHP Tesseract**: Faster but less accurate (1-2 seconds per image)
- **EasyOCR**: Best accuracy but slowest (3-8 seconds per image)

For production, consider:
- Running Python service on a separate server
- Using a queue system for OCR processing
- Caching results for repeated scans

## Next Steps

1. Test OCR with sample ID card images
2. Monitor logs for accuracy improvements
3. Adjust field detection patterns if needed (in `OCRController.php`)

