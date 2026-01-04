@echo off
REM Quick Deploy Script for TOMECO App with Expo Go
REM This script helps you start everything needed for deployment

echo ========================================
echo TOMECO App - Quick Deploy with Expo Go
echo ========================================
echo.

echo [1/5] Checking prerequisites...
where node >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Node.js not found! Please install Node.js first.
    pause
    exit /b 1
)

where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: PHP not found! Backend server may not start.
)

where ngrok >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: ngrok not found! Make sure ngrok is in your PATH.
)

echo.
echo [2/5] Installing/updating dependencies...
cd /d "%~dp0"
call npm install
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: npm install failed!
    pause
    exit /b 1
)

echo.
echo [3/5] Checking API configuration...
echo Make sure config/api.js has USE_NGROK = true for Expo Go
echo.

echo [4/5] Starting Expo development server...
echo.
echo ========================================
echo IMPORTANT: Before starting Expo:
echo ========================================
echo 1. Start Admin Server (port 8000):
echo    cd ..\tomeco_web
echo    set APP_PORTAL_TYPE=admin
echo    php artisan serve --port=8000
echo.
echo 2. Start ngrok tunnels:
echo    cd ..\tomeco_web
echo    start-ngrok-both.bat
echo.
echo 3. Test API endpoint:
echo    curl https://Tomeco.ngrok.dev/api/mobile/health
echo.
echo ========================================
echo.
pause

echo.
echo [5/5] Starting Expo...
echo.
echo Scan the QR code with Expo Go app on your phone!
echo.
npx expo start

pause

