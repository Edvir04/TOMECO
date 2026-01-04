#!/bin/bash
# Start Violator Server Instance
# This script starts the Laravel server for the Violator Portal on port 8001

echo "Starting TOMECO Violator Portal Server..."
echo "Portal Type: VIOLATOR"
echo "Server will be available at: http://localhost:8001"

# Set environment variable for violator portal
export APP_PORTAL_TYPE=violator

# Start Laravel development server (bind to all interfaces for mobile access)
php artisan serve --port=8001 --host=0.0.0.0

