#!/bin/bash
# Simple serve command that runs on 0.0.0.0 (all interfaces)

echo "Starting Laravel development server..."
echo "Server will be available at:"
echo "  - http://localhost:8000"
echo "  - http://127.0.0.1:8000"
echo "  - http://[YOUR_LOCAL_IP]:8000 (from other devices)"

# Start Laravel development server on all interfaces
php artisan serve --host=0.0.0.0 --port=8000

