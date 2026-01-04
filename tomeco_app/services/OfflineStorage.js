import 'react-native-get-random-values'; // Must be imported before uuid
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as FileSystem from 'expo-file-system/legacy';
import { v4 as uuidv4 } from 'uuid';

const PENDING_TICKETS_KEY = 'pending_tickets';
const SYNCED_TICKETS_KEY = 'synced_tickets';
const OFFLINE_FILES_DIR = `${FileSystem.documentDirectory}offline_files/`;

/**
 * Offline Storage Service
 * Handles saving tickets and files locally when offline
 */

// Ensure offline files directory exists
const ensureOfflineDir = async () => {
  const dirInfo = await FileSystem.getInfoAsync(OFFLINE_FILES_DIR);
  if (!dirInfo.exists) {
    await FileSystem.makeDirectoryAsync(OFFLINE_FILES_DIR, { intermediates: true });
  }
};

/**
 * Save a ticket to offline storage
 * @param {Object} ticketData - Ticket form data
 * @param {Array} images - Array of image objects with uri
 * @param {string} signature - Base64 signature string
 * @param {string} driverSignature - Base64 driver signature string
 * @returns {Promise<string>} Local ticket ID
 */
export const saveTicketOffline = async (ticketData, images = [], signature = '', driverSignature = '') => {
  try {
    await ensureOfflineDir();
    
    const localTicketId = uuidv4();
    const timestamp = new Date().toISOString();
    
    // Save images to local storage
    const savedImages = [];
    for (let i = 0; i < images.length; i++) {
      const image = images[i];
      if (image.uri) {
        const fileName = `ticket_${localTicketId}_image_${i}_${Date.now()}.jpg`;
        const filePath = `${OFFLINE_FILES_DIR}${fileName}`;
        
        // Copy file to offline storage
        await FileSystem.copyAsync({
          from: image.uri,
          to: filePath,
        });
        
        savedImages.push({
          localPath: filePath,
          originalName: image.name || fileName,
          type: image.type || 'image/jpeg',
        });
      }
    }
    
    // Helper function to extract base64 from data URI or return as-is
    const extractBase64 = (data) => {
      if (!data) return '';
      // Remove data URI prefix if present (e.g., "data:image/png;base64,")
      if (data.includes(',')) {
        return data.split(',')[1];
      }
      return data;
    };
    
    // Save signatures to local storage if they exist
    let signaturePath = '';
    let driverSignaturePath = '';
    
    if (signature) {
      const sigFileName = `ticket_${localTicketId}_signature_${Date.now()}.png`;
      signaturePath = `${OFFLINE_FILES_DIR}${sigFileName}`;
      // Extract clean base64 string (remove data URI prefix if present)
      const cleanBase64 = extractBase64(signature);
      // Write base64 string to file
      await FileSystem.writeAsStringAsync(signaturePath, cleanBase64, {
        encoding: FileSystem.EncodingType.Base64,
      });
    }
    
    if (driverSignature) {
      const driverSigFileName = `ticket_${localTicketId}_driver_signature_${Date.now()}.png`;
      driverSignaturePath = `${OFFLINE_FILES_DIR}${driverSigFileName}`;
      // Extract clean base64 string (remove data URI prefix if present)
      const cleanDriverBase64 = extractBase64(driverSignature);
      await FileSystem.writeAsStringAsync(driverSignaturePath, cleanDriverBase64, {
        encoding: FileSystem.EncodingType.Base64,
      });
    }
    
    // Create ticket object
    const ticket = {
      id: localTicketId,
      data: ticketData,
      images: savedImages,
      signaturePath,
      driverSignaturePath,
      status: 'pending', // pending, syncing, synced, failed
      createdAt: timestamp,
      syncedAt: null,
      retryCount: 0,
    };
    
    // Get existing pending tickets
    const pendingTickets = await getPendingTickets();
    pendingTickets.push(ticket);
    
    // Save to AsyncStorage
    await AsyncStorage.setItem(PENDING_TICKETS_KEY, JSON.stringify(pendingTickets));
    
    console.log('Ticket saved offline:', localTicketId);
    return localTicketId;
  } catch (error) {
    console.error('Error saving ticket offline:', error);
    throw error;
  }
};

