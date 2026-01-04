# PM2 Auto-Start on Windows

## Current Status

✅ **Both services are running:**
- `tomeco-node-server` - Online
- `python-ocr-service` - Online

✅ **Configuration saved:** `pm2 save` completed

## Windows Auto-Start Setup

Since `pm2 startup` doesn't work on Windows, we need to use Windows Task Scheduler.

### Option 1: Create a Startup Script (Recommended)

Create a batch file that starts PM2 when Windows boots:

#### Create `start-pm2-services.bat`:

```batch
@echo off
cd /d "C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app"
pm2 resurrect
```

#### Add to Windows Startup:

1. Press `Win + R`
2. Type: `shell:startup`
3. Press Enter
4. Copy `start-pm2-services.bat` to this folder

### Option 2: Use Windows Task Scheduler

1. Open **Task Scheduler** (search in Start menu)
2. Click **Create Basic Task**
3. Name: "Start PM2 Services"
4. Trigger: **When the computer starts**
5. Action: **Start a program**
6. Program: `C:\Users\Charles Rosete\AppData\Roaming\npm\pm2.cmd`
7. Arguments: `resurrect`
8. Start in: `C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app`
9. Check **Run with highest privileges**
10. Click **Finish**

### Option 3: Use PM2-Windows-Startup (Third-party)

Install a package that handles Windows startup:

```bash
npm install -g pm2-windows-startup
pm2-startup install
```

## Verify Services are Running

```bash
pm2 list
```

You should see both services with status "online".

## Useful Commands

```bash
# View all services
pm2 list

# View logs
pm2 logs

# Restart all services
pm2 restart all

# Stop all services
pm2 stop all

# Delete all services
pm2 delete all

# Monitor resources
pm2 monit
```

## Manual Start (if needed)

If services don't auto-start, you can manually start them:

```bash
cd C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
pm2 start ecosystem.config.js
pm2 save
```

## Check Service Status

```bash
# Check if services are running
pm2 status

# View detailed info
pm2 show tomeco-node-server
pm2 show python-ocr-service

# View logs
pm2 logs tomeco-node-server
pm2 logs python-ocr-service
```

