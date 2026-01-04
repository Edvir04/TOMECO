@echo off
echo ========================================
echo Installing Python OCR Dependencies
echo ========================================
echo.
echo NOTE: Python 3.10 or 3.11 is recommended
echo Current Python version:
python --version
echo.
pause

REM Upgrade pip first
echo Upgrading pip...
python -m pip install --upgrade pip

echo.
echo Installing dependencies with compatible versions...
echo.

REM Install packages one by one to avoid conflicts
echo [1/6] Installing Flask...
pip install flask flask-cors

echo [2/6] Installing NumPy (compatible version)...
pip install "numpy<2.0"

echo [3/6] Installing OpenCV...
pip install opencv-python-headless

echo [4/6] Installing Pillow...
pip install Pillow

echo [5/6] Installing pytesseract...
pip install pytesseract

echo.
echo ========================================
echo Core dependencies installed!
echo ========================================
echo.
echo To test, run: python ocr_api.py
echo.
echo Optional: Install EasyOCR later (requires Visual Studio Build Tools)
echo   pip install easyocr
echo.
pause

