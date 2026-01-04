@echo off
echo ========================================
echo Installing Python OCR Dependencies
echo ========================================
echo.
echo Checking Python version...
python --version
echo.
echo NOTE: Python 3.10 or 3.11 is recommended for best compatibility
echo If you encounter build errors, consider using Python 3.11
echo.
pause

REM Upgrade pip first
echo Upgrading pip...
python -m pip install --upgrade pip

echo.
echo Installing core dependencies (without EasyOCR)...
echo Installing with compatible versions for better success rate...
echo.

REM Install packages one by one with compatible versions
pip install flask flask-cors
pip install "numpy<2.0"
pip install opencv-python-headless
pip install Pillow
pip install pytesseract

if errorlevel 1 (
    echo.
    echo ERROR: Failed to install core dependencies
    pause
    exit /b 1
)

echo.
echo ========================================
echo Core dependencies installed successfully!
echo ========================================
echo.
echo Optional: Install EasyOCR for better accuracy (this may take 5-10 minutes)
echo   pip install easyocr
echo.
echo To start the OCR API service, run:
echo   python ocr_api.py
echo.
pause

