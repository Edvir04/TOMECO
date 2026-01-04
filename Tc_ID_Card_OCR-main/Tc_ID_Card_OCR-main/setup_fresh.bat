@echo off
echo ========================================
echo Python OCR API - Fresh Setup
echo ========================================
echo.
echo Detected Python version:
py --version
echo.
echo This script will:
echo 1. Remove old virtual environment (if exists)
echo 2. Create new virtual environment with Python 3.11
echo 3. Install all dependencies
echo.
pause

REM Navigate to script directory
cd /d "%~dp0"

REM Step 1: Remove old virtual environment
if exist card_id_ocr_venv (
    echo Removing old virtual environment...
    rmdir /s /q card_id_ocr_venv
    echo Done.
    echo.
)

REM Step 2: Create new virtual environment
echo Creating new virtual environment with Python 3.11...
py -3.11 -m venv card_id_ocr_venv
if errorlevel 1 (
    echo ERROR: Failed to create virtual environment
    echo Make sure Python 3.11 is installed
    pause
    exit /b 1
)
echo Virtual environment created!
echo.

REM Step 3: Activate and upgrade pip
echo Activating virtual environment...
call card_id_ocr_venv\Scripts\activate.bat

echo Upgrading pip...
python -m pip install --upgrade pip
echo.

REM Step 4: Install dependencies
echo Installing dependencies...
echo This may take a few minutes...
echo.
pip install flask flask-cors opencv-python-headless "numpy<2.0" pytesseract Pillow

if errorlevel 1 (
    echo.
    echo ERROR: Failed to install dependencies
    echo Try installing packages one by one:
    echo   pip install flask
    echo   pip install flask-cors
    echo   pip install "numpy<2.0"
    echo   pip install opencv-python-headless
    echo   pip install Pillow
    echo   pip install pytesseract
    pause
    exit /b 1
)

echo.
echo ========================================
echo Installation completed successfully!
echo ========================================
echo.
echo Testing installation...
python -c "import cv2; import numpy; import flask; print('✓ All packages installed successfully!')"

if errorlevel 1 (
    echo WARNING: Some packages may not be installed correctly
) else (
    echo.
    echo ========================================
    echo Ready to start OCR service!
    echo ========================================
    echo.
    echo To start the service, run:
    echo   python ocr_api.py
    echo.
    echo Or use: start_ocr_api.bat
    echo.
)

pause

