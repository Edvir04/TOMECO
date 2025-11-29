# Separate Server Setup Guide

This guide explains how to run the TOMECO application as two separate server instances - one for the Admin Portal and one for the Violator Portal.

## Overview

The application can run in two modes:
- **Admin Portal Server** - Serves only admin routes (authentication required)
- **Violator Portal Server** - Serves only violator/public routes (no authentication)

Each server instance runs independently on different ports or domains.

## Configuration

The application uses the `APP_PORTAL_TYPE` environment variable to determine which portal to serve:

- `APP_PORTAL_TYPE=admin` - Admin Portal Server (default)
- `APP_PORTAL_TYPE=violator` - Violator Portal Server

## Quick Start

### Windows

1. **Start Admin Server** (port 8000):
   ```cmd
   start-admin-server.bat
   ```
   Or manually:
   ```cmd
   set APP_PORTAL_TYPE=admin
   php artisan serve --port=8000
   ```
   Access at: http://localhost:8000

2. **Start Violator Server** (port 8001):
   ```cmd
   start-violator-server.bat
   ```
   Or manually:
   ```cmd
   set APP_PORTAL_TYPE=violator
   php artisan serve --port=8001
   ```
   Access at: http://localhost:8001

### Linux/Mac

1. **Start Admin Server** (port 8000):
   ```bash
   chmod +x start-admin-server.sh
   ./start-admin-server.sh
   ```
   Or manually:
   ```bash
   export APP_PORTAL_TYPE=admin
   php artisan serve --port=8000
   ```
   Access at: http://localhost:8000

2. **Start Violator Server** (port 8001):
   ```bash
   chmod +x start-violator-server.sh
   ./start-violator-server.sh
   ```
   Or manually:
   ```bash
   export APP_PORTAL_TYPE=violator
   php artisan serve --port=8001
   ```
   Access at: http://localhost:8001

## Using .env File (Recommended for Production)

For production or when you want persistent configuration, set the variable in your `.env` file:

### Admin Server .env
```env
APP_PORTAL_TYPE=admin
APP_URL=http://admin.tomeco.local:8000
# ... other configuration
```

### Violator Server .env
```env
APP_PORTAL_TYPE=violator
APP_URL=http://violator.tomeco.local:8001
# ... other configuration
```

## Production Setup

For production, you'll want to:

1. **Use different domains/subdomains:**
   - Admin: `admin.tomeco.com` or `admin.tomeco.local`
   - Violator: `violator.tomeco.com` or `tomeco.com`

2. **Configure web server (Apache/Nginx):**
   
   **For Admin Server:**
   - Point to your Laravel installation
   - Set environment variable: `APP_PORTAL_TYPE=admin`
   - Configure SSL if needed
   
   **For Violator Server:**
   - Point to your Laravel installation (can be same codebase)
   - Set environment variable: `APP_PORTAL_TYPE=violator`
   - Configure SSL if needed

3. **Example Nginx Configuration:**

   **Admin Server (admin.tomeco.local):**
   ```nginx
   server {
       listen 80;
       server_name admin.tomeco.local;
       root /path/to/tomeco_web/public;
       
       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";
       
       index index.php;
       
       charset utf-8;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
           fastcgi_hide_header X-Powered-By;
           fastcgi_param APP_PORTAL_TYPE admin;
       }
   }
   ```

   **Violator Server (violator.tomeco.local):**
   ```nginx
   server {
       listen 80;
       server_name violator.tomeco.local;
       root /path/to/tomeco_web/public;
       
       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";
       
       index index.php;
       
       charset utf-8;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
           fastcgi_hide_header X-Powered-By;
           fastcgi_param APP_PORTAL_TYPE violator;
       }
   }
   ```

## Running Both Servers Simultaneously

You can run both servers at the same time:

1. Open two terminal windows
2. In Terminal 1, run the admin server:
   ```bash
   ./start-admin-server.sh  # or start-admin-server.bat on Windows
   ```
3. In Terminal 2, run the violator server:
   ```bash
   ./start-violator-server.sh  # or start-violator-server.bat on Windows
   ```

Now you have:
- Admin Portal: http://localhost:8000
- Violator Portal: http://localhost:8001

## What Each Server Serves

### Admin Server (APP_PORTAL_TYPE=admin)
- `/admin/login` - Admin login page
- `/admin/dashboard` - Admin dashboard
- `/admin/ticket-issuance` - Ticket management
- `/admin/accounts` - Account management
- `/admin/settings` - Settings
- All routes prefixed with `/admin/*`

### Violator Server (APP_PORTAL_TYPE=violator)
- `/violator/portal` - Violator portal (main page)
- `/violator/portal/search` - Ticket search
- `/violator/payment/*` - Payment routes
- Root `/` redirects to `/violator/portal`

## Database

Both servers share the same database. Make sure both instances point to the same database configuration in their `.env` files or use the same `.env` file if running from the same codebase.

## Troubleshooting

1. **Port already in use:**
   - Change the port in the startup scripts: `--port=8002`
   - Or kill the process using the port

2. **Routes not working:**
   - Make sure `APP_PORTAL_TYPE` is set correctly
   - Clear route cache: `php artisan route:clear`
   - Clear config cache: `php artisan config:clear`

3. **Both portals showing same content:**
   - Check your `.env` file for `APP_PORTAL_TYPE`
   - Make sure you're setting the environment variable when starting the server
   - Restart the server after changing the variable

## Notes

- Both servers can run from the same codebase
- They share the same database
- Session cookies are separated by domain/port
- Each server only loads the routes it needs, improving security and performance

