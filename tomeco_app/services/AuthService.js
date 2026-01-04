import AsyncStorage from '@react-native-async-storage/async-storage';

const AUTH_TOKEN_KEY = 'auth_token';
const ENFORCER_DATA_KEY = 'enforcer_data';

/**
 * Authentication Service
 * Handles token validation and authentication state
 */

/**
 * Get current auth token
 * @returns {Promise<string|null>}
 */
export const getAuthToken = async () => {
  try {
    const token = await AsyncStorage.getItem(AUTH_TOKEN_KEY);
    return token;
  } catch (error) {
    console.error('Error getting auth token:', error);
    return null;
  }
};

/**
 * Check if token is valid (not offline placeholder)
 * @param {string} token
 * @returns {boolean}
 */
export const isValidToken = (token) => {
  if (!token) return false;
  // Reject offline placeholder tokens for API calls
  if (token.startsWith('offline_token_')) return false;
  // Accept Sanctum tokens (format: "1|abc123...") - typically 40+ characters
  if (token.includes('|') && token.length >= 40) return true;
  // Accept UUID tokens (format: "db48e16a-b6e2-4f3c-a86e-823c4013e31d") - 36 characters
  if (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(token)) return true;
  // Reject tokens that are too short
  if (token.length < 10) return false;
  return true;
};

/**
 * Check if token is an offline placeholder
 * @param {string} token
 * @returns {boolean}
 */
export const isOfflineToken = (token) => {
  return token && token.startsWith('offline_token_');
};

/**
 * Validate current token
 * @returns {Promise<{valid: boolean, token: string|null}>}
 */
export const validateToken = async () => {
  const token = await getAuthToken();
  return {
    valid: isValidToken(token),
    token,
  };
};

/**
 * Clear authentication data
 */
export const clearAuth = async () => {
  try {
    await AsyncStorage.removeItem(AUTH_TOKEN_KEY);
    await AsyncStorage.removeItem(ENFORCER_DATA_KEY);
  } catch (error) {
    console.error('Error clearing auth:', error);
  }
};

/**
 * Check if response indicates authentication error
 * @param {Response} response
 * @returns {boolean}
 */
export const isAuthError = (response) => {
  return response.status === 401 || response.status === 403;
};

/**
 * Handle authentication error
 * @param {Error|Response} error
 * @returns {Promise<boolean>} Returns true if user should re-login
 */
export const handleAuthError = async (error) => {
  // Import network status check
  const { isOnline } = require('./NetworkStatus');
  
  // Don't treat network errors as auth errors when offline
  const isNetworkError = error.message && (
    error.message.includes('Network') ||
    error.message.includes('fetch') ||
    error.message.includes('Failed to fetch') ||
    error.message.includes('Network request failed')
  );
  
  // If offline and it's a network error, don't clear auth
  if (!isOnline() && isNetworkError) {
    console.log('Network error while offline - not clearing auth');
    return false; // Not an auth error, just network issue
  }
  
  // Check if it's a 401/403 error (only if we got a valid HTTP response)
  if (error.status === 401 || error.status === 403) {
    // Clear invalid token
    await clearAuth();
    return true; // Should re-login
  }
  
  // Check error message (but not network errors)
  if (error.message && !isNetworkError && (
    error.message.includes('Unauthenticated') ||
    error.message.includes('Unauthorized') ||
    error.message.includes('Invalid token')
  )) {
    await clearAuth();
    return true; // Should re-login
  }
  
  return false; // Not an auth error
};

/**
 * Store auth token
 * @param {string} token
 */
export const setAuthToken = async (token) => {
  try {
    await AsyncStorage.setItem(AUTH_TOKEN_KEY, token);
  } catch (error) {
    console.error('Error setting auth token:', error);
  }
};

export default {
  getAuthToken,
  setAuthToken,
  isValidToken,
  isOfflineToken,
  validateToken,
  clearAuth,
  isAuthError,
  handleAuthError,
};

