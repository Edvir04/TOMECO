# Python Version Compatibility Issue

## Problem

You're using **Python 3.14**, which is very new. Many packages don't have pre-built wheels for Python 3.14 yet, so they try to build from source, which requires:
- Visual Studio Build Tools (C++ compiler)
- This is a large download (~3GB)

## Solution: Use Python 3.10 or 3.11

### Option 1: Install Python 3.11 (Recommended)

1. **Download Python 3.11**:
   - Go to: https://www.python.org/downloads/release/python-31111/
   - Download: "Windows installer (64-bit)"
   - **Important**: Check "Add Python to PATH" during installation

2. **Create new virtual environment with Python 3.11**:
   ```bash
   # Deactivate current environment
   deactivate
   
   # Remove old virtual environment (optional)
   rmdir /s card_id_ocr_venv
   
   # Create new one with Python 3.11
   py -3.11 -m venv card_id_ocr_venv
   
   # Activate it
   card_id_ocr_venv\Scripts\activate
   
   # Install dependencies
   pip install flask flask-cors opencv-python-headless "numpy<2.0" pytesseract Pillow
   ```

### Option 2: Quick Fix (Install Compatible Versions)

If you want to keep Python 3.14, try installing compatible versions:

```bash
# Make sure virtual environment is activated
card_id_ocr_venv\Scripts\activate

# Install with specific versions that might have wheels
pip install flask flask-cors
pip install "numpy<2.0"
pip install opencv-python-headless
pip install Pillow
pip install pytesseract
```

### Option 3: Install Visual Studio Build Tools (Not Recommended)

If you want to keep Python 3.14 and build from source:

1. Download Visual Studio Build Tools: https://visualstudio.microsoft.com/downloads/#build-tools-for-visual-studio-2022
2. Install "C++ build tools" workload (~3GB download)
3. Then try installing packages again

## Recommended Approach

**Use Python 3.11** - it's stable, well-supported, and all packages have pre-built wheels.

## Verify Installation

After installing dependencies, test:

```bash
python -c "import cv2; import numpy; import flask; print('All packages installed!')"
```

If no errors, you're good to go!

## Start OCR Service

```bash
python ocr_api.py
```

