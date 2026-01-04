@echo off
echo Installing missing dependencies...
echo.

REM Make sure virtual environment is activated
if not defined VIRTUAL_ENV (
    echo Activating virtual environment...
    call card_id_ocr_venv\Scripts\activate.bat
)

echo Installing matplotlib...
pip install matplotlib

echo.
echo Checking for other missing dependencies...
python -c "import utlis" 2>&1 | findstr /i "ModuleNotFoundError"
if errorlevel 1 (
    echo All dependencies are installed!
) else (
    echo Some dependencies may still be missing.
    echo Check the error messages above.
)

pause

