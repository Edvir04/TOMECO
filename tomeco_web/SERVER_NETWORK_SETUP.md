# Server Network Configuration

## Current Configuration

Both servers are configured to bind to `0.0.0.0`, which means they listen on **all network interfaces**.

### What This Means:

**`0.0.0.0` (All Interfaces):**
- ✅ Accessible from `http://localhost:8000`
- ✅ Accessible from `http://127.0.0.1:8000`
- ✅ Accessible from your local IP address (e.g., `http://192.168.1.100:8000`)
- ✅ Accessible from other devices on your network (phones, tablets, other computers)
- ✅ Perfect for mobile app testing and development

**`127.0.0.1` (Localhost Only):**
- ✅ Accessible from `http://localhost:8000`
- ✅ Accessible from `http://127.0.0.1:8000`
- ❌ NOT accessible from other devices on your network
- ❌ Mobile apps on physical devices cannot connect

## Server Configuration

### Admin Server (Port 8000)
- **Host:** `0.0.0.0`
- **Port:** `8000`
- **Access URLs:**
  - `http://localhost:8000`
  - `http://127.0.0.1:8000`
  - `http://[YOUR_LOCAL_IP]:8000` (from other devices)

### Violator Server (Port 8001)
- **Host:** `0.0.0.0`
- **Port:** `8001`
- **Access URLs:**
  - `http://localhost:8001`
  - `http://127.0.0.1:8001`
  - `http://[YOUR_LOCAL_IP]:8001` (from other devices)

## Finding Your Local IP Address

### Windows:
```cmd
ipconfig
```
Look for "IPv4 Address" under your active network adapter (usually WiFi or Ethernet).

### Linux/Mac:
```bash
ip addr show
# or
ifconfig
```

## Why Use 0.0.0.0?

1. **Mobile App Development:** Your mobile app (running on a physical device) can connect to your development server
2. **Network Testing:** Test from multiple devices on your network
3. **Team Development:** Other developers on your network can access your server
4. **API Testing:** Test API endpoints from mobile devices or Postman on other machines

## Security Note

⚠️ **Development Only:** Using `0.0.0.0` exposes your server to your local network. This is fine for development but:
- Don't use this in production
- Make sure your firewall is configured appropriately
- Only use on trusted networks

For production, use a proper web server (Apache/Nginx) with proper security configurations.

## Changing Back to Localhost Only

If you want to restrict access to localhost only, change the `--host` parameter:

**Admin Server:**
```cmd
php artisan serve --port=8000 --host=127.0.0.1
```

**Violator Server:**
```cmd
php artisan serve --port=8001 --host=127.0.0.1
```

Or remove the `--host` parameter entirely (defaults to 127.0.0.1):
```cmd
php artisan serve --port=8000
```

