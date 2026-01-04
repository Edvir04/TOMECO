import AsyncStorage from '@react-native-async-storage/async-storage';
import * as FileSystem from 'expo-file-system/legacy';
import API from '../config/api';
import { getPendingTickets, updateTicketStatus, removePendingTicket } from './OfflineStorage';
import { isOnline } from './NetworkStatus';
import { getAuthToken, isValidToken, isOfflineToken, handleAuthError } from './AuthService';
import { compressImage, compressBase64Signature } from './ImageCompression';

/**
 * Sync Service
 * Handles syncing pending tickets to the server when online
 */

let isSyncing = false;
let syncListeners = [];

/**
 * Subscribe to sync status changes
 * @param {Function} callback
 * @returns {Function} Unsubscribe function
 */
export const subscribeToSyncStatus = (callback) => {
  syncListeners.push(callback);
  return () => {
    syncListeners = syncListeners.filter(l => l !== callback);
  };
};

const notifySyncStatus = (status) => {
  syncListeners.forEach(listener => listener(status));
};

/**
 * Sync a single ticket to the server
 * @param {Object} ticket - Ticket object from offline storage
 * @returns {Promise<boolean>} Success status
 */
const syncSingleTicket = async (ticket) => {
  try {
    // Update status to syncing
    await updateTicketStatus(ticket.id, 'syncing');
    
    // Get auth token
    const token = await getAuthToken();
    if (!token) {
      throw new Error('Authentication token not found. Please login again.');
    }
    
    // Check if token is an offline placeholder - skip sync if so
    if (isOfflineToken(token)) {
      throw new Error('Offline token detected. Please login online to sync tickets.');
    }
    
    // Check if token is valid (not an offline placeholder token)
    if (!isValidToken(token)) {
      await handleAuthError({ message: 'Invalid token' });
      throw new Error('Invalid token. Please login online to sync tickets.');
    }
    
    // Get current enforcer data to ensure officer names are set
    let enforcerData = null;
    try {
      const enforcerDataStr = await AsyncStorage.getItem('enforcer_data');
      if (enforcerDataStr) {
        enforcerData = JSON.parse(enforcerDataStr);
      }
    } catch (error) {
      console.warn('SyncService - Error getting enforcer data:', error);
    }
    
    // Prepare FormData
    const formData = new FormData();
    
    // Add all form fields
    const ticketData = ticket.data;
    
    // Ensure enforcer fields are populated (use saved data or current enforcer data)
    const apprehendingOfficer = ticketData.apprehending_officer || enforcerData?.fullname || '';
    const issuedBy = ticketData.issued_by || enforcerData?.fullname || '';
    const tomecoDid = ticketData.tomeco_did || enforcerData?.id_number || '';
    
    console.log('SyncService - Enforcer info for sync:', {
      saved_apprehending_officer: ticketData.apprehending_officer,
      saved_issued_by: ticketData.issued_by,
      enforcer_fullname: enforcerData?.fullname,
      final_apprehending_officer: apprehendingOfficer,
      final_issued_by: issuedBy,
    });
    
    Object.keys(ticketData).forEach(key => {
      const value = ticketData[key];
      
      // Skip null/undefined values (but allow empty strings)
      if (value === null || value === undefined) {
        return;
      }
      
      // Handle dates
      if (value instanceof Date) {
        if (key.includes('date')) {
          formData.append(key, value.toISOString().split('T')[0]);
        } else if (key.includes('time')) {
          const hours = String(value.getHours()).padStart(2, '0');
          const minutes = String(value.getMinutes()).padStart(2, '0');
          formData.append(key, `${hours}:${minutes}`);
        }
        return;
      }
      
      // Handle arrays (violations)
      if (Array.isArray(value)) {
        value.forEach((item, index) => {
          formData.append(`${key}[${index}]`, item);
        });
        return;
      }
      
      // Handle booleans
      if (typeof value === 'boolean') {
        formData.append(key, value ? '1' : '0');
        return;
      }
      
      // Regular values
      formData.append(key, String(value));
    });
    
    // Explicitly set enforcer fields to ensure they're not blank
    formData.append('apprehending_officer', apprehendingOfficer);
    formData.append('issued_by', issuedBy);
    if (tomecoDid) {
      formData.append('tomeco_did', tomecoDid);
    }
    
    // Ensure driver_contact is included (required for SMS)
    const driverContact = ticketData.driver_contact || '';
    formData.append('driver_contact', driverContact);
    
    console.log('SyncService - Driver contact for SMS:', {
      has_driver_contact: !!driverContact,
      driver_contact: driverContact ? driverContact.substring(0, 3) + '***' : 'MISSING',
      ticket_id: ticket.id,
    });
    
    // Add images (with compression)
    for (let i = 0; i < ticket.images.length; i++) {
      const image = ticket.images[i];
      try {
        const fileInfo = await FileSystem.getInfoAsync(image.localPath);
        
        if (fileInfo.exists) {
          console.log(`SyncService - Compressing image ${i} before upload...`);
          
          // Compress image before uploading
          const compressed = await compressImage(fileInfo.uri, {
            maxWidth: 1920,
            maxHeight: 1920,
            quality: 0.7, // 70% quality for good balance
          });
          
          // For React Native, use the compressed file URI
          const imageFile = {
            uri: compressed.uri,
            type: image.type || 'image/jpeg',
            name: image.originalName || `image_${i}.jpg`,
          };
          
          formData.append(`images[${i}]`, imageFile);
          
          console.log(`SyncService - Added compressed image ${i}:`, {
            uri: compressed.uri.substring(0, 50) + '...',
            type: image.type || 'image/jpeg',
            name: image.originalName || `image_${i}.jpg`,
            size: compressed.size ? `${(compressed.size / 1024 / 1024).toFixed(2)} MB` : 'unknown',
          });
        } else {
          console.warn(`SyncService - Image file not found: ${image.localPath}`);
        }
      } catch (imageError) {
        console.error(`SyncService - Error processing image ${i}:`, imageError);
        // Continue with other images
      }
    }
    
    // Add signatures (with compression)
    if (ticket.signaturePath) {
      const sigInfo = await FileSystem.getInfoAsync(ticket.signaturePath);
      if (sigInfo.exists) {
        try {
          console.log('SyncService - Compressing signature before upload...');
          
          // Read signature as base64
          const signatureBase64 = await FileSystem.readAsStringAsync(ticket.signaturePath, {
            encoding: FileSystem.EncodingType.Base64,
          });
          
          // Compress signature (signatures can be smaller)
          const compressedSignature = await compressBase64Signature(
            `data:image/png;base64,${signatureBase64}`,
            {
              maxWidth: 800,
              maxHeight: 400,
              quality: 0.8, // Higher quality for signatures
            }
          );
          
          // Extract base64 data from compressed signature
          const compressedBase64 = compressedSignature.includes(',') 
            ? compressedSignature.split(',')[1] 
            : compressedSignature;
          
          formData.append('signature', compressedBase64);
          console.log('SyncService - Added compressed signature');
        } catch (sigError) {
          console.error('SyncService - Error compressing signature, using original:', sigError);
          // Fallback to original if compression fails
          const signatureBase64 = await FileSystem.readAsStringAsync(ticket.signaturePath, {
            encoding: FileSystem.EncodingType.Base64,
          });
          formData.append('signature', signatureBase64);
        }
      }
    }
    
    if (ticket.driverSignaturePath) {
      const driverSigInfo = await FileSystem.getInfoAsync(ticket.driverSignaturePath);
      if (driverSigInfo.exists) {
        try {
          console.log('SyncService - Compressing driver signature before upload...');
          
          // Read signature as base64
          const driverSignatureBase64 = await FileSystem.readAsStringAsync(ticket.driverSignaturePath, {
            encoding: FileSystem.EncodingType.Base64,
          });
          
          // Compress signature
          const compressedSignature = await compressBase64Signature(
            `data:image/png;base64,${driverSignatureBase64}`,
            {
              maxWidth: 800,
              maxHeight: 400,
              quality: 0.8,
            }
          );
          
          // Extract base64 data from compressed signature
          const compressedBase64 = compressedSignature.includes(',') 
            ? compressedSignature.split(',')[1] 
            : compressedSignature;
          
          formData.append('driver_signature', compressedBase64);
          console.log('SyncService - Added compressed driver signature');
        } catch (sigError) {
          console.error('SyncService - Error compressing driver signature, using original:', sigError);
          // Fallback to original if compression fails
          const driverSignatureBase64 = await FileSystem.readAsStringAsync(ticket.driverSignaturePath, {
            encoding: FileSystem.EncodingType.Base64,
          });
          formData.append('driver_signature', driverSignatureBase64);
        }
      }
    }
    
    // Submit to API
    console.log('SyncService - Attempting to sync ticket:', ticket.id);
    console.log('SyncService - API endpoint:', API.TICKETS.CREATE);
    console.log('SyncService - Token preview:', token.substring(0, 20) + '...');
    console.log('SyncService - FormData entries:', {
      imagesCount: ticket.images.length,
      hasSignature: !!ticket.signaturePath,
      hasDriverSignature: !!ticket.driverSignaturePath,
    });
    
    let response;
    try {
      // Note: Don't set Content-Type header - React Native sets it automatically for FormData
      // with the correct boundary
      // Use a longer timeout for large file uploads (60 seconds)
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 60000); // 60 second timeout for large uploads
      
      console.log('SyncService - Sending request with FormData (this may take a while for large files)...');
      
      response = await fetch(API.TICKETS.CREATE, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          // Explicitly DO NOT set Content-Type - let React Native handle it for FormData
        },
        body: formData,
        signal: controller.signal,
      });
      
      clearTimeout(timeoutId);
      
      console.log('SyncService - Response received:', {
        status: response.status,
        statusText: response.statusText,
        ok: response.ok,
      });
    } catch (fetchError) {
      console.error('SyncService - Fetch error details:', {
        message: fetchError.message,
        name: fetchError.name,
        type: fetchError.constructor.name,
      });
      
      // Check if it's a timeout
      if (fetchError.name === 'AbortError' || fetchError.message.includes('aborted')) {
        throw new Error('Request timed out. The files may be too large or the connection is too slow. Please try again with a better connection.');
      }
      
      // Check if it's a network error
      if (fetchError.message && (
        fetchError.message.includes('Network request failed') ||
        fetchError.message.includes('Failed to fetch') ||
        fetchError.message.includes('NetworkError') ||
        fetchError.message.includes('TypeError')
      )) {
        // Provide more specific error message
        const errorMsg = 'Network request failed. This might be due to:\n' +
          '1. Large file size (images/signatures) - try reducing image size\n' +
          '2. Network timeout - check your internet connection\n' +
          '3. Server not accepting the request - verify ngrok is running\n' +
          '4. Request size limit exceeded\n' +
          'Please check your connection and try again.';
        throw new Error(errorMsg);
      }
      throw fetchError;
    }
    
    // Check for authentication errors
    if (response.status === 401 || response.status === 403) {
      await handleAuthError(response);
      throw new Error('Authentication failed. Please login again.');
    }
    
    // Check if response is JSON
    let result;
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      result = await response.json();
    } else {
      const text = await response.text();
      throw new Error(`Server returned non-JSON response: ${text.substring(0, 200)}`);
    }
    
    if (response.ok) {
      // Success - remove from pending
      await removePendingTicket(ticket.id);
      return true;
    } else {
      // Failed - update status
      const retryCount = (ticket.retryCount || 0) + 1;
      await updateTicketStatus(ticket.id, 'failed', { retryCount });
      throw new Error(result.message || result.error || 'Failed to sync ticket');
    }
  } catch (error) {
    console.error('Error syncing ticket:', error);
    const retryCount = (ticket.retryCount || 0) + 1;
    await updateTicketStatus(ticket.id, 'failed', { retryCount });
    throw error;
  }
};

