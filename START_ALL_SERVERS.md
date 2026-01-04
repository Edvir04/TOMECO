# Complete Server Startup Guide - TOMECO Project

This guide provides instructions for starting **all servers** required for the TOMECO project.

## Overview of All Servers

1. **tomeco_app** - Node.js API Server (Port 3000)
2. **tomeco_web** - Laravel Web Application
   - Admin Portal (Port 8000)
   - Violator Portal (Port 8001)
3. **Python OCR Service** - Flask OCR API (Port 5000)

---

## Quick Start - All Servers

You'll need **4 separate Command Prompt windows** to run all servers simultaneously.

---

## TERMINAL 1: Node.js API Server (tomeco_app)

**Purpose:** Mobile app backend API server

### Commands:
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
npm run server
```

**Or manually:**
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
node server.js
```

**Expected Output:**
```
Server is running on port 3000
Server accessible at:
  - http://localhost:3000
  - http://127.0.0.1:3000
  - Use your local network IP for mobile devices

API Endpoints:
  - GET  /health (Health check for production)
  - POST /api/mobile/login
  - GET  /api/mobile/profile
  - POST /api/mobile/ocr/scan-id
  - GET  /api/diagnostics
```

**Access:** http://localhost:3000

---

## TERMINAL 2: Laravel Admin Portal (tomeco_web)

**Purpose:** Admin dashboard and management portal

### Commands:
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-admin-server.bat
```

**Or manually:**
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
set APP_PORTAL_TYPE=admin
php artisan serve --port=8000
```

**Expected Output:**
```
Starting TOMECO Admin Portal Server...
Portal Type: ADMIN
Server will be available at: http://localhost:8000
   INFO  Server running on [http://127.0.0.1:8000].
```

**Access:** http://localhost:8000

---

## TERMINAL 3: Laravel Violator Portal (tomeco_web)

**Purpose:** Public violator portal for ticket viewing/payment

### Commands:
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-violator-server.bat
```

**Or manually:**
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
set APP_PORTAL_TYPE=violator
php artisan serve --port=8001
```

**Expected Output:**
```
Starting TOMECO Violator Portal Server...
Portal Type: VIOLATOR
Server will be available at: http://localhost:8001
   INFO  Server running on [http://127.0.0.1:8001].
```

**Access:** http://localhost:8001

---

## TERMINAL 4: Python OCR Service

**Purpose:** ID card OCR processing service

### Commands:
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main
card_id_ocr_venv\Scripts\activate
python ocr_api.py
```

**Or with full path:**
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main
C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main\card_id_ocr_venv\Scripts\python.exe ocr_api.py
```

**Expected Output:**
```
 * Serving Flask app 'ocr_api'
 * Running on http://0.0.0.0:5000
 * Debug mode: on
```

**Access:** http://localhost:5000

---

## Complete Startup Sequence

### Option 1: Manual (Step by Step)

Open **4 Command Prompt windows** and run in order:

**Window 1 - Node.js Server:**
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
npm run server
```

**Window 2 - Admin Portal:**
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-admin-server.bat
```

**Window 3 - Violator Portal:**
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-violator-server.bat
```

**Window 4 - Python OCR:**
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main
card_id_ocr_venv\Scripts\activate
python ocr_api.py
```

### Option 2: Using PM2 (Advanced - Recommended for Production)

If you have PM2 installed, you can start Node.js and Python services together:

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
pm2 start ecosystem.config.js
```

This will start:
- Node.js server (port 3000)
- Python OCR service (port 5000)

Then you still need to start the Laravel servers separately (Terminal 2 & 3 above).

**PM2 Commands:**
```cmd
pm2 list              # View all processes
pm2 logs              # View logs
pm2 restart all       # Restart all services
pm2 stop all          # Stop all services
pm2 monit             # Monitor resources
```

---

## Server Ports Summary

| Server | Port | URL | Purpose |
|--------|------|-----|---------|
| Node.js API | 3000 | http://localhost:3000 | Mobile app backend |
| Laravel Admin | 8000 | http://localhost:8000 | Admin dashboard |
| Laravel Violator | 8001 | http://localhost:8001 | Public violator portal |
| Python OCR | 5000 | http://localhost:5000 | ID card OCR service |

---

## Verifying All Servers Are Running

### Check Ports:
```cmd
netstat -ano | findstr ":3000"
netstat -ano | findstr ":5000"
netstat -ano | findstr ":8000"
netstat -ano | findstr ":8001"
```

### Health Check Endpoints:

1. **Node.js Server:**
   ```
   http://localhost:3000/health
   ```

2. **Python OCR Service:**
   ```
   http://localhost:5000/health
   ```

3. **Laravel Servers:**
   - Admin: http://localhost:8000
   - Violator: http://localhost:8001

---

## Troubleshooting

### Port Already in Use

If a port is already in use:

1. Find the process using the port:
   ```cmd
   netstat -ano | findstr :3000
   ```

2. Kill the process (replace PID with the number from above):
   ```cmd
   taskkill /PID <PID> /F
   ```

### Python Virtual Environment Not Found

Make sure the virtual environment exists:
```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main
dir card_id_ocr_venv
```

If it doesn't exist, create it:
```cmd
python -m venv card_id_ocr_venv
card_id_ocr_venv\Scripts\activate
pip install -r requirements.txt
```

### Node.js Dependencies Not Installed

```cmd
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
npm install
```

### PHP Not Found

Make sure PHP is in your PATH:
```cmd
php -v
```

If not found, use full path (adjust for your PHP installation):
```cmd
C:\xampp\php\php.exe artisan serve --port=8000
```

### Database Connection Issues

Ensure PostgreSQL is running and accessible:
- Default config in `server.js`: `localhost:5432`, database: `capstone_gigs`
- Verify database credentials match your setup

---

## Stopping All Servers

Press `Ctrl + C` in each Command Prompt window where a server is running.

**For PM2:**
```cmd
pm2 stop all
pm2 delete all
```

---

## Production Deployment on Render

For production deployment, the application is configured to run on Render. See the `render.yaml` file for deployment configuration.

### Environment Variables Required:

- `API_BASE_URL` - Node.js API server URL (e.g., `https://tomeco-api.onrender.com`)
- `LARAVEL_BASE_URL` - Laravel backend URL (e.g., `https://tomeco-web.onrender.com`)
- `VIOLATOR_PORTAL_URL` - Violator portal URL (e.g., `https://tomeco-web.onrender.com/violator/portal`)

---

## Production Deployment

For production, consider using:
- **PM2** for Node.js and Python services
- **Laravel Queue Workers** for background jobs
- **Nginx/Apache** as reverse proxy
- **Process managers** for auto-restart on failure

See `tomeco_app/PRODUCTION_DEPLOYMENT.md` for detailed production setup.

---

## Quick Reference Card

```cmd
REM ============================================
REM TERMINAL 1: Node.js Server
REM ============================================
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
npm run server

REM ============================================
REM TERMINAL 2: Laravel Admin Portal
REM ============================================
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-admin-server.bat

REM ============================================
REM TERMINAL 3: Laravel Violator Portal
REM ============================================
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_web
start-violator-server.bat

REM ============================================
REM TERMINAL 4: Python OCR Service
REM ============================================
cd C:\Users\Charles Rosete\Capstone_Gigs\Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main
card_id_ocr_venv\Scripts\activate
python ocr_api.py
```

---

**Last Updated:** Based on current project structure
**All servers must be running for the application to function correctly.**

