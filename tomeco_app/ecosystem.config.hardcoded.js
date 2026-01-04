// Alternative configuration with hardcoded absolute paths
// Use this if path.resolve() doesn't work with PM2

module.exports = {
  apps: [
    {
      name: 'tomeco-node-server',
      script: './server.js',
      instances: 1,
      exec_mode: 'fork',
      env: {
        NODE_ENV: 'development',
        PORT: 3000,
        PYTHON_OCR_HOST: '192.168.1.10',
        PYTHON_OCR_PORT: '5000'
      },
      env_production: {
        NODE_ENV: 'production',
        PORT: 3000,
        PYTHON_OCR_HOST: 'localhost',
        PYTHON_OCR_PORT: '5000'
      },
      error_file: './logs/node-error.log',
      out_file: './logs/node-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      min_uptime: '10s',
      max_restarts: 10
    },
    {
      name: 'python-ocr-service',
      script: 'ocr_api.py',
      cwd: 'C:\\Users\\Charles Rosete\\Capstone_Gigs\\Tc_ID_Card_OCR-main\\Tc_ID_Card_OCR-main',
      interpreter: 'C:\\Users\\Charles Rosete\\Capstone_Gigs\\Tc_ID_Card_OCR-main\\Tc_ID_Card_OCR-main\\card_id_ocr_venv\\Scripts\\python.exe',
      instances: 1,
      exec_mode: 'fork',
      env: {
        FLASK_ENV: 'development'
      },
      env_production: {
        FLASK_ENV: 'production'
      },
      error_file: 'C:\\Users\\Charles Rosete\\Capstone_Gigs\\tomeco_app\\logs\\python-error.log',
      out_file: 'C:\\Users\\Charles Rosete\\Capstone_Gigs\\tomeco_app\\logs\\python-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      autorestart: true,
      watch: false,
      max_memory_restart: '2G',
      min_uptime: '10s',
      max_restarts: 10
    }
  ]
};

