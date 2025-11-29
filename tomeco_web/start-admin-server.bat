@echo off
REM Start Admin Server Instance
REM This script starts the Laravel server for the Admin Portal on port 8000

echo Starting TOMECO Admin Portal Server...
echo Portal Type: ADMIN
echo Server will be available at: http://localhost:8000

REM Set environment variable for admin portal
set APP_PORTAL_TYPE=admin

REM Start Laravel development server
php artisan serve --port=8000

pause

