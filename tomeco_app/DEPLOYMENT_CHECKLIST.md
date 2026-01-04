# Deployment Checklist

## ⚠️ Issues to Fix Before Deployment

### 1. API Configuration (CRITICAL)
- [ ] Update `config/api.js` with production URLs
- [ ] Replace `https://your-production-domain.com/api` with actual production domain
- [ ] Ensure all endpoints use HTTPS in production
- [ ] Test API endpoints are accessible

### 2. Network Detection
- [ ] Current implementation uses Google's favicon (may be blocked)
- [ ] Consider using your own API endpoint for connectivity check
- [ ] Or use a more reliable connectivity check

### 3. Android Configuration
- [ ] Review `usesCleartextTraffic: true` - should be false for production
- [ ] Ensure all API calls use HTTPS
- [ ] Update Android package name if needed

### 4. Environment Variables
- [ ] Set up environment variables for API URLs
- [ ] Use different configs for dev/staging/production

### 5. Backend Deployment
- [ ] Deploy Node.js server (server.js) to production
- [ ] Deploy Laravel backend to production
- [ ] Configure CORS for production domain
- [ ] Set up database connections
- [ ] Configure file storage paths

## Required Changes

See `DEPLOYMENT_FIXES.md` for detailed fixes.

