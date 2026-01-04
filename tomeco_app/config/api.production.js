// Production API Configuration
// Copy this to api.js and update with your production domain

// ============================================
// PRODUCTION CONFIGURATION
// ============================================

// Your production server domain (e.g., 'api.tomeco.com' or your server IP)
const PRODUCTION_DOMAIN = 'your-production-domain.com'; // CHANGE THIS!
const PRODUCTION_IP = 'your-server-ip'; // Or use IP if no domain

// Local development settings
const DEV_HOST = '192.168.1.16'; // Your local IP for development

// Node.js Server URL (server.js runs on port 3000)
const API_BASE_URL = __DEV__ 
  ? `http://${DEV_HOST}:3000/api`  // Development - Node.js server
  : `https://${PRODUCTION_DOMAIN}/api`; // Production - Use HTTPS!

// Laravel Backend URL (if using Laravel for some endpoints)
const LARAVEL_BASE_URL = __DEV__
  ? `http://${DEV_HOST}:8000`  // Development
  : `https://${PRODUCTION_DOMAIN}`; // Production

// Python OCR service URL (internal - Node.js will proxy to it)
// In production, Python OCR runs on same server, so use localhost
const PYTHON_OCR_URL = __DEV__
  ? `http://192.168.1.10:5000`  // Development
  : `http://localhost:5000`; // Production - internal only

export default {
  BASE_URL: API_BASE_URL,
  LARAVEL_BASE_URL: LARAVEL_BASE_URL,
  HEALTH: `${API_BASE_URL}/diagnostics`, // Node.js diagnostics endpoint
  // Use Node.js server for authentication (UUID tokens)
  LOGIN: `${API_BASE_URL}/mobile/login`, // Node.js server endpoint
  LOGOUT: `${LARAVEL_BASE_URL}/api/mobile/logout`, // Laravel endpoint
  PROFILE: `${API_BASE_URL}/mobile/profile`, // Node.js server endpoint
  TICKETS: {
    CREATE: `${LARAVEL_BASE_URL}/api/mobile/tickets`,
    LIST: `${LARAVEL_BASE_URL}/api/mobile/tickets`,
    SHOW: (id) => `${LARAVEL_BASE_URL}/api/mobile/tickets/${id}`,
  },
  OCR: {
    SCAN_ID: `${API_BASE_URL}/mobile/ocr/scan-id`, // Node.js server endpoint (proxies to Python)
  },
};