/**
 * Sync all pending tickets
 * @returns {Promise<{success: number, failed: number}>}
 */
export const syncPendingTickets = async () => {
  // Check if already syncing
  if (isSyncing) {
    console.log('Sync already in progress');
    return { success: 0, failed: 0 };
  }
  
  // Check if online
  if (!isOnline()) {
    console.log('Device is offline, cannot sync');
    notifySyncStatus({ isSyncing: false, message: 'Device is offline' });
    return { success: 0, failed: 0 };
  }
  
  // Check if we have a valid token (not offline placeholder)
  const token = await getAuthToken();
  if (!token || isOfflineToken(token) || !isValidToken(token)) {
    console.log('No valid token for syncing. User needs to login online.');
    notifySyncStatus({ 
      isSyncing: false, 
      message: 'Please login online to sync tickets',
      error: 'Authentication required',
    });
    return { success: 0, failed: 0 };
  }
  
  // Additional check: Verify API endpoint is actually reachable
  // Note: Health check might timeout, but we'll still try to sync
  // since login works, the server is likely reachable
  try {
    const baseUrl = API.LARAVEL_BASE_URL;
    console.log('SyncService - Testing connectivity to:', `${baseUrl}/api/mobile/health`);
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // Increased to 10 seconds
    
    const connectivityTest = await fetch(`${baseUrl}/api/mobile/health`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
      signal: controller.signal,
    }).catch((error) => {
      clearTimeout(timeoutId);
      if (error.name === 'AbortError') {
        console.warn('SyncService - Health check timed out (server may be slow, but will try sync anyway)');
      } else {
        console.warn('SyncService - Health check failed:', error.message);
      }
      return null;
    });
    
    clearTimeout(timeoutId);
    
    if (connectivityTest && connectivityTest.ok) {
      console.log('SyncService - Server is reachable, proceeding with sync');
    } else {
      console.warn('SyncService - Health check inconclusive, but proceeding with sync (login works, so server is reachable)');
    }
  } catch (connectivityError) {
    console.warn('SyncService - Connectivity check failed:', connectivityError);
    // Continue anyway - login works, so server is likely reachable
  }
  
  isSyncing = true;
  notifySyncStatus({ isSyncing: true, message: 'Syncing tickets...' });
  
  try {
    const pendingTickets = await getPendingTickets();
    
    if (pendingTickets.length === 0) {
      isSyncing = false;
      notifySyncStatus({ isSyncing: false, message: 'No pending tickets' });
      return { success: 0, failed: 0 };
    }
    
    let successCount = 0;
    let failedCount = 0;
    
    // Sync tickets one by one
    for (const ticket of pendingTickets) {
      try {
        // Skip tickets that are already syncing (in case of interruption)
        if (ticket.status === 'syncing') {
          await updateTicketStatus(ticket.id, 'pending');
        }
        
        await syncSingleTicket(ticket);
        successCount++;
        notifySyncStatus({
          isSyncing: true,
          message: `Synced ${successCount} of ${pendingTickets.length} tickets...`,
        });
      } catch (error) {
        console.error('Failed to sync ticket:', ticket.id, error);
        failedCount++;
        
        // If authentication error, stop syncing and notify
        if (error.message && (
          error.message.includes('Authentication') ||
          error.message.includes('Invalid token') ||
          error.message.includes('Unauthenticated')
        )) {
          console.warn('Authentication error detected. Stopping sync.');
          notifySyncStatus({
            isSyncing: false,
            message: 'Authentication failed. Please login again to sync tickets.',
            error: 'Authentication required',
          });
          break; // Stop trying to sync remaining tickets
        }
        
        // If network error, provide helpful message
        if (error.message && (
          error.message.includes('Network request failed') ||
          error.message.includes('Failed to fetch') ||
          error.message.includes('NetworkError')
        )) {
          console.warn('Network error during sync. Ticket will be retried later.');
          // Don't break - continue trying other tickets
          // The ticket status is already set to 'failed' in syncSingleTicket
        }
      }
    }
    
    isSyncing = false;
    const message = successCount > 0
      ? `Successfully synced ${successCount} ticket(s)`
      : 'No tickets synced';
    
    notifySyncStatus({
      isSyncing: false,
      message,
      successCount,
      failedCount,
    });
    
    return { success: successCount, failed: failedCount };
  } catch (error) {
    console.error('Error during sync:', error);
    isSyncing = false;
    notifySyncStatus({
      isSyncing: false,
      message: 'Sync failed',
      error: error.message,
    });
    return { success: 0, failed: 0 };
  }
};

/**
 * Auto-sync when device comes online
 */
export const setupAutoSync = () => {
  // Import here to avoid circular dependency
  const NetworkStatus = require('./NetworkStatus');
  
  NetworkStatus.subscribeToNetworkStatus((status) => {
    if (status.isConnected && !isSyncing) {
      // Device came online, try to sync
      console.log('Device came online, attempting to sync...');
      syncPendingTickets().catch(error => {
        console.error('Auto-sync failed:', error);
      });
    }
  });
};

export default {
  syncPendingTickets,
  syncSingleTicket,
  setupAutoSync,
  subscribeToSyncStatus,
};

