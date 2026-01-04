# Quick Start Guide - Python OCR API

## Step-by-Step Installation

### 1. Navigate to the OCR Directory

Open Command Prompt or PowerShell and run:

```bash
cd "C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main"
```

**OR** if you're already in `Capstone_Gigs`:

```bash
cd "Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main"
```

### 2. Create Virtual Environment (if not already created)

```bash
python -m venv card_id_ocr_venv
```

### 3. Activate Virtual Environment

```bash
card_id_ocr_venv\Scripts\activate
```

You should see `(card_id_ocr_venv)` at the beginning of your command prompt.

### 4. Install Dependencies

```bash
pip install -r requirements_api.txt
```

**Note:** This may take 5-10 minutes, especially when installing EasyOCR (it downloads models).

### 5. Start the OCR API Service

```bash
python ocr_api.py
```

Or use the batch file:

```bash
start_ocr_api.bat
```

The service will start on `http://localhost:5000`

## Alternative: Use Installation Script

You can also use the automated installation script:

```bash
cd "Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main"
INSTALL.bat
```

This will:
- Create virtual environment (if needed)
- Activate it
- Install all dependencies
- Show you how to start the service

## Verify Installation

After starting the service, test it in another terminal:

```bash
curl http://localhost:5000/health
```

Or open in browser: http://localhost:5000/health

You should see:
```json
{
  "status": "healthy",
  "service": "ID Card OCR API"
}
```

## Troubleshooting

### "Could not open requirements file"
- Make sure you're in the correct directory: `Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main`
- Check if file exists: `dir requirements_api.txt`

### "Python is not recognized"
- Install Python 3.8+ from python.org
- Make sure to check "Add Python to PATH" during installation

### "pip is not recognized"
- Make sure virtual environment is activated: `card_id_ocr_venv\Scripts\activate`
- You should see `(card_id_ocr_venv)` in your prompt

### Installation takes too long
- This is normal, especially for EasyOCR
- EasyOCR downloads AI models (~500MB) on first install
- Be patient and let it complete

## Next Steps

After installation:
1. Start the OCR service: `python ocr_api.py`
2. Configure Laravel `.env`: `PYTHON_OCR_URL=http://localhost:5000`
3. Test OCR from mobile app

