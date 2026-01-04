const path = require('path');

module.exports = {
  apps: [
    {
      name: 'tomeco-node-server',
      script: './server.js',
      instances: 1, // Start with 1, increase for load balancing
      exec_mode: 'fork', // Use 'fork' for single instance, 'cluster' for multiple
      env: {
        NODE_ENV: 'development',
        PORT: 3000,
        PYTHON_OCR_HOST: 'localhost', // Use localhost since both services run on same machine
        PYTHON_OCR_PORT: '5000'
      },
      env_production: {
        NODE_ENV: 'production',
        PORT: 3000,
        PYTHON_OCR_HOST: 'localhost', // Use localhost in production if on same server
        PYTHON_OCR_PORT: '5000'
      },
      error_file: './logs/node-error.log',
      out_file: './logs/node-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,
      autorestart: true,
      watch: false, // Set to true for development auto-reload
      max_memory_restart: '1G',
      // Restart if server crashes
      min_uptime: '30s', // Increased to 30 seconds
      max_restarts: 15, // Increased restart limit
      restart_delay: 4000 // Wait 4 seconds before restarting
    },
    {
      name: 'python-ocr-service',
      script: 'ocr_api.py',
      cwd: path.resolve(__dirname, '../Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main'),
      // Windows - Use absolute path
      interpreter: path.resolve(__dirname, '../Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main/card_id_ocr_venv/Scripts/python.exe'),
      // Linux/Mac (uncomment if using Linux/Mac)
      // interpreter: path.resolve(__dirname, '../Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main/card_id_ocr_venv/bin/python'),
      instances: 1,
      exec_mode: 'fork',
      env: {
        FLASK_ENV: 'development'
      },
      env_production: {
        FLASK_ENV: 'production'
      },
      error_file: path.resolve(__dirname, './logs/python-error.log'),
      out_file: path.resolve(__dirname, './logs/python-out.log'),
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      autorestart: true,
      watch: false,
      max_memory_restart: '2G',
      min_uptime: '30s', // Increased to 30 seconds
      max_restarts: 15, // Increased restart limit
      restart_delay: 4000 // Wait 4 seconds before restarting
    }
  ]
};

