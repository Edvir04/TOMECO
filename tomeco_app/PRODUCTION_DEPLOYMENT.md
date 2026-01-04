# Production Deployment Guide

## Current Setup Limitations

The current development setup requires:
- ✅ Node.js server running manually
- ✅ Python OCR service running manually
- ✅ Both services must stay running

**This works for development but needs improvements for production.**

## Production Requirements

For production, you need:

1. **Process Management** - Services should auto-start and restart on failure
2. **Reliability** - Services should recover from crashes
3. **Monitoring** - Logs and health checks
4. **Security** - Proper authentication and HTTPS
5. **Scalability** - Handle multiple requests efficiently

## Production Deployment Options

### Option 1: PM2 (Recommended for Node.js)

PM2 is a process manager for Node.js that keeps applications alive forever, reloads them without downtime, and helps manage logs.

#### Install PM2:
```bash
npm install -g pm2
```

#### Create PM2 Configuration (`ecosystem.config.js`):
```javascript
module.exports = {
  apps: [
    {
      name: 'tomeco-node-server',
      script: './server.js',
      instances: 2, // Run 2 instances for load balancing
      exec_mode: 'cluster',
      env: {
        NODE_ENV: 'production',
        PORT: 3000,
        PYTHON_OCR_HOST: 'localhost', // or your Python service IP
        PYTHON_OCR_PORT: '5000'
      },
      error_file: './logs/node-error.log',
      out_file: './logs/node-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,
      autorestart: true,
      max_memory_restart: '1G'
    },
    {
      name: 'python-ocr-service',
      script: 'python',
      args: 'ocr_api.py',
      cwd: '../Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main',
      interpreter: 'card_id_ocr_venv/Scripts/python.exe', // Windows
      // interpreter: 'card_id_ocr_venv/bin/python', // Linux/Mac
      instances: 1,
      env: {
        FLASK_ENV: 'production'
      },
      error_file: './logs/python-error.log',
      out_file: './logs/python-out.log',
      autorestart: true,
      max_memory_restart: '2G'
    }
  ]
};
```

#### Start Services:
```bash
cd tomeco_app
pm2 start ecosystem.config.js
pm2 save  # Save process list
pm2 startup  # Generate startup script
```

#### Useful PM2 Commands:
```bash
pm2 list              # List all processes
pm2 logs              # View logs
pm2 restart all       # Restart all services
pm2 stop all          # Stop all services
pm2 monit             # Monitor resources
```

### Option 2: Docker (Best for Scalability)

Docker containers make deployment easier and more consistent.

#### Create `Dockerfile` for Node.js:
```dockerfile
FROM node:18-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production
COPY . .
EXPOSE 3000
CMD ["node", "server.js"]
```

#### Create `Dockerfile` for Python OCR:
```dockerfile
FROM python:3.9-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY . .
EXPOSE 5000
CMD ["python", "ocr_api.py"]
```

#### Create `docker-compose.yml`:
```yaml
version: '3.8'

services:
  node-server:
    build: ./tomeco_app
    ports:
      - "3000:3000"
    environment:
      - NODE_ENV=production
      - PYTHON_OCR_HOST=python-ocr
      - PYTHON_OCR_PORT=5000
    depends_on:
      - python-ocr
    restart: unless-stopped

  python-ocr:
    build: ./Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main
    ports:
      - "5000:5000"
    environment:
      - FLASK_ENV=production
    restart: unless-stopped
    volumes:
      - ./ocr-models:/app/models  # Persist models
```

#### Deploy:
```bash
docker-compose up -d
```

### Option 3: Systemd (Linux Production Servers)

For Linux servers, use systemd to manage services.

#### Create `/etc/systemd/system/tomeco-node.service`:
```ini
[Unit]
Description=TOMECO Node.js Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/tomeco_app
Environment=NODE_ENV=production
Environment=PYTHON_OCR_HOST=localhost
Environment=PYTHON_OCR_PORT=5000
ExecStart=/usr/bin/node server.js
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

#### Create `/etc/systemd/system/python-ocr.service`:
```ini
[Unit]
Description=Python OCR Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main
Environment=FLASK_ENV=production
ExecStart=/var/www/Tc_ID_Card_OCR-main/Tc_ID_Card_OCR-main/venv/bin/python ocr_api.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

