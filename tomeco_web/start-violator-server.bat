@echo off
REM Start Violator Server Instance
REM This script starts the Laravel server for the Violator Portal on port 8001

echo Starting TOMECO Violator Portal Server...
echo Portal Type: VIOLATOR
echo Server will be available at: http://localhost:8001

REM Set environment variable for violator portal
set APP_PORTAL_TYPE=violator

REM Start Laravel development server
php artisan serve --port=8001 --host=127.0.0.1

pause

