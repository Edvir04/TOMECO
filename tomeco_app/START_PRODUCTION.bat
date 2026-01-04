@echo off
REM Production Deployment Script for TOMECO App
REM This script starts all services needed for production

echo ========================================
echo   TOMECO Production Deployment
echo ========================================
echo.

REM Check if PM2 is installed
where pm2 >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: PM2 is not installed!
    echo Please install PM2: npm install -g pm2
    pause
    exit /b 1
)

REM Navigate to project directory
cd /d "%~dp0"

echo [1/3] Starting PM2 services...
pm2 start ecosystem.config.js --env production
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to start PM2 services!
    pause
    exit /b 1
)

echo.
echo [2/3] Saving PM2 configuration...
pm2 save

echo.
echo [3/3] Checking service status...
pm2 list

echo.
echo ========================================
echo   Services Started!
echo ========================================
echo.
echo Next steps:
echo 1. Start ngrok tunnels (if using ngrok)
echo 2. Build APK: npx eas-cli build --platform android --profile production
echo 3. Install APK on devices
echo.
echo View logs: pm2 logs
echo Stop services: pm2 stop all
echo.
pause

