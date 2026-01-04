@echo off
echo ========================================
echo Python OCR API - Installation Script
echo ========================================
echo.

REM Check if virtual environment exists
if not exist "card_id_ocr_venv" (
    echo Creating Python virtual environment...
    python -m venv card_id_ocr_venv
    if errorlevel 1 (
        echo ERROR: Failed to create virtual environment
        echo Please ensure Python is installed and in PATH
        pause
        exit /b 1
    )
    echo Virtual environment created successfully!
    echo.
)

REM Activate virtual environment
echo Activating virtual environment...
call card_id_ocr_venv\Scripts\activate.bat

REM Install dependencies
echo.
echo Installing Python dependencies...
echo This may take several minutes, especially for EasyOCR...
echo.
pip install --upgrade pip
pip install -r requirements_api.txt

if errorlevel 1 (
    echo.
    echo ERROR: Failed to install dependencies
    echo Please check the error messages above
    pause
    exit /b 1
)

echo.
echo ========================================
echo Installation completed successfully!
echo ========================================
echo.
echo To start the OCR API service, run:
echo   start_ocr_api.bat
echo.
pause

