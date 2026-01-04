#!/bin/bash
# Start Admin Server Instance
# This script starts the Laravel server for the Admin Portal on port 8000

echo "Starting TOMECO Admin Portal Server..."
echo "Portal Type: ADMIN"
echo "Server will be available at: http://localhost:8000"

# Set environment variable for admin portal
export APP_PORTAL_TYPE=admin

# Start Laravel development server (localhost only)
php artisan serve --host=127.0.0.1 --port=8000

