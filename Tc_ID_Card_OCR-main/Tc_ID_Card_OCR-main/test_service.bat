@echo off
echo ========================================
echo Testing Python OCR Service
echo ========================================
echo.

echo [1/3] Testing health endpoint...
curl http://localhost:5000/health
echo.
echo.

if errorlevel 1 (
    echo ERROR: Service is not responding!
    echo Make sure the service is running:
    echo   python ocr_api.py
    echo.
    pause
    exit /b 1
)

echo [2/3] Service is running! ✓
echo.

echo [3/3] To test OCR with an image:
echo   curl -X POST -F "image=@C:\FULL\PATH\TO\YOUR\IMAGE.jpg" http://localhost:5000/ocr/scan-id
echo.
echo   Example:
echo   curl -X POST -F "image=@C:\Users\Charles Rosete\Desktop\id_card.jpg" http://localhost:5000/ocr/scan-id
echo.
echo   Or test from mobile app (recommended)!
echo.

pause

