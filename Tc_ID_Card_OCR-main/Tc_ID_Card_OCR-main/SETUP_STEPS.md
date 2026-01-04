# Step-by-Step Setup Instructions

## You have Python 3.11.9 - Perfect! ✅

Follow these steps:

### Step 1: Navigate to the OCR directory

```bash
cd "C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main"
```

### Step 2: Remove old virtual environment (if it exists)

```bash
# If you have an old virtual environment, remove it
if exist card_id_ocr_venv rmdir /s /q card_id_ocr_venv
```

### Step 3: Create new virtual environment with Python 3.11

```bash
py -3.11 -m venv card_id_ocr_venv
```

### Step 4: Activate the virtual environment

```bash
card_id_ocr_venv\Scripts\activate
```

You should see `(card_id_ocr_venv)` at the beginning of your prompt.

### Step 5: Upgrade pip

```bash
python -m pip install --upgrade pip
```

### Step 6: Install dependencies

```bash
pip install flask flask-cors opencv-python-headless "numpy<2.0" pytesseract Pillow
```

This should install smoothly with pre-built wheels!

### Step 7: Test installation

```bash
python -c "import cv2; import numpy; import flask; print('All packages installed successfully!')"
```

### Step 8: Start the OCR service

```bash
python ocr_api.py
```

The service will start on `http://localhost:5000`

## Quick Test

In another terminal, test the health endpoint:

```bash
curl http://localhost:5000/health
```

Or open in browser: http://localhost:5000/health

## Troubleshooting

If Step 6 fails, try installing packages one by one:

```bash
pip install flask
pip install flask-cors
pip install "numpy<2.0"
pip install opencv-python-headless
pip install Pillow
pip install pytesseract
```

