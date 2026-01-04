# Deployment Fixes Guide

## Before Deploying - Required Steps

### 1. Update API Configuration

**File: `config/api.js`**

Replace the production URLs with your actual production domain:

```javascript
// BEFORE (won't work):
const PROD_API_BASE_URL = 'https://your-production-domain.com/api';
const PROD_LARAVEL_BASE_URL = 'https://your-production-domain.com';

// AFTER (update with your domain):
const PROD_API_BASE_URL = 'https://api.tomeco.com/api'; // Your actual domain
const PROD_LARAVEL_BASE_URL = 'https://api.tomeco.com'; // Your actual domain
```

### 2. Using Environment Variables (Recommended)

Create a `.env` file in the root directory:

```env
EXPO_PUBLIC_API_URL=https://api.tomeco.com/api
EXPO_PUBLIC_LARAVEL_URL=https://api.tomeco.com
```

Then install `expo-constants` if not already installed:
```bash
npm install expo-constants
```

Update `config/api.js`:
```javascript
import Constants from 'expo-constants';

const PROD_API_BASE_URL = Constants.expoConfig?.extra?.apiUrl || 'https://api.tomeco.com/api';
const PROD_LARAVEL_BASE_URL = Constants.expoConfig?.extra?.laravelUrl || 'https://api.tomeco.com';
```

Update `app.json`:
```json
{
  "expo": {
    "extra": {
      "apiUrl": "https://api.tomeco.com/api",
      "laravelUrl": "https://api.tomeco.com"
    }
  }
}
```

### 3. Backend Deployment Requirements

#### Node.js Server (server.js)
- Deploy to a server (Heroku, AWS, DigitalOcean, etc.)
- Update CORS to allow your app domain
- Configure database connection
- Set environment variables:
  - `DATABASE_URL`
  - `PORT` (defaults to 3000)
  - `NODE_ENV=production`

#### Laravel Backend
- Deploy to server
- Configure `.env` file
- Set up database
- Configure file storage
- Update CORS settings

### 4. Network Detection Fix

The network detection now tries your API endpoint first, then falls back to Google. This is better for production but ensure:

- Your API health endpoint (`/api/diagnostics`) is accessible
- It responds quickly (within 3 seconds)
- It doesn't require authentication

### 5. Android Configuration

**File: `app.json`**

Already updated:
- `usesCleartextTraffic: false` (requires HTTPS)
- Added necessary permissions

### 6. Build Configuration

#### For EAS Build (Recommended)

1. Install EAS CLI:
```bash
npm install -g eas-cli
eas login
```

2. Configure build:
```bash
eas build:configure
```

3. Update `eas.json`:
```json
{
  "build": {
    "production": {
      "env": {
        "EXPO_PUBLIC_API_URL": "https://api.tomeco.com/api",
        "EXPO_PUBLIC_LARAVEL_URL": "https://api.tomeco.com"
      },
      "android": {
        "buildType": "apk"
      }
    }
  }
}
```

4. Build:
```bash
eas build --platform android --profile production
```

#### For Local Build

1. Update API URLs in `config/api.js`
2. Build:
```bash
expo build:android
```

### 7. Testing Before Deployment

1. **Test API Endpoints:**
   ```bash
   # Test Node.js API
   curl https://api.tomeco.com/api/diagnostics
   
   # Test Laravel API
   curl https://api.tomeco.com/api/mobile/health
   ```

2. **Test Offline Features:**
   - Create ticket while offline
   - Login with cached credentials
   - Verify sync when online

3. **Test Network Detection:**
   - Turn off WiFi/data
   - Verify offline indicator shows
   - Turn on connection
   - Verify sync triggers

### 8. Security Checklist

- [ ] All API endpoints use HTTPS
- [ ] CORS configured for production domain only
- [ ] Authentication tokens properly secured
- [ ] Database credentials in environment variables
- [ ] File uploads have size limits
- [ ] Input validation on backend
- [ ] Rate limiting configured
- [ ] Error messages don't expose sensitive info

### 9. Performance Optimization

- [ ] Enable image compression
- [ ] Optimize bundle size
- [ ] Use production build (not development)
- [ ] Enable code splitting if needed
- [ ] Test on low-end devices

### 10. Monitoring

Set up monitoring for:
- API response times
- Error rates
- Offline sync success rate
- User login success rate
- Ticket creation success rate

## Quick Deployment Steps

1. **Update API URLs:**
   ```bash
   # Edit config/api.js
   # Replace 'your-production-domain.com' with actual domain
   ```

2. **Deploy Backend:**
   ```bash
   # Deploy Node.js server
   # Deploy Laravel backend
   # Configure CORS
   ```

3. **Build App:**
   ```bash
   eas build --platform android --profile production
   ```

4. **Test:**
   - Install APK on test device
   - Test all features
   - Verify offline functionality

5. **Release:**
   - Upload to Google Play Store
   - Or distribute APK

## Common Issues

### Issue: "Network request failed"
**Solution:** Check API URLs are correct and accessible

### Issue: "CORS error"
**Solution:** Update backend CORS to allow your app domain

### Issue: "Offline login not working"
**Solution:** User must login online first to cache credentials

### Issue: "Tickets not syncing"
**Solution:** Check network connection and API endpoint accessibility

## Support

If you encounter issues:
1. Check console logs
2. Verify API endpoints are accessible
3. Check network connectivity
4. Review error messages
5. Test with development build first

