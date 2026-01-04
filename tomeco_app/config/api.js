// API Configuration
// IMPORTANT: Expo Go doesn't support usesCleartextTraffic setting!
// You need a development build for HTTP to work on Android.
// 
// For Android Emulator: Use '10.0.2.2' (emulator's alias for host machine)
// For Physical Device: Use your computer's IP (e.g., '192.168.1.5')
// 
// To find your IP: Windows: ipconfig | findstr IPv4

// ============================================
// PRODUCTION DEPLOYMENT CONFIGURATION
// ============================================
// For production: Set your Render URLs here or use a production config file
// For development: Use local IP

// PRODUCTION: Set your Render URLs here
// You can also create api.production.js with these values
const PRODUCTION_API_URL = null; // e.g., 'https://tomeco-api.onrender.com'
const PRODUCTION_LARAVEL_URL = null; // e.g., 'https://tomeco-web.onrender.com'
const USE_PRODUCTION = !!(PRODUCTION_API_URL && PRODUCTION_LARAVEL_URL);

// Local development settings
const DEV_HOST = '192.168.137.20'; // Your local IP address

// Node.js Server URL (server.js runs on port 3000)
// PRODUCTION: Uses Render URL
// DEVELOPMENT: Uses local IP
const API_BASE_URL = USE_PRODUCTION
  ? `${PRODUCTION_API_URL}/api`  // Production - Render URL
  : `http://${DEV_HOST}:3000/api`;  // Local development

// Laravel Backend URL (for tickets and some endpoints)
const LARAVEL_BASE_URL = USE_PRODUCTION
  ? PRODUCTION_LARAVEL_URL  // Production - Render URL
  : `http://${DEV_HOST}:8000`;  // Local development

const API_CONFIG = {
  BASE_URL: API_BASE_URL,
  LARAVEL_BASE_URL: LARAVEL_BASE_URL,
  HEALTH: `${API_BASE_URL}/diagnostics`, // Node.js diagnostics endpoint
  // Use Laravel Sanctum auth so tickets/secure APIs accept the token
  LOGIN: `${LARAVEL_BASE_URL}/api/mobile/login`, // Laravel endpoint
  LOGOUT: `${LARAVEL_BASE_URL}/api/mobile/logout`, // Laravel endpoint
  PROFILE: `${LARAVEL_BASE_URL}/api/mobile/profile`, // Laravel endpoint
  TICKETS: {
    CREATE: `${LARAVEL_BASE_URL}/api/mobile/tickets`,
    LIST: `${LARAVEL_BASE_URL}/api/mobile/tickets`,
    SHOW: (id) => `${LARAVEL_BASE_URL}/api/mobile/tickets/${id}`,
  },
  OCR: {
    SCAN_ID: `${API_BASE_URL}/mobile/ocr/scan-id`, // Node.js server endpoint
  },
};

// Log API configuration in development
if (__DEV__) {
  console.log('API Configuration:', {
    USE_PRODUCTION,
    LARAVEL_BASE_URL: API_CONFIG.LARAVEL_BASE_URL,
    API_BASE_URL: API_CONFIG.BASE_URL,
    TICKETS_CREATE: API_CONFIG.TICKETS.CREATE,
    HEALTH_ENDPOINT: `${API_CONFIG.LARAVEL_BASE_URL}/api/mobile/health`,
  });
}

export default API_CONFIG;

