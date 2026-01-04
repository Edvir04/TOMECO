@echo off
REM Start Admin Server Instance for Mobile App Access
REM This script starts the Laravel server accessible from mobile devices on the network

echo Starting TOMECO Admin Portal Server for Mobile Access...
echo Portal Type: ADMIN
echo Server will be available at: http://0.0.0.0:8000
echo.
echo To access from mobile device, use your computer's IP address:
echo Example: http://192.168.1.5:8000
echo.

REM Set environment variable for admin portal
set APP_PORTAL_TYPE=admin

REM Start Laravel development server (bind to all network interfaces)
php artisan serve --host=0.0.0.0 --port=8000

pause