#### Enable and Start:
```bash
sudo systemctl enable tomeco-node
sudo systemctl enable python-ocr
sudo systemctl start tomeco-node
sudo systemctl start python-ocr
```

## Production Checklist

### Security
- [ ] Use HTTPS (SSL/TLS certificates)
- [ ] Set secure environment variables
- [ ] Enable CORS only for your domain
- [ ] Use strong authentication tokens
- [ ] Implement rate limiting
- [ ] Validate all inputs

### Performance
- [ ] Enable gzip compression
- [ ] Use reverse proxy (Nginx/Apache)
- [ ] Implement caching where appropriate
- [ ] Optimize database queries
- [ ] Use CDN for static assets

### Monitoring
- [ ] Set up error logging (Winston, Morgan)
- [ ] Monitor server resources (CPU, Memory)
- [ ] Set up health check endpoints
- [ ] Configure alerts for failures
- [ ] Regular backups

### Configuration
- [ ] Use environment variables for all configs
- [ ] Set proper NODE_ENV=production
- [ ] Configure database connection pooling
- [ ] Set up proper logging levels

## Environment Variables for Production

Create `.env.production`:

```env
# Node.js Server
NODE_ENV=production
PORT=3000
PYTHON_OCR_HOST=localhost
PYTHON_OCR_PORT=5000

# Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=capstone_gigs
DB_USER=postgres
DB_PASSWORD=your_secure_password

# Security
JWT_SECRET=your_very_secure_secret_key
SESSION_SECRET=your_session_secret
```

## Reverse Proxy Setup (Nginx)

```nginx
# Node.js Server
server {
    listen 80;
    server_name api.tomeco.com;
    
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}

# Python OCR Service (internal only)
server {
    listen 80;
    server_name ocr.tomeco.com;
    
    # Only allow from localhost or Node.js server
    allow 127.0.0.1;
    allow 192.168.1.0/24;  # Your internal network
    deny all;
    
    location / {
        proxy_pass http://localhost:5000;
    }
}
```

## Health Check Endpoints

Add to your Node.js server:

```javascript
// Health check endpoint
app.get('/health', async (req, res) => {
  try {
    // Check Python OCR service
    const pythonHealth = await fetch(`http://${PYTHON_OCR_HOST}:${PYTHON_OCR_PORT}/health`);
    const pythonStatus = pythonHealth.ok ? 'healthy' : 'unhealthy';
    
    res.json({
      status: 'healthy',
      nodejs: 'running',
      python_ocr: pythonStatus,
      timestamp: new Date().toISOString()
    });
  } catch (error) {
    res.status(503).json({
      status: 'unhealthy',
      error: error.message
    });
  }
});
```

## Recommended Production Architecture

```
┌─────────────┐
│   Mobile    │
│    App      │
└──────┬──────┘
       │ HTTPS
       ▼
┌─────────────┐
│   Nginx     │ (Reverse Proxy + SSL)
└──────┬──────┘
       │
       ├─────────► Node.js Server (Port 3000)
       │           - Authentication
       │           - API Endpoints
       │
       └─────────► Python OCR (Port 5000)
                   - OCR Processing
                   - Image Analysis
```

## Quick Start for Production

### Using PM2 (Easiest):

1. **Install PM2:**
   ```bash
   npm install -g pm2
   ```

2. **Create ecosystem.config.js** (see above)

3. **Start services:**
   ```bash
   pm2 start ecosystem.config.js
   pm2 save
   pm2 startup
   ```

4. **Services will auto-start on server reboot**

## Conclusion

**Yes, the Python OCR service can work in production**, but you need:
- ✅ Process management (PM2, Docker, or systemd)
- ✅ Auto-restart on failure
- ✅ Proper logging
- ✅ Health monitoring
- ✅ Security hardening

**Recommended approach:** Use PM2 for quick deployment or Docker for better scalability and isolation.

