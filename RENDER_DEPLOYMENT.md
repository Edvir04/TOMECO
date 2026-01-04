# Render Deployment Guide

This guide explains how to deploy the TOMECO application to Render.

## Overview

The application consists of 4 services, all containerized with Docker:
1. **Node.js API Server** (tomeco-api) - Port 3000
2. **Laravel Admin Portal** (tomeco-web-admin) - Port 8000 (Docker)
3. **Laravel Violator Portal** (tomeco-web-violator) - Port 8001 (Docker)
4. **Python OCR Service** (tomeco-ocr) - Port 5000 (Docker)

Plus a PostgreSQL database.

All services use Docker for consistent deployment and better control over the runtime environment.

## Prerequisites

1. A Render account (sign up at https://render.com)
2. Your code pushed to a Git repository (GitHub, GitLab, or Bitbucket)

## Deployment Steps

### 1. Connect Your Repository

1. Log in to Render Dashboard
2. Click "New +" → "Blueprint"
3. Connect your Git repository
4. Render will detect the `render.yaml` file automatically

### 2. Configure Environment Variables

After the services are created, you need to set up environment variables:

#### For Node.js API (tomeco-api):

- `DB_USER` - PostgreSQL username (from database service)
- `DB_HOST` - PostgreSQL host (from database service)
- `DB_NAME` - Database name (usually `capstone_gigs`)
- `DB_PASSWORD` - PostgreSQL password (from database service)
- `DB_PORT` - PostgreSQL port (usually `5432`)
- `DB_SSL` - Set to `true` for Render PostgreSQL

#### For Laravel Services (tomeco-web-admin and tomeco-web-violator):

- `DB_CONNECTION` - Set to `pgsql`
- `DB_HOST` - PostgreSQL host (from database service)
- `DB_PORT` - PostgreSQL port (usually `5432`)
- `DB_DATABASE` - Database name (usually `capstone_gigs`)
- `DB_USERNAME` - PostgreSQL username (from database service)
- `DB_PASSWORD` - PostgreSQL password (from database service)
- `APP_KEY` - Generate with: `php artisan key:generate` (copy the key)
- `APP_URL` - Will be auto-set from service URL
- `VIOLATOR_PORTAL_URL` - Will be auto-set from violator service URL

### 3. Database Setup

1. After the database is created, note the connection details
2. Run migrations on one of the Laravel services:
   - Go to the service shell/console
   - Run: `cd tomeco_web && php artisan migrate`

### 4. Update Mobile App Configuration

Update `tomeco_app/config/api.js` with your Render URLs:

```javascript
const PRODUCTION_API_URL = 'https://tomeco-api.onrender.com';
const PRODUCTION_LARAVEL_URL = 'https://tomeco-web-admin.onrender.com';
```

Or create a production config file and use it in your build process.

## Service URLs

After deployment, your services will be available at:
- Node.js API: `https://tomeco-api.onrender.com`
- Admin Portal: `https://tomeco-web-admin.onrender.com`
- Violator Portal: `https://tomeco-web-violator.onrender.com`
- OCR Service: `https://tomeco-ocr.onrender.com`

## Important Notes

1. **Free Tier Limitations**: 
   - Services spin down after 15 minutes of inactivity
   - First request after spin-down may take 30-60 seconds
   - Consider upgrading to paid plans for production

2. **Database Connections**:
   - Use the internal database hostname provided by Render
   - Enable SSL connections (DB_SSL=true)

3. **Environment Variables**:
   - Some variables are auto-synced from the database service
   - Set sensitive values (passwords, API keys) manually

4. **CORS Configuration**:
   - Update Laravel CORS config to allow your mobile app domain
   - Update Node.js CORS to allow your mobile app domain

5. **File Storage**:
   - Consider using Render's disk storage or external storage (S3) for uploads
   - Local file storage is ephemeral on Render

## Troubleshooting

### Services not starting

- Check build logs for errors
- Verify all environment variables are set
- Check that database is accessible

### Database connection errors

- Verify database credentials
- Check that DB_SSL is set to `true`
- Ensure database hostname is correct (use internal hostname)

### OCR service not working

- Check that Python OCR service is running
- Verify PYTHON_OCR_HOST environment variable points to the OCR service URL
- Check OCR service logs for errors

### Laravel errors

- Check Docker build logs for any build failures
- Ensure APP_KEY is set in environment variables
- Verify database migrations have been run
- Check that storage and bootstrap/cache directories have proper permissions
- If config cache issues occur, the Dockerfile will rebuild caches automatically

## Updating the Deployment

1. Push changes to your Git repository
2. Render will automatically detect changes and redeploy
3. Or manually trigger a deploy from the Render dashboard

## Monitoring

- Check service logs in the Render dashboard
- Set up health check endpoints (already configured)
- Monitor service uptime and response times

