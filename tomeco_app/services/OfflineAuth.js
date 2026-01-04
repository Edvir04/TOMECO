import AsyncStorage from '@react-native-async-storage/async-storage';
import { isOnline } from './NetworkStatus';
import API from '../config/api';

const CACHED_USER_KEY = 'cached_user';
const CACHED_CREDENTIALS_KEY = 'cached_credentials';
const LAST_SYNC_KEY = 'last_user_sync';

/**
 * Offline Authentication Service
 * Handles offline login and user data caching
 */

// Simple encoding for React Native (not secure, just for basic offline login)
// In production, use proper encryption libraries
const simpleEncode = (str) => {
  // Simple hash-like encoding (not secure, just for basic validation)
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    const char = str.charCodeAt(i);
    hash = ((hash << 5) - hash) + char;
    hash = hash & hash; // Convert to 32bit integer
  }
  return Math.abs(hash).toString(16);
};

/**
 * Cache user credentials for offline login
 * @param {string} username
 * @param {string} password - Will be stored securely (hashed)
 */
export const cacheCredentials = async (username, password) => {
  try {
    // Store username and a hash of password (not the actual password)
    // In production, use proper encryption
    const credentials = {
      username,
      passwordHash: simpleEncode(password), // Simple encoding (use proper encryption in production)
      cachedAt: new Date().toISOString(),
    };
    
    await AsyncStorage.setItem(CACHED_CREDENTIALS_KEY, JSON.stringify(credentials));
  } catch (error) {
    console.error('Error caching credentials:', error);
  }
};

/**
 * Get cached credentials
 * @returns {Promise<{username: string, passwordHash: string}|null>}
 */
export const getCachedCredentials = async () => {
  try {
    const data = await AsyncStorage.getItem(CACHED_CREDENTIALS_KEY);
    return data ? JSON.parse(data) : null;
  } catch (error) {
    console.error('Error getting cached credentials:', error);
    return null;
  }
};

/**
 * Cache user data for offline access
 * @param {Object} userData - User/enforcer data
 */
export const cacheUserData = async (userData) => {
  try {
    const cachedData = {
      user: userData,
      cachedAt: new Date().toISOString(),
    };
    
    await AsyncStorage.setItem(CACHED_USER_KEY, JSON.stringify(cachedData));
    await AsyncStorage.setItem(LAST_SYNC_KEY, new Date().toISOString());
  } catch (error) {
    console.error('Error caching user data:', error);
  }
};

/**
 * Get cached user data
 * @returns {Promise<Object|null>}
 */
export const getCachedUserData = async () => {
  try {
    const data = await AsyncStorage.getItem(CACHED_USER_KEY);
    if (data) {
      const cached = JSON.parse(data);
      return cached.user;
    }
    return null;
  } catch (error) {
    console.error('Error getting cached user data:', error);
    return null;
  }
};

/**
 * Attempt offline login using cached credentials
 * @param {string} username
 * @param {string} password
 * @returns {Promise<{success: boolean, user: Object|null, message: string}>}
 */
export const attemptOfflineLogin = async (username, password) => {
  try {
    // Check if we have cached credentials
    const cached = await getCachedCredentials();
    
    if (!cached || cached.username !== username) {
      return {
        success: false,
        user: null,
        message: 'No cached credentials found. Please login online first.',
      };
    }
    
    // Verify password (simple comparison - use proper hashing in production)
    const passwordHash = simpleEncode(password);
    if (passwordHash !== cached.passwordHash) {
      return {
        success: false,
        user: null,
        message: 'Invalid credentials. Please check your username and password.',
      };
    }
    
    // Get cached user data
    const userData = await getCachedUserData();
    
    if (!userData) {
      return {
        success: false,
        user: null,
        message: 'No cached user data found. Please login online first.',
      };
    }
    
    // Return cached user
    return {
      success: true,
      user: userData,
      message: 'Logged in with cached credentials (offline mode)',
      isOffline: true,
    };
  } catch (error) {
    console.error('Error in offline login:', error);
    return {
      success: false,
      user: null,
      message: 'Offline login failed. Please try again.',
    };
  }
};

/**
 * Sync user data when online
 * @param {string} token - Auth token
 * @returns {Promise<{success: boolean, user: Object|null}>}
 */
export const syncUserData = async (token) => {
  try {
    if (!isOnline()) {
      return {
        success: false,
        user: null,
        message: 'Device is offline. Cannot sync user data.',
      };
    }
    
    // Fetch latest user data from API
    const response = await fetch(API.PROFILE, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    
    if (response.ok) {
      const data = await response.json();
      if (data.success && data.data) {
        // Cache the updated user data
        await cacheUserData(data.data);
        
        return {
          success: true,
          user: data.data,
          message: 'User data synced successfully',
        };
      }
    }
    
    return {
      success: false,
      user: null,
      message: 'Failed to sync user data',
    };
  } catch (error) {
    console.error('Error syncing user data:', error);
    return {
      success: false,
      user: null,
      message: 'Error syncing user data',
    };
  }
};

/**
 * Clear cached credentials and user data
 */
export const clearCachedAuth = async () => {
  try {
    await AsyncStorage.removeItem(CACHED_CREDENTIALS_KEY);
    await AsyncStorage.removeItem(CACHED_USER_KEY);
    await AsyncStorage.removeItem(LAST_SYNC_KEY);
  } catch (error) {
    console.error('Error clearing cached auth:', error);
  }
};

/**
 * Get last sync timestamp
 * @returns {Promise<string|null>}
 */
export const getLastSyncTime = async () => {
  try {
    return await AsyncStorage.getItem(LAST_SYNC_KEY);
  } catch (error) {
    console.error('Error getting last sync time:', error);
    return null;
  }
};

export default {
  cacheCredentials,
  getCachedCredentials,
  cacheUserData,
  getCachedUserData,
  attemptOfflineLogin,
  syncUserData,
  clearCachedAuth,
  getLastSyncTime,
};

