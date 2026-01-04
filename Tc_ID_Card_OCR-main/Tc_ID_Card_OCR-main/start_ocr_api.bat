@echo off
echo Starting Python OCR API Service...
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python 3.8 or higher
    pause
    exit /b 1
)

REM Check if virtual environment exists
if not exist "card_id_ocr_venv" (
    echo Creating Python virtual environment...
    python -m venv card_id_ocr_venv
)

REM Activate virtual environment
call card_id_ocr_venv\Scripts\activate.bat

REM Install/upgrade dependencies
echo Installing dependencies...
pip install -r requirements_api.txt

REM Start Flask API
echo.
echo Starting Flask OCR API on http://0.0.0.0:5000
echo Press Ctrl+C to stop the server
echo.
python ocr_api.py

pause

