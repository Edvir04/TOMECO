# PM2 Setup Guide

## File Location

The `ecosystem.config.js` file should be placed in:
```
tomeco_app/
├── ecosystem.config.js  ← HERE (same folder as server.js)
├── server.js
├── package.json
└── ...
```

## Quick Start

### 1. Install PM2
```bash
npm install -g pm2
```

### 2. Create Logs Directory
```bash
cd tomeco_app
mkdir logs
```

### 3. Update Configuration

Edit `ecosystem.config.js` and update:
- `PYTHON_OCR_HOST`: Your Python OCR service IP (currently `192.168.1.10`)
- `cwd`: Path to your Python OCR directory
- `interpreter`: Path to Python virtual environment

### 4. Start Services

```bash
cd tomeco_app
pm2 start ecosystem.config.js
```

### 5. Save Configuration

Save the process list so PM2 remembers it:
```bash
pm2 save
```

### 6. Setup Auto-Start on Boot

Generate startup script (run as administrator/sudo):
```bash
pm2 startup
```

Follow the instructions it provides. This will make services start automatically when your computer/server boots.

## Useful Commands

```bash
# View all running services
pm2 list

# View logs
pm2 logs

# View logs for specific service
pm2 logs tomeco-node-server
pm2 logs python-ocr-service

# Restart all services
pm2 restart all

# Restart specific service
pm2 restart tomeco-node-server

# Stop all services
pm2 stop all

# Stop specific service
pm2 stop python-ocr-service

# Delete services from PM2
pm2 delete all

# Monitor resources (CPU, Memory)
pm2 monit

# View detailed info
pm2 show tomeco-node-server
```

## Configuration Notes

### For Windows:
- Use `card_id_ocr_venv/Scripts/python.exe` as interpreter
- Make sure Python virtual environment path is correct

### For Linux/Mac:
- Use `card_id_ocr_venv/bin/python` as interpreter
- Update the `cwd` path if different

### Adjusting Instances:
- Start with `instances: 1` for testing
- Increase to `instances: 2` or more for load balancing
- Change `exec_mode` to `'cluster'` for multiple instances

## Troubleshooting

### Services not starting:
1. Check logs: `pm2 logs`
2. Verify paths in `ecosystem.config.js`
3. Test Python service manually first

### Python service not found:
- Verify the `cwd` path is correct
- Check if virtual environment exists
- Make sure `ocr_api.py` is in that directory

### Port already in use:
- Change `PORT` in environment variables
- Or stop the service using that port

## Production Tips

1. **Set NODE_ENV=production** in `env_production`
2. **Disable watch mode** (`watch: false`) in production
3. **Set proper log rotation**:
   ```bash
   pm2 install pm2-logrotate
   ```
4. **Monitor regularly**: `pm2 monit`

