# How to Run Servers in CMD (Windows)

## Quick Start - Using the Batch Files

### Option 1: Double-click the batch files

1. **For Admin Server:**
   - Double-click `start-admin-server.bat`
   - The server will start on port 8000
   - Access at: http://localhost:8000

2. **For Violator Server:**
   - Double-click `start-violator-server.bat`
   - The server will start on port 8001
   - Access at: http://localhost:8001

### Option 2: Run from Command Prompt

1. Open **Command Prompt** (cmd.exe)

2. Navigate to your project directory:
   ```cmd
   cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
   ```

3. **Start Admin Server:**
   ```cmd
   start-admin-server.bat
   ```
   
   Or manually:
   ```cmd
   set APP_PORTAL_TYPE=admin
   php artisan serve --port=8000
   ```

4. **Start Violator Server** (open a NEW Command Prompt window):
   ```cmd
   cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
   start-violator-server.bat
   ```
   
   Or manually:
   ```cmd
   set APP_PORTAL_TYPE=violator
   php artisan serve --port=8001
   ```

## Running Both Servers at the Same Time

### Method 1: Two Separate Command Prompt Windows

1. **Open First Command Prompt:**
   - Run: `start-admin-server.bat`
   - Keep this window open
   - Admin server runs on port 8000

2. **Open Second Command Prompt:**
   - Run: `start-violator-server.bat`
   - Keep this window open
   - Violator server runs on port 8001

### Method 2: Using Start Command (Same Window)

In one Command Prompt window:

```cmd
start "Admin Server" cmd /k "set APP_PORTAL_TYPE=admin && php artisan serve --port=8000"
start "Violator Server" cmd /k "set APP_PORTAL_TYPE=violator && php artisan serve --port=8001"
```

This will open two new windows, one for each server.

## Manual Commands (Step by Step)

### Admin Server Setup:

```cmd
REM Navigate to project folder
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web

REM Set environment variable
set APP_PORTAL_TYPE=admin

REM Start the server
php artisan serve --port=8000
```

### Violator Server Setup:

```cmd
REM Navigate to project folder (in a NEW cmd window)
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web

REM Set environment variable
set APP_PORTAL_TYPE=violator

REM Start the server
php artisan serve --port=8001
```

## Verify Servers Are Running

After starting both servers, you should see:

**Admin Server Output:**
```
Starting TOMECO Admin Portal Server...
Portal Type: ADMIN
Server will be available at: http://localhost:8000
   INFO  Server running on [http://127.0.0.1:8000].
```

**Violator Server Output:**
```
Starting TOMECO Violator Portal Server...
Portal Type: VIOLATOR
Server will be available at: http://localhost:8001
   INFO  Server running on [http://127.0.0.1:8001].
```

## Access the Portals

- **Admin Portal:** Open browser and go to `http://localhost:8000`
- **Violator Portal:** Open browser and go to `http://localhost:8001`

## Troubleshooting

### Port Already in Use Error

If you get "Port 8000 is already in use" or similar:

1. Find what's using the port:
   ```cmd
   netstat -ano | findstr :8000
   ```

2. Kill the process (replace PID with the number from above):
   ```cmd
   taskkill /PID <PID> /F
   ```

3. Or use a different port:
   ```cmd
   php artisan serve --port=8002
   ```

### PHP Not Found Error

Make sure PHP is in your PATH:
```cmd
php -v
```

If not found, add PHP to your system PATH or use full path:
```cmd
C:\xampp\php\php.exe artisan serve --port=8000
```

### Environment Variable Not Working

If the portal type isn't being recognized:

1. Make sure you're setting it in the same command prompt session
2. Or add it to your `.env` file:
   ```env
   APP_PORTAL_TYPE=admin
   ```
   (Then restart the server)

### Stop the Servers

Press `Ctrl + C` in the Command Prompt window where the server is running.

