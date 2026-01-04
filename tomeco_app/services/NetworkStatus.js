import { useState, useEffect } from 'react';

/**
 * Network Status Service
 * Detects online/offline status using fetch-based approach
 * Works with Expo without additional dependencies
 */

let listeners = [];
let currentStatus = { isConnected: true, type: 'unknown' };
let checkInterval = null;

// API endpoint for connectivity check (will be set dynamically)
let API_HEALTH_ENDPOINT = null;

// Initialize API endpoint for connectivity check (lazy load)
const getHealthEndpoint = () => {
  if (API_HEALTH_ENDPOINT) return API_HEALTH_ENDPOINT;
  
  try {
    // Try to use app's API endpoint for connectivity check
    const api = require('../config/api');
    API_HEALTH_ENDPOINT = api.default?.HEALTH || api.HEALTH;
    return API_HEALTH_ENDPOINT;
  } catch (e) {
    // Fallback if API not available
    return null;
  }
};

// Simple connectivity check using fetch
const checkConnectivity = async () => {
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    
    // Prefer using app's API endpoint, fallback to Google
    const healthEndpoint = getHealthEndpoint();
    const checkUrl = healthEndpoint || 'https://www.google.com/favicon.ico';
    
    const response = await fetch(checkUrl, {
      method: 'HEAD',
      mode: 'no-cors',
      signal: controller.signal,
      cache: 'no-cache',
    });
    
    clearTimeout(timeoutId);
    return true;
  } catch (error) {
    // If API endpoint fails, try a simple internet check
    if (API_HEALTH_ENDPOINT) {
      try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 2000);
        await fetch('https://www.google.com/favicon.ico', {
          method: 'HEAD',
          mode: 'no-cors',
          signal: controller.signal,
        });
        clearTimeout(timeoutId);
        return true;
      } catch (e) {
        return false;
      }
    }
    return false;
  }
};

// Periodic connectivity check
const startConnectivityCheck = () => {
  if (checkInterval) return;
  
  checkInterval = setInterval(async () => {
    const isConnected = await checkConnectivity();
    const newStatus = {
      isConnected,
      type: 'unknown',
    };
    
    if (newStatus.isConnected !== currentStatus.isConnected) {
      currentStatus = newStatus;
      listeners.forEach(listener => listener(newStatus));
    }
  }, 5000); // Check every 5 seconds
};

// Initialize
checkConnectivity().then(isConnected => {
  currentStatus = { isConnected, type: 'unknown' };
  startConnectivityCheck();
});

/**
 * Get current network status
 * @returns {Promise<{isConnected: boolean, type: string}>}
 */
export const getNetworkStatus = async () => {
  const isConnected = await checkConnectivity();
  return {
    isConnected,
    type: 'unknown',
  };
};

/**
 * Check if device is currently online
 * @returns {boolean}
 */
export const isOnline = () => {
  return currentStatus.isConnected;
};

/**
 * Subscribe to network status changes
 * @param {Function} callback - Called when network status changes
 * @returns {Function} Unsubscribe function
 */
export const subscribeToNetworkStatus = (callback) => {
  listeners.push(callback);
  
  // Immediately call with current status
  callback(currentStatus);
  
  // Return unsubscribe function
  return () => {
    listeners = listeners.filter(l => l !== callback);
  };
};

/**
 * React Hook for network status
 * @returns {{isConnected: boolean, type: string}}
 */
export const useNetworkStatus = () => {
  const [status, setStatus] = useState(currentStatus);

  useEffect(() => {
    const unsubscribe = subscribeToNetworkStatus(setStatus);
    return unsubscribe;
  }, []);

  return status;
};

export default {
  getNetworkStatus,
  isOnline,
  subscribeToNetworkStatus,
  useNetworkStatus,
};

