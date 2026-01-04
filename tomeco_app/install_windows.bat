@echo off
echo Removing old node_modules and package-lock.json...
if exist node_modules rmdir /s /q node_modules
if exist package-lock.json del /q package-lock.json
echo.
echo Installing dependencies...
call npm install
echo.
echo Installing babel-preset-expo if needed...
call npm install --save-dev babel-preset-expo
echo.
echo Done! You can now run: npm start
pause