/**
 * Get all pending tickets
 * @returns {Promise<Array>}
 */
export const getPendingTickets = async () => {
  try {
    const data = await AsyncStorage.getItem(PENDING_TICKETS_KEY);
    return data ? JSON.parse(data) : [];
  } catch (error) {
    console.error('Error getting pending tickets:', error);
    return [];
  }
};

/**
 * Get a specific pending ticket by ID
 * @param {string} ticketId
 * @returns {Promise<Object|null>}
 */
export const getPendingTicket = async (ticketId) => {
  try {
    const pendingTickets = await getPendingTickets();
    return pendingTickets.find(t => t.id === ticketId) || null;
  } catch (error) {
    console.error('Error getting pending ticket:', error);
    return null;
  }
};

/**
 * Update ticket status
 * @param {string} ticketId
 * @param {string} status - 'pending', 'syncing', 'synced', 'failed'
 * @param {Object} updates - Additional fields to update
 */
export const updateTicketStatus = async (ticketId, status, updates = {}) => {
  try {
    const pendingTickets = await getPendingTickets();
    const index = pendingTickets.findIndex(t => t.id === ticketId);
    
    if (index !== -1) {
      pendingTickets[index] = {
        ...pendingTickets[index],
        status,
        ...updates,
      };
      
      await AsyncStorage.setItem(PENDING_TICKETS_KEY, JSON.stringify(pendingTickets));
    }
  } catch (error) {
    console.error('Error updating ticket status:', error);
  }
};

/**
 * Remove a ticket from pending list (after successful sync)
 * @param {string} ticketId
 */
export const removePendingTicket = async (ticketId) => {
  try {
    const pendingTickets = await getPendingTickets();
    const filtered = pendingTickets.filter(t => t.id !== ticketId);
    await AsyncStorage.setItem(PENDING_TICKETS_KEY, JSON.stringify(filtered));
    
    // Also clean up files
    const ticket = pendingTickets.find(t => t.id === ticketId);
    if (ticket) {
      // Delete images
      for (const image of ticket.images) {
        try {
          const fileInfo = await FileSystem.getInfoAsync(image.localPath);
          if (fileInfo.exists) {
            await FileSystem.deleteAsync(image.localPath);
          }
        } catch (error) {
          console.warn('Error deleting image:', error);
        }
      }
      
      // Delete signatures
      if (ticket.signaturePath) {
        try {
          const fileInfo = await FileSystem.getInfoAsync(ticket.signaturePath);
          if (fileInfo.exists) {
            await FileSystem.deleteAsync(ticket.signaturePath);
          }
        } catch (error) {
          console.warn('Error deleting signature:', error);
        }
      }
      
      if (ticket.driverSignaturePath) {
        try {
          const fileInfo = await FileSystem.getInfoAsync(ticket.driverSignaturePath);
          if (fileInfo.exists) {
            await FileSystem.deleteAsync(ticket.driverSignaturePath);
          }
        } catch (error) {
          console.warn('Error deleting driver signature:', error);
        }
      }
    }
  } catch (error) {
    console.error('Error removing pending ticket:', error);
  }
};

/**
 * Get count of pending tickets
 * @returns {Promise<number>}
 */
export const getPendingTicketsCount = async () => {
  try {
    const pendingTickets = await getPendingTickets();
    return pendingTickets.length;
  } catch (error) {
    console.error('Error getting pending tickets count:', error);
    return 0;
  }
};

/**
 * Clear all pending tickets (use with caution)
 */
export const clearPendingTickets = async () => {
  try {
    await AsyncStorage.removeItem(PENDING_TICKETS_KEY);
    
    // Clean up all files
    try {
      const dirInfo = await FileSystem.getInfoAsync(OFFLINE_FILES_DIR);
      if (dirInfo.exists) {
        await FileSystem.deleteAsync(OFFLINE_FILES_DIR, { idempotent: true });
      }
    } catch (error) {
      console.warn('Error cleaning up offline files:', error);
    }
  } catch (error) {
    console.error('Error clearing pending tickets:', error);
  }
};

export default {
  saveTicketOffline,
  getPendingTickets,
  getPendingTicket,
  updateTicketStatus,
  removePendingTicket,
  getPendingTicketsCount,
  clearPendingTickets,
};

