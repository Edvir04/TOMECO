@echo off
REM PM2 Services Auto-Start Script for Windows
REM This script will restore PM2 services on Windows startup

cd /d "%~dp0"
echo Starting PM2 services...
pm2 resurrect
echo PM2 services restored.
pause

