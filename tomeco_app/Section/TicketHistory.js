import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Modal,
  Image,
  Dimensions,
  Linking,
  Platform,
} from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
// Note: Install expo-print and expo-file-system:
// npx expo install expo-print expo-file-system
let Print, FileSystem;
try {
  Print = require('expo-print');
  // Use legacy API for readAsStringAsync
  FileSystem = require('expo-file-system/legacy');
} catch (e) {
  console.warn('expo-print or expo-file-system not installed');
}
import API from '../config/api';
import { isOnline } from '../services/NetworkStatus';
import { getPendingTickets, removePendingTicket } from '../services/OfflineStorage';
import { getAuthToken, isValidToken, handleAuthError } from '../services/AuthService';

export default function TicketHistory({ navigation, route }) {
  const { enforcer } = route.params || {};
  const [tickets, setTickets] = useState([]);
  const [pendingTickets, setPendingTickets] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setIsRefreshing] = useState(false);
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [showTicketModal, setShowTicketModal] = useState(false);
  const [ticketDetails, setTicketDetails] = useState(null);
  const [loadingDetails, setLoadingDetails] = useState(false);
  const [isPrinting, setIsPrinting] = useState(false);
  const ticketPrintViewRef = useRef(null);
  const [showPrintPreview, setShowPrintPreview] = useState(false);

  useEffect(() => {
    loadTickets();
  }, []);

  // Refresh tickets when screen comes into focus
  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => {
      console.log('TicketHistory - Screen focused, refreshing tickets...');
      loadTickets();
    });

    return unsubscribe;
  }, [navigation]);

  const loadTickets = async () => {
    setIsLoading(true);
    try {
      // Load pending offline tickets
      const pending = await getPendingTickets();
      setPendingTickets(pending);

      // Load tickets from API if online
      if (isOnline()) {
        await loadTicketsFromAPI();
      }
    } catch (error) {
      console.error('Error loading tickets:', error);
      Alert.alert('Error', 'Failed to load tickets');
    } finally {
      setIsLoading(false);
    }
  };

  const loadTicketsFromAPI = async () => {
    try {
      const token = await getAuthToken();
      
      // Skip if invalid token
      if (!token || !isValidToken(token)) {
        console.warn('TicketHistory - Invalid or missing token');
        return;
      }

      // Get enforcer data to debug
      const enforcerDataStr = await AsyncStorage.getItem('enforcer_data');
      const enforcerData = enforcerDataStr ? JSON.parse(enforcerDataStr) : null;
      console.log('TicketHistory - Loading tickets for enforcer:', enforcerData?.fullname || 'unknown');

      console.log('TicketHistory - Fetching from:', API.TICKETS.LIST);
      const response = await fetch(API.TICKETS.LIST, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
      
      console.log('TicketHistory - Response status:', response.status);

      // Handle authentication errors
      if (response.status === 401 || response.status === 403) {
        await handleAuthError(response);
        Alert.alert(
          'Authentication Required',
          'Your session has expired. Please login again to view tickets.',
          [{ text: 'OK' }]
        );
        return;
      }

      if (response.ok) {
        const data = await response.json();
        console.log('TicketHistory - API Response:', JSON.stringify(data, null, 2));
        
        // Handle different response formats
        let ticketsArray = [];
        if (Array.isArray(data)) {
          ticketsArray = data;
        } else if (data.data && Array.isArray(data.data)) {
          ticketsArray = data.data;
        } else if (data.tickets && Array.isArray(data.tickets)) {
          ticketsArray = data.tickets;
        }
        
        console.log('TicketHistory - Parsed tickets count:', ticketsArray.length);
        if (ticketsArray.length > 0) {
          console.log('TicketHistory - First ticket sample:', JSON.stringify(ticketsArray[0], null, 2));
          // Log all ticket officer names for debugging
          console.log('TicketHistory - All ticket officer names:', ticketsArray.map(t => ({
            id: t.id,
            apprehending_officer: t.apprehending_officer,
            issued_by: t.issued_by,
          })));
        } else {
          console.warn('TicketHistory - No tickets found!');
          console.log('TicketHistory - Enforcer fullname used for query:', enforcerData?.fullname);
        }
        
        setTickets(ticketsArray);
      } else {
        const errorText = await response.text();
        console.error('TicketHistory - Failed to load tickets:', {
          status: response.status,
          statusText: response.statusText,
          error: errorText.substring(0, 200),
        });
        
        if (response.status === 401 || response.status === 403) {
          Alert.alert(
            'Authentication Required',
            'Please login again to view tickets.',
            [{ text: 'OK' }]
          );
        } else {
          Alert.alert(
            'Error',
            `Failed to load tickets (${response.status}). Please try again.`,
            [{ text: 'OK' }]
          );
        }
      }
    } catch (error) {
      console.error('Error fetching tickets from API:', error);
      Alert.alert(
        'Connection Error',
        'Failed to load tickets. Please check your internet connection and try again.',
        [{ text: 'OK' }]
      );
    }
  };

  const onRefresh = async () => {
    setIsRefreshing(true);
    await loadTickets();
    setIsRefreshing(false);
  };

  const handleDeleteOfflineTicket = async (ticketId, driverName) => {
    Alert.alert(
      'Delete Offline Ticket',
      `Are you sure you want to delete the offline ticket for ${driverName}? This action cannot be undone.`,
      [
        {
          text: 'Cancel',
          style: 'cancel',
        },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            try {
              await removePendingTicket(ticketId);
              Alert.alert('Success', 'Offline ticket deleted successfully.');
              // Reload tickets to update the list
              await loadTickets();
            } catch (error) {
              console.error('Error deleting offline ticket:', error);
              Alert.alert('Error', 'Failed to delete offline ticket. Please try again.');
            }
          },
        },
      ]
    );
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      });
    } catch (e) {
      return dateString;
    }
  };

  const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    try {
      const date = new Date(dateString);
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch (e) {
      return dateString;
    }
  };

  const formatCitationNumber = (citationNumber) => {
    if (!citationNumber) return '';
    
    // If already in web admin format (YYYY-NNNNNN), return as is
    if (/^\d{4}-\d{6}$/.test(citationNumber)) {
      return citationNumber;
    }
    
    // Convert from TOMECO-YYYY-XXXX format to YYYY-NNNNNN format
    // Example: TOMECO-2024-0001 -> 2024-000001
    const match = citationNumber.match(/TOMECO-(\d{4})-(\d+)/);
    if (match) {
      const year = match[1];
      const number = match[2];
      // Pad number to 6 digits
      const paddedNumber = number.padStart(6, '0');
      return `${year}-${paddedNumber}`;
    }
    
    // If format doesn't match, return as is
    return citationNumber;
  };

  const formatTime = (timeString) => {
    if (!timeString) return 'N/A';
    try {
      // Handle both Date objects and time strings
      if (timeString instanceof Date) {
        return timeString.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit',
        });
      }
      // If it's a time string like "14:30"
      if (typeof timeString === 'string' && timeString.includes(':')) {
        const [hours, minutes] = timeString.split(':');
        const date = new Date();
        date.setHours(parseInt(hours), parseInt(minutes));
        return date.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit',
        });
      }
      return timeString;
    } catch (e) {
      return timeString;
    }
  };

  const loadTicketDetails = async (ticket) => {
    setSelectedTicket(ticket);
    setShowTicketModal(true);
    setLoadingDetails(true);
    setTicketDetails(null);

    try {
      // If it's an offline ticket, use the stored data
      if (ticket.isOffline) {
        // Get full ticket data - check for _fullTicket first, then data property, then use ticket itself
        let ticketData;
        if (ticket._fullTicket) {
          // Use the full ticket object stored in _fullTicket
          ticketData = {
            ...ticket._fullTicket.data, // All ticket fields from data
            id: ticket._fullTicket.id,
            localId: ticket._fullTicket.id,
            status: ticket._fullTicket.status,
            createdAt: ticket._fullTicket.createdAt,
            created_at: ticket._fullTicket.createdAt,
            // Include images if available (these are local file paths for offline tickets)
            images: ticket._fullTicket.images || [],
            signaturePath: ticket._fullTicket.signaturePath,
            driverSignaturePath: ticket._fullTicket.driverSignaturePath,
            // Ensure all fields from ticket.data are included
            violations: ticket._fullTicket.data?.violations || ticket.violations,
            violations_others_text: ticket._fullTicket.data?.violations_others_text || ticket.violations_others_text,
            price: ticket._fullTicket.data?.price || ticket.price,
          };
        } else if (ticket.data) {
          // Fallback: use data property if available
          ticketData = { 
            ...ticket.data, 
            ...ticket,
            images: ticket.images || ticket.data.images || [],
          };
        } else {
          // Last resort: use ticket itself (should have all fields from spread)
          ticketData = {
            ...ticket,
            images: ticket.images || [],
          };
        }
        setTicketDetails(ticketData);
        setLoadingDetails(false);
        return;
      }

      // For online tickets, fetch full details from API
      if (ticket.id) {
        const token = await getAuthToken();
        if (!token || !isValidToken(token)) {
          // Fallback to using available ticket data
          setTicketDetails(ticket);
          setLoadingDetails(false);
          return;
        }

        try {
          const response = await fetch(API.TICKETS.SHOW(ticket.id), {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json',
            },
          });

          if (response.ok) {
            const data = await response.json();
            const ticketData = data.data || data;
            setTicketDetails(ticketData);
          } else {
            // Fallback to using available ticket data
            setTicketDetails(ticket);
          }
        } catch (error) {
          console.error('Error fetching ticket details:', error);
          // Fallback to using available ticket data
          setTicketDetails(ticket);
        }
      } else {
        // No ID, use available data
        setTicketDetails(ticket);
      }
    } catch (error) {
      console.error('Error loading ticket details:', error);
      setTicketDetails(ticket);
    } finally {
      setLoadingDetails(false);
    }
  };

  const getImageUrl = (imagePath, isOffline = false) => {
    if (!imagePath) return null;
    
    // For offline tickets, imagePath might be a local file path
    if (isOffline && typeof imagePath === 'object' && imagePath.localPath) {
      // Return the local file path for offline images
      return imagePath.localPath;
    }
    
    // If it's already a full URL, return it
    if (typeof imagePath === 'string' && (imagePath.startsWith('http://') || imagePath.startsWith('https://'))) {
      return imagePath;
    }
    
    // If it's a local file path (starts with file://), return as is
    if (typeof imagePath === 'string' && imagePath.startsWith('file://')) {
      return imagePath;
    }
    
    // Construct full URL using Laravel base URL
    const baseUrl = API.LARAVEL_BASE_URL.replace('/api', '');
    
    // If it starts with /storage, use as is
    if (typeof imagePath === 'string' && imagePath.startsWith('/storage/')) {
      return `${baseUrl}${imagePath}`;
    }
    
    // Otherwise, prepend /storage/
    return `${baseUrl}/storage/${imagePath}`;
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending':
        return '#ff9800';
      case 'syncing':
        return '#2196F3';
      case 'synced':
        return '#4CAF50';
      case 'failed':
        return '#f44336';
      default:
        return '#666';
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'pending':
        return 'schedule';
      case 'syncing':
        return 'sync';
      case 'synced':
        return 'check-circle';
      case 'failed':
        return 'error';
      default:
        return 'receipt';
    }
  };

  const generateTicketHTML = (ticket) => {
    if (!ticket) return '';

    // Simple text-only format - no images or signatures

    // Format dates
    const formatDateForPrint = (dateString) => {
      if (!dateString) return 'N/A';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
        });
      } catch (e) {
        return dateString;
      }
    };

    const formatTimeForPrint = (timeString) => {
      if (!timeString) return 'N/A';
      try {
        if (timeString instanceof Date) {
          return timeString.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
          });
        }
        if (typeof timeString === 'string' && timeString.includes(':')) {
          const [hours, minutes] = timeString.split(':');
          const date = new Date();
          date.setHours(parseInt(hours), parseInt(minutes));
          return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
          });
        }
        return timeString;
      } catch (e) {
        return timeString;
      }
    };

    const issuedDate = formatDateForPrint(ticket.issued_date || ticket.created_at);
    const issuedTime = formatTimeForPrint(ticket.issued_time);
    const courtDate = formatDateForPrint(ticket.court_date);
    const courtTime = formatTimeForPrint(ticket.court_time);

    const driverName = `${ticket.driver_firstname || ''} ${ticket.driver_middlename || ''} ${ticket.driver_lastname || ''}`.trim();
    const vehicleInfo = [ticket.vehicle_year, ticket.vehicle_make, ticket.vehicle_model]
      .filter(Boolean)
      .join(' ');

    // Paper width: 48mm = 1.89 inches = ~181 pixels at 96 DPI
    const paperWidthPx = 181;
    
    return `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="UTF-8">
        <style>
          @page {
            size: ${paperWidthPx}px auto;
            margin: 0;
          }
          body {
            margin: 0;
            padding: 8px;
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            width: ${paperWidthPx}px;
            color: #000;
          }
          .header {
            text-align: center;
            margin-bottom: 8px;
          }
          .header-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
          }
          .header-subtitle {
            font-size: 8px;
          }
          .section {
            margin-bottom: 6px;
            border-top: 1px solid #000;
            padding-top: 4px;
          }
          .section-title {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 3px;
            text-transform: uppercase;
          }
          .field {
            margin-bottom: 2px;
            font-size: 8px;
          }
          .field-label {
            font-weight: bold;
            display: inline;
          }
          .field-value {
            display: inline;
          }
          .violations-list {
            margin-left: 8px;
          }
          .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 8px;
          }
          .total-fine {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin: 6px 0;
            border: 1px solid #000;
            padding: 4px;
          }
        </style>
      </head>
      <body>
        <div class="header">
          <div class="header-title">REPUBLIC OF THE PHILIPPINES</div>
          <div class="header-title">CITY OF TACLOBAN</div>
          <div class="header-title">CITY MAYOR'S OFFICE</div>
          <div class="header-subtitle">TOMECO</div>
          <div class="header-subtitle">(Traffic Operations, Management, Enforcement and Control Office)</div>
        </div>

        <div style="text-align: center; font-weight: bold; border: 2px solid #000; padding: 4px; margin-bottom: 6px;">
          TRAFFIC VIOLATION RECEIPT/<br>
          CITATION TICKET
        </div>

        ${ticket.citation_number ? `<div class="field"><span class="field-label">Citation No.:</span> <span class="field-value">${formatCitationNumber(ticket.citation_number)}</span></div>` : ''}

        <div class="section">
          <div class="section-title">Driver Information</div>
          ${driverName ? `<div class="field"><span class="field-label">Name:</span> <span class="field-value">${driverName}</span></div>` : ''}
          ${ticket.driver_address ? `<div class="field"><span class="field-label">Address:</span> <span class="field-value">${ticket.driver_address}</span></div>` : ''}
          ${ticket.driver_contact ? `<div class="field"><span class="field-label">Contact:</span> <span class="field-value">${ticket.driver_contact}</span></div>` : ''}
          ${ticket.dl_number ? `<div class="field"><span class="field-label">DL No.:</span> <span class="field-value">${ticket.dl_number}${ticket.dl_type ? ' (' + ticket.dl_type + ')' : ''}</span></div>` : ''}
        </div>

        <div class="section">
          <div class="section-title">Vehicle Information</div>
          ${ticket.plate_number ? `<div class="field"><span class="field-label">Plate No.:</span> <span class="field-value">${ticket.plate_number}</span></div>` : ''}
          ${ticket.cr_number ? `<div class="field"><span class="field-label">CR No.:</span> <span class="field-value">${ticket.cr_number}</span></div>` : ''}
          ${vehicleInfo ? `<div class="field"><span class="field-label">Vehicle:</span> <span class="field-value">${vehicleInfo}</span></div>` : ''}
          ${ticket.vehicle_type ? `<div class="field"><span class="field-label">Type:</span> <span class="field-value">${ticket.vehicle_type}</span></div>` : ''}
          ${ticket.or_number ? `<div class="field"><span class="field-label">OR No.:</span> <span class="field-value">${ticket.or_number}</span></div>` : ''}
          ${ticket.owner_name ? `<div class="field"><span class="field-label">Owner:</span> <span class="field-value">${ticket.owner_name}</span></div>` : ''}
        </div>

        <div class="section">
          <div class="section-title">Violations</div>
          ${ticket.violations && Array.isArray(ticket.violations) && ticket.violations.length > 0
            ? `<div class="violations-list">${ticket.violations.map((v, i) => `${i + 1}. ${v}`).join('<br>')}</div>`
            : ''}
          ${ticket.violations_others_text ? `<div class="field"><span class="field-label">Other:</span> <span class="field-value">${ticket.violations_others_text}</span></div>` : ''}
        </div>

        <div class="section">
          <div class="section-title">Incident Information</div>
          ${ticket.place ? `<div class="field"><span class="field-label">Location:</span> <span class="field-value">${ticket.place}</span></div>` : ''}
          ${issuedDate ? `<div class="field"><span class="field-label">Date:</span> <span class="field-value">${issuedDate}</span></div>` : ''}
          ${issuedTime ? `<div class="field"><span class="field-label">Time:</span> <span class="field-value">${issuedTime}</span></div>` : ''}
          ${ticket.accident !== null && ticket.accident !== undefined
            ? `<div class="field"><span class="field-label">Accident:</span> <span class="field-value">${ticket.accident ? 'Yes' : 'No'}</span></div>`
            : ''}
          ${ticket.incident_notes ? `<div class="field"><span class="field-label">Notes:</span> <span class="field-value">${ticket.incident_notes}</span></div>` : ''}
          ${ticket.remarks ? `<div class="field"><span class="field-label">Remarks:</span> <span class="field-value">${ticket.remarks}</span></div>` : ''}
        </div>

        ${courtDate || courtTime ? `
        <div class="section">
          <div class="section-title">Court Information</div>
          ${courtDate ? `<div class="field"><span class="field-label">Court Date:</span> <span class="field-value">${courtDate}</span></div>` : ''}
          ${courtTime ? `<div class="field"><span class="field-label">Court Time:</span> <span class="field-value">${courtTime}</span></div>` : ''}
        </div>
        ` : ''}

        ${ticket.apprehending_officer || ticket.issued_by || ticket.tomeco_did ? `
        <div class="section">
          <div class="section-title">Officer Information</div>
          ${ticket.apprehending_officer ? `<div class="field"><span class="field-label">Officer:</span> <span class="field-value">${ticket.apprehending_officer}</span></div>` : ''}
          ${ticket.issued_by ? `<div class="field"><span class="field-label">Issued By:</span> <span class="field-value">${ticket.issued_by}</span></div>` : ''}
          ${ticket.tomeco_did ? `<div class="field"><span class="field-label">TOMECO DID:</span> <span class="field-value">${ticket.tomeco_did}</span></div>` : ''}
        </div>
        ` : ''}


        ${ticket.price ? `
        <div class="total-fine">
          TOTAL FINE: PHP ${parseFloat(ticket.price).toFixed(2)}
        </div>
        ` : ''}

        <div class="footer">
          Thank you for your cooperation.
        </div>
      </body>
      </html>
    `;
  };

  const formatTicketForPrint = (ticket) => {
    if (!ticket) return '';

    // Simple plain text format - no ESC/POS commands that might cause encoding issues
    // Use simple text formatting that all printers can handle
    let printContent = '';
    
    // Header - Use spacing for centering effect
    printContent += '\n\n';
    printContent += '     REPUBLIC OF THE PHILIPPINES\n';
    printContent += '         CITY OF TACLOBAN\n';
    printContent += '       CITY MAYOR\'S OFFICE\n';
    printContent += '\n';
    printContent += '            TOMECO\n';
    printContent += '(Traffic Operations, Management,\n';
    printContent += 'Enforcement and Control Office)\n';
    printContent += '\n';
    printContent += '================================\n';
    
    // Title
    printContent += '\n';
    printContent += '  TRAFFIC VIOLATION RECEIPT/\n';
    printContent += '      CITATION TICKET\n';
    printContent += '\n';
    printContent += '================================\n';
    
    // Citation Number
    if (ticket.citation_number) {
      const formattedCitation = formatCitationNumber(ticket.citation_number);
      printContent += 'Citation Ticket #: ' + formattedCitation + '\n';
    }
    
    printContent += '\n';
    
    // Driver Information
    printContent += 'DRIVER INFORMATION\n';
    printContent += '--------------------------------\n';
    
    if (ticket.driver_firstname || ticket.driver_lastname) {
      const driverName = `${ticket.driver_firstname || ''} ${ticket.driver_middlename || ''} ${ticket.driver_lastname || ''}`.trim();
      printContent += 'Name: ' + driverName + '\n';
    }
    
    if (ticket.driver_address) {
      printContent += 'Address: ' + ticket.driver_address + '\n';
    }
    
    if (ticket.driver_contact) {
      printContent += 'Contact: ' + ticket.driver_contact + '\n';
    }
    
    if (ticket.dl_number) {
      printContent += 'DL No.: ' + ticket.dl_number;
      if (ticket.dl_type) {
        printContent += ' (' + ticket.dl_type + ')';
      }
      printContent += '\n';
    }
    
    printContent += '\n';
    
    // Vehicle Information
    printContent += 'VEHICLE INFORMATION\n';
    printContent += '--------------------------------\n';
    
    if (ticket.plate_number) {
      printContent += 'Plate No.: ' + ticket.plate_number + '\n';
    }
    
    if (ticket.cr_number) {
      printContent += 'CR No.: ' + ticket.cr_number + '\n';
    }
    
    if (ticket.vehicle_year || ticket.vehicle_make || ticket.vehicle_model) {
      const vehicle = [ticket.vehicle_year, ticket.vehicle_make, ticket.vehicle_model]
        .filter(Boolean)
        .join(' ');
      if (vehicle) {
        printContent += 'Vehicle: ' + vehicle + '\n';
      }
    }
    
    if (ticket.vehicle_type) {
      printContent += 'Type: ' + ticket.vehicle_type + '\n';
    }
    
    if (ticket.or_number) {
      printContent += 'OR No.: ' + ticket.or_number + '\n';
    }
    
    if (ticket.owner_name) {
      printContent += 'Owner: ' + ticket.owner_name + '\n';
    }
    
    printContent += '\n';
    
    // Violations
    printContent += 'VIOLATIONS\n';
    printContent += '--------------------------------\n';
    
    if (ticket.violations && Array.isArray(ticket.violations) && ticket.violations.length > 0) {
      ticket.violations.forEach((violation, index) => {
        printContent += (index + 1) + '. ' + violation + '\n';
      });
    }
    
    if (ticket.violations_others_text) {
      printContent += 'Other: ' + ticket.violations_others_text + '\n';
    }
    
    printContent += '\n';
    
    // Incident Information
    printContent += 'INCIDENT INFORMATION\n';
    printContent += '--------------------------------\n';
    
    if (ticket.place) {
      printContent += 'Location: ' + ticket.place + '\n';
    }
    
    if (ticket.issued_date || ticket.created_at) {
      const date = ticket.issued_date || ticket.created_at;
      printContent += 'Date: ' + formatDate(date) + '\n';
    }
    
    if (ticket.issued_time) {
      printContent += 'Time: ' + formatTime(ticket.issued_time) + '\n';
    }
    
    if (ticket.accident !== null && ticket.accident !== undefined) {
      printContent += 'Accident: ' + (ticket.accident ? 'Yes' : 'No') + '\n';
    }
    
    if (ticket.incident_notes) {
      printContent += 'Notes: ' + ticket.incident_notes + '\n';
    }
    
    if (ticket.remarks) {
      printContent += 'Remarks: ' + ticket.remarks + '\n';
    }
    
    printContent += '\n';
    
    // Court Information
    if (ticket.court_date || ticket.court_time) {
      printContent += 'COURT INFORMATION\n';
      printContent += '--------------------------------\n';
      
      if (ticket.court_date) {
        printContent += 'Court Date: ' + formatDate(ticket.court_date) + '\n';
      }
      
      if (ticket.court_time) {
        printContent += 'Court Time: ' + formatTime(ticket.court_time) + '\n';
      }
      
      printContent += '\n';
    }
    
    // Officer Information
    if (ticket.apprehending_officer || ticket.issued_by || ticket.tomeco_did) {
      printContent += 'OFFICER INFORMATION\n';
      printContent += '--------------------------------\n';
      
      if (ticket.apprehending_officer) {
        printContent += 'Officer: ' + ticket.apprehending_officer + '\n';
      }
      
      if (ticket.issued_by) {
        printContent += 'Issued By: ' + ticket.issued_by + '\n';
      }
      
      if (ticket.tomeco_did) {
        printContent += 'TOMECO DID: ' + ticket.tomeco_did + '\n';
      }
      
      printContent += '\n';
    }
    
    // Price
    if (ticket.price) {
      printContent += '\n';
      printContent += '================================\n';
      printContent += '  TOTAL FINE: PHP ' + parseFloat(ticket.price).toFixed(2) + '\n';
      printContent += '================================\n';
      printContent += '\n';
    }
    
    // Signatures Section
    printContent += '\n';
    printContent += 'SIGNATURES\n';
    printContent += '--------------------------------\n';
    
    if (ticket.driver_signature) {
      printContent += '\n';
      printContent += 'Driver Signature: [SIGNED]\n';
      printContent += '\n';
    } else {
      printContent += '\n';
      printContent += 'Driver Signature: [NOT SIGNED]\n';
      printContent += '\n';
    }
    
    if (ticket.signature) {
      printContent += 'Officer Signature: [SIGNED]\n';
      printContent += '\n';
    } else {
      printContent += 'Officer Signature: [NOT SIGNED]\n';
      printContent += '\n';
    }
    
    printContent += '--------------------------------\n';
    
    // Payment Information
    printContent += '\n';
    printContent += 'PAY ONLINE\n';
    printContent += '--------------------------------\n';
    printContent += 'Visit the link below in your\n';
    printContent += 'browser to pay online:\n';
    printContent += '\n';
    // Use API config to get the violator portal URL
    const violatorUrl = API.LARAVEL_BASE_URL + '/violator/portal';
    printContent += violatorUrl + '\n';
    printContent += '\n';
    printContent += '--------------------------------\n';
    
    // Footer
    printContent += '\n';
    printContent += '  Thank you for your cooperation.\n';
    printContent += '\n\n\n';
    
    return printContent;
  };

  // Simple base64 encoder for React Native (since btoa might not be available)
  const base64Encode = (str) => {
    try {
      // Try using btoa if available (some React Native environments have it)
      if (typeof btoa !== 'undefined') {
        return btoa(unescape(encodeURIComponent(str)));
      }
      // Fallback: Use a simple base64 implementation
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
      let output = '';
      for (let i = 0; i < str.length; i += 3) {
        const a = str.charCodeAt(i);
        const b = i + 1 < str.length ? str.charCodeAt(i + 1) : 0;
        const c = i + 2 < str.length ? str.charCodeAt(i + 2) : 0;
        const bitmap = (a << 16) | (b << 8) | c;
        output += chars.charAt((bitmap >> 18) & 63);
        output += chars.charAt((bitmap >> 12) & 63);
        output += i + 1 < str.length ? chars.charAt((bitmap >> 6) & 63) : '=';
        output += i + 2 < str.length ? chars.charAt(bitmap & 63) : '=';
      }
      return output;
    } catch (e) {
      console.error('Base64 encoding error:', e);
      return null;
    }
  };

  const handlePrint = async () => {
    if (!ticketDetails) {
      Alert.alert('Error', 'No ticket data available for printing');
      return;
    }

    // Check if required packages are installed
    if (!Print || !FileSystem) {
      Alert.alert(
        'Packages Required',
        'Please install required packages:\n\nnpx expo install expo-print expo-file-system\n\nThen restart the app.',
        [
          { text: 'OK', onPress: () => setIsPrinting(false) },
        ]
      );
      return;
    }

    setIsPrinting(true);

    try {
      // Ensure we have all ticket data for printing
      // If it's an offline ticket, make sure we have the full data
      let printTicketData = ticketDetails;
      if (selectedTicket?.isOffline && selectedTicket?._fullTicket) {
        // Merge full ticket data for printing
        printTicketData = {
          ...selectedTicket._fullTicket.data,
          ...ticketDetails, // Override with any updated details
        };
      }
      
      // Generate HTML for the ticket
      const htmlContent = generateTicketHTML(printTicketData);
      
      console.log('Generating PDF from HTML...');
      
      // Generate PDF using expo-print
      // Paper size: 48mm = 1.89 inches width = ~181 pixels at 96 DPI
      const { uri } = await Print.printToFileAsync({
        html: htmlContent,
        width: 181, // 48mm in pixels at 96 DPI
        height: undefined, // Auto height
        base64: false,
      });

      console.log('PDF generated at:', uri);

      // Convert PDF to image for RawBT (RawBT works better with images)
      // Use expo-print's ability to generate as image, or convert PDF
      // For now, let's use the ESC/POS text format which is more reliable for thermal printers
      // Use the printTicketData we already prepared above
      await sendTextToRawBT(printTicketData);

    } catch (error) {
      console.error('Error generating print:', error);
      Alert.alert('Error', 'Failed to generate print: ' + error.message);
      setIsPrinting(false);
    }
  };

  const sendTextToRawBT = async (ticket) => {
    try {
      // Use simple plain text format - no binary commands
      const printContent = formatTicketForPrint(ticket);
      
      console.log('Sending plain text to RawBT...');
      console.log('Print content length:', printContent.length);
      console.log('First 300 chars (preview):', printContent.substring(0, 300));
      
      // Method 1: Try HTTP POST to local RawBT server (most reliable)
      // Send as plain text/UTF-8
      try {
        console.log('Attempting HTTP POST to RawBT server...');
        const response = await fetch('http://localhost:9100/', {
          method: 'POST',
          headers: {
            'Content-Type': 'text/plain; charset=utf-8',
          },
          body: printContent, // Send as plain text string
        });
        
        console.log('HTTP POST response status:', response.status);
        
        if (response.ok || response.status === 0) {
          Alert.alert('Success', 'Print job sent to RawBT via HTTP POST.');
          setIsPrinting(false);
          return;
        }
      } catch (httpError) {
        console.log('HTTP POST method failed:', httpError);
      }
      
      // Method 2: Try RawBT with URL-encoded text (plain text, no base64)
      // Use URL encoding to send plain text via intent URL
      try {
        const urlEncodedContent = encodeURIComponent(printContent);
        console.log('Trying URL-encoded text format...');
        console.log('URL-encoded length:', urlEncodedContent.length);
        
        const canOpenRawBT = await Linking.canOpenURL('rawbt:');
        console.log('Can open RawBT URL:', canOpenRawBT);
        
        if (canOpenRawBT) {
          // Use rawbt: with URL-encoded text (plain text format)
          console.log('Sending via rawbt: with URL-encoded text...');
          await Linking.openURL(`rawbt:${urlEncodedContent}`);
          Alert.alert('Success', 'Print job sent to RawBT.');
          setIsPrinting(false);
          return;
        }
      } catch (urlError) {
        console.log('URL-encoded format failed:', urlError);
      }
      
      // If all methods fail
      Alert.alert(
        'RawBT Not Found',
        'RawBT app is not installed or not running. Please install RawBT from Google Play Store and ensure it is running.\n\nCheck console logs for debug information.',
        [
          { text: 'Cancel', style: 'cancel' },
          {
            text: 'Install RawBT',
            onPress: () => {
              if (Platform.OS === 'android') {
                Linking.openURL('https://play.google.com/store/apps/details?id=ru.a402d.rawbtprinter');
              }
              setIsPrinting(false);
            },
          },
        ]
      );
    } catch (error) {
      console.error('Error sending to RawBT:', error);
      Alert.alert('Error', 'Failed to send print job: ' + error.message + '\n\nCheck console for details.');
      setIsPrinting(false);
    }
  };

  // Base64 to binary converter (since atob might not be available)
  const base64ToBinary = (base64) => {
    try {
      if (typeof atob !== 'undefined') {
        const binaryString = atob(base64);
        const bytes = new Uint8Array(binaryString.length);
        for (let i = 0; i < binaryString.length; i++) {
          bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes;
      }
      // Manual base64 decode
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
      let output = '';
      let i = 0;
      base64 = base64.replace(/[^A-Za-z0-9\+\/\=]/g, '');
      while (i < base64.length) {
        const enc1 = chars.indexOf(base64.charAt(i++));
        const enc2 = chars.indexOf(base64.charAt(i++));
        const enc3 = chars.indexOf(base64.charAt(i++));
        const enc4 = chars.indexOf(base64.charAt(i++));
        const bitmap = (enc1 << 18) | (enc2 << 12) | (enc3 << 6) | enc4;
        if (enc3 !== 64) output += String.fromCharCode((bitmap >> 16) & 255);
        if (enc4 !== 64) output += String.fromCharCode((bitmap >> 8) & 255);
        if (enc4 !== 64) output += String.fromCharCode(bitmap & 255);
      }
      const bytes = new Uint8Array(output.length);
      for (let i = 0; i < output.length; i++) {
        bytes[i] = output.charCodeAt(i);
      }
      return bytes;
    } catch (e) {
      console.error('Base64 decode error:', e);
      return null;
    }
  };

  const sendPDFToRawBT = async (pdfBase64, pdfUri) => {
    try {
      // Method 1: Try HTTP POST with PDF
      try {
        console.log('Attempting HTTP POST to RawBT server with PDF...');
        // Convert base64 to binary
        const bytes = base64ToBinary(pdfBase64);
        if (!bytes) {
          throw new Error('Failed to convert PDF to binary');
        }

        const response = await fetch('http://localhost:9100/', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/pdf',
          },
          body: bytes,
        });

        if (response.ok || response.status === 0) {
          Alert.alert('Success', 'PDF sent to RawBT via HTTP POST.');
          setIsPrinting(false);
          return;
        }
      } catch (httpError) {
        console.log('HTTP POST with PDF failed:', httpError);
      }

      // Method 2: Try RawBT intent URL with PDF base64
      const rawbtUrl = `rawbt://send?base64=${pdfBase64}`;
      
      try {
        const canOpen = await Linking.canOpenURL(rawbtUrl.substring(0, 100)); // Check with shortened URL
        if (canOpen) {
          await Linking.openURL(rawbtUrl);
          Alert.alert('Success', 'PDF sent to RawBT via Intent URL.');
          setIsPrinting(false);
          return;
        }
      } catch (linkError) {
        console.log('Intent URL method failed:', linkError);
      }

      // Method 3: Use expo-print's printAsync to print directly
      try {
        await Print.printAsync({
          uri: pdfUri,
        });
        Alert.alert('Success', 'Print dialog opened. Please select your printer.');
        setIsPrinting(false);
        return;
      } catch (printError) {
        console.log('Direct print failed:', printError);
      }

      // If all methods fail
      Alert.alert(
        'Print Options',
        'Could not send to RawBT automatically. PDF has been generated. You can:\n\n1. Use the system print dialog\n2. Ensure RawBT is installed and running',
        [
          { text: 'Cancel', style: 'cancel' },
          {
            text: 'Try System Print',
            onPress: async () => {
              try {
                await Print.printAsync({ uri: pdfUri });
              } catch (e) {
                Alert.alert('Error', 'System print failed: ' + e.message);
              }
              setIsPrinting(false);
            },
          },
          {
            text: 'Install RawBT',
            onPress: () => {
              if (Platform.OS === 'android') {
                Linking.openURL('https://play.google.com/store/apps/details?id=ru.a402d.rawbtprinter');
              }
              setIsPrinting(false);
            },
          },
        ]
      );
    } catch (error) {
      console.error('Error sending PDF to RawBT:', error);
      Alert.alert('Error', 'Failed to send PDF: ' + error.message);
      setIsPrinting(false);
    }
  };

  const sendPrintJob = async (printContent) => {
    try {
      // Method 1: Try HTTP POST to local RawBT server (preferred - most reliable)
      // This sends raw ESC/POS commands directly as binary data
      try {
        console.log('Attempting HTTP POST to RawBT server...');
        const response = await fetch('http://localhost:9100/', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/octet-stream',
          },
          body: printContent, // Send as string - fetch will handle encoding
        });
        
        console.log('HTTP POST response status:', response.status);
        console.log('HTTP POST response ok:', response.ok);
        
        if (response.ok || response.status === 0) {
          Alert.alert('Success', 'Print job sent to RawBT via HTTP POST. Please check your printer connection.');
          setIsPrinting(false);
          return;
        }
      } catch (httpError) {
        console.log('HTTP POST method failed:', httpError);
        console.log('Error details:', httpError.message);
      }
      
      // Method 2: Try RawBT intent URL with base64 (most compatible)
      const base64Content = base64Encode(printContent);
      console.log('Base64 encoded length:', base64Content ? base64Content.length : 0);
      console.log('Base64 preview (first 100 chars):', base64Content ? base64Content.substring(0, 100) : 'null');
      
      if (base64Content) {
        // RawBT accepts base64 encoded ESC/POS commands
        const rawbtUrl = `rawbt://send?base64=${base64Content}`;
        console.log('RawBT URL length:', rawbtUrl.length);
        console.log('RawBT URL preview (first 200 chars):', rawbtUrl.substring(0, 200));
        
        try {
          // Check if RawBT app is installed
          const canOpen = await Linking.canOpenURL(rawbtUrl);
          console.log('Can open RawBT URL:', canOpen);
          
          if (canOpen) {
            await Linking.openURL(rawbtUrl);
            Alert.alert('Success', 'Print job sent to RawBT via Intent URL. Please check your printer connection.');
            setIsPrinting(false);
            return;
          }
        } catch (linkError) {
          console.log('Intent URL method failed:', linkError);
          console.log('Link error details:', linkError.message);
        }
      }
      
      // Method 3: Try alternative RawBT URL format
      try {
        const altUrl = `rawbt:${base64Encode(printContent)}`;
        console.log('Trying alternative URL format...');
        const canOpenAlt = await Linking.canOpenURL(altUrl);
        console.log('Can open alternative URL:', canOpenAlt);
        
        if (canOpenAlt) {
          await Linking.openURL(altUrl);
          Alert.alert('Success', 'Print job sent to RawBT via alternative URL. Please check your printer connection.');
          setIsPrinting(false);
          return;
        }
      } catch (altError) {
        console.log('Alternative URL method failed:', altError);
      }
      
      // If all methods fail, show installation instructions
      Alert.alert(
        'RawBT Not Found',
        'RawBT app is not installed or not running. Please install RawBT from Google Play Store and ensure it is running to enable printing.\n\nCheck console logs for debug information.',
        [
          { text: 'Cancel', style: 'cancel' },
          {
            text: 'Install',
            onPress: () => {
              if (Platform.OS === 'android') {
                Linking.openURL('https://play.google.com/store/apps/details?id=ru.a402d.rawbtprinter');
              }
            },
          },
        ]
      );
    } catch (error) {
      console.error('Error sending print job:', error);
      Alert.alert('Error', 'Failed to send print job: ' + error.message + '\n\nCheck console for details.');
    } finally {
      setIsPrinting(false);
    }
  };

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#962e2eff" />
        <Text style={styles.loadingText}>Loading tickets...</Text>
      </View>
    );
  }

  // Combine online and pending offline tickets
  const allTickets = [
    ...tickets.map(t => ({ ...t, isOffline: false })),
    ...pendingTickets.map(t => ({
      // Preserve the full ticket structure for offline tickets
      ...t.data, // Spread all ticket data fields
      id: t.id,
      localId: t.id,
      citation_number: t.data.citation_number || 'Pending',
      driver_firstname: t.data.driver_firstname,
      driver_lastname: t.data.driver_lastname,
      issued_date: t.createdAt,
      created_at: t.createdAt,
      createdAt: t.createdAt,
      status: t.status,
      isOffline: true,
      // Preserve the full ticket object structure for details access
      _fullTicket: t, // Store full ticket object for details modal
    })),
  ].sort((a, b) => {
    const dateA = new Date(a.created_at || a.createdAt || a.issued_date || 0);
    const dateB = new Date(b.created_at || b.createdAt || b.issued_date || 0);
    return dateB - dateA; // Newest first
  });

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <MaterialIcons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Ticket History</Text>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor="#962e2eff"
            colors={["#962e2eff"]}
          />
        }
      >
        {/* Status Summary */}
        <View style={styles.summaryCard}>
          <View style={styles.summaryItem}>
            <Text style={styles.summaryLabel}>Total Tickets</Text>
            <Text style={styles.summaryValue}>{allTickets.length}</Text>
          </View>
          <View style={styles.summaryItem}>
            <Text style={styles.summaryLabel}>Online</Text>
            <Text style={[styles.summaryValue, { color: '#4CAF50' }]}>
              {tickets.length}
            </Text>
          </View>
          {pendingTickets.length > 0 && (
            <View style={styles.summaryItem}>
              <Text style={styles.summaryLabel}>Pending</Text>
              <Text style={[styles.summaryValue, { color: '#ff9800' }]}>
                {pendingTickets.length}
              </Text>
            </View>
          )}
        </View>

        {/* Tickets List */}
        {allTickets.length === 0 ? (
          <View style={styles.emptyContainer}>
            <MaterialIcons name="receipt" size={64} color="#ccc" />
            <Text style={styles.emptyText}>No tickets found</Text>
            <Text style={styles.emptySubtext}>
              Start by issuing a new ticket
            </Text>
          </View>
        ) : (
          allTickets.map((ticket, index) => {
            const driverName = `${ticket.driver_firstname || ''} ${ticket.driver_lastname || ''}`.trim();
            const violations = ticket.violations || ticket.data?.violations || [];
            const status = ticket.isOffline ? (ticket.status || 'pending') : 'synced';
            const date = ticket.created_at || ticket.issued_date || ticket.createdAt;

            return (
              <View key={ticket.id || index} style={styles.ticketCard}>
                <TouchableOpacity
                  style={styles.ticketContent}
                  onPress={() => loadTicketDetails(ticket)}
                >
                  <View style={styles.ticketHeader}>
                    <View style={styles.ticketInfo}>
                      <Text style={styles.ticketDriverName}>
                        {driverName || 'Unknown Driver'}
                      </Text>
                      <Text style={styles.ticketDate}>
                        {formatDate(date)}
                      </Text>
                    </View>
                    <View
                      style={[
                        styles.statusBadge,
                        { backgroundColor: getStatusColor(status) },
                      ]}
                    >
                      <MaterialIcons
                        name={getStatusIcon(status)}
                        size={16}
                        color="#fff"
                      />
                      <Text style={styles.statusText}>
                        {status === 'synced' ? 'Synced' : status.charAt(0).toUpperCase() + status.slice(1)}
                      </Text>
                    </View>
                  </View>

                  <View style={styles.ticketDetails}>
                    <View style={styles.detailRow}>
                      <MaterialIcons name="gavel" size={16} color="#666" />
                      <Text style={styles.detailText}>
                        {violations.length} violation(s)
                      </Text>
                    </View>
                    {ticket.isOffline ? (
                      <View style={styles.detailRow}>
                        <MaterialIcons name="cloud-off" size={16} color="#ff9800" />
                        <Text style={[styles.detailText, { color: '#ff9800' }]}>
                          {status === 'pending' ? 'Pending sync' : status === 'syncing' ? 'Syncing...' : 'Offline'}
                        </Text>
                      </View>
                    ) : (
                      <View style={styles.detailRow}>
                        <MaterialIcons name="cloud-done" size={16} color="#4CAF50" />
                        <Text style={[styles.detailText, { color: '#4CAF50' }]}>
                          Synced online
                        </Text>
                      </View>
                    )}
                  </View>
                </TouchableOpacity>
                
                {/* Delete button for offline tickets only */}
                {ticket.isOffline && (
                  <TouchableOpacity
                    style={styles.deleteButton}
                    onPress={() => handleDeleteOfflineTicket(ticket.localId || ticket.id, driverName)}
                    activeOpacity={0.7}
                  >
                    <MaterialIcons name="delete" size={20} color="#f44336" />
                  </TouchableOpacity>
                )}
              </View>
            );
          })
        )}
      </ScrollView>

      {/* Ticket Details Modal */}
      <Modal
        visible={showTicketModal}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setShowTicketModal(false)}
      >
        <TouchableOpacity
          style={styles.modalOverlay}
          activeOpacity={1}
          onPress={() => setShowTicketModal(false)}
        >
          <TouchableOpacity
            activeOpacity={1}
            onPress={(e) => e.stopPropagation()}
            style={styles.modalContent}
          >
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Ticket Details</Text>
              <View style={styles.modalHeaderActions}>
                <TouchableOpacity
                  onPress={handlePrint}
                  style={styles.printButton}
                  disabled={isPrinting || !ticketDetails}
                >
                  {isPrinting ? (
                    <ActivityIndicator size="small" color="#962e2eff" />
                  ) : (
                    <MaterialIcons name="print" size={24} color="#962e2eff" />
                  )}
                </TouchableOpacity>
                <TouchableOpacity
                  onPress={() => setShowTicketModal(false)}
                  style={styles.closeButton}
                >
                  <MaterialIcons name="close" size={24} color="#333" />
                </TouchableOpacity>
              </View>
            </View>

            <ScrollView 
              style={styles.modalBody} 
              contentContainerStyle={styles.modalBodyContent}
              showsVerticalScrollIndicator={true}
              scrollEnabled={true}
              bounces={true}
              alwaysBounceVertical={false}
              decelerationRate="normal"
            >
                  {loadingDetails ? (
                    <View style={styles.loadingDetailsContainer}>
                      <ActivityIndicator size="large" color="#962e2eff" />
                      <Text style={styles.loadingDetailsText}>Loading ticket details...</Text>
                    </View>
                  ) : ticketDetails ? (
                    <View style={styles.detailSection}>
                      {/* Citation Number */}
                      {ticketDetails.citation_number && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="receipt" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Citation Number</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{formatCitationNumber(ticketDetails.citation_number)}</Text>
                          </View>
                        </View>
                      )}

                      {/* Driver Information */}
                      <View style={styles.sectionHeader}>
                        <Text style={styles.sectionHeaderText}>Driver Information</Text>
                      </View>

                      {(ticketDetails.driver_firstname || ticketDetails.driver_lastname) && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="person" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Driver Name</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>
                              {`${ticketDetails.driver_firstname || ''} ${ticketDetails.driver_middlename || ''} ${ticketDetails.driver_lastname || ''}`.trim() || 'N/A'}
                            </Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.driver_address && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="location-on" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Address</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.driver_address}</Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.driver_contact && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="phone" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Contact Number</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.driver_contact}</Text>
                          </View>
                        </View>
                      )}

                      {(ticketDetails.dl_number || ticketDetails.dl_type) && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="badge" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Driver's License</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>
                              {ticketDetails.dl_number || 'N/A'} {ticketDetails.dl_type ? `(${ticketDetails.dl_type})` : ''}
                            </Text>
                          </View>
                        </View>
                      )}

                      {/* Vehicle Information */}
                      <View style={styles.sectionHeader}>
                        <Text style={styles.sectionHeaderText}>Vehicle Information</Text>
                      </View>

                      {ticketDetails.plate_number && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="directions-car" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Plate Number</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.plate_number}</Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.cr_number && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="description" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>CR Number</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.cr_number}</Text>
                          </View>
                        </View>
                      )}

                      {(ticketDetails.vehicle_make || ticketDetails.vehicle_model || ticketDetails.vehicle_year) && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="build" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Vehicle</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>
                              {[ticketDetails.vehicle_year, ticketDetails.vehicle_make, ticketDetails.vehicle_model]
                                .filter(Boolean)
                                .join(' ') || 'N/A'}
                            </Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.vehicle_type && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="category" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Vehicle Type</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.vehicle_type}</Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.or_number && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="receipt-long" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>OR Number</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.or_number}</Text>
                          </View>
                        </View>
                      )}

                      {(ticketDetails.owner_name || ticketDetails.owner_address) && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="person-outline" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Owner</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>
                              {ticketDetails.owner_name || 'N/A'}
                              {ticketDetails.owner_address ? `\n${ticketDetails.owner_address}` : ''}
                            </Text>
                          </View>
                        </View>
                      )}

                      {/* Violations */}
                      <View style={styles.sectionHeader}>
                        <Text style={styles.sectionHeaderText}>Violations</Text>
                      </View>

                      {ticketDetails.violations && Array.isArray(ticketDetails.violations) && ticketDetails.violations.length > 0 ? (
                        ticketDetails.violations.map((violation, idx) => (
                          <View key={idx} style={styles.detailRow}>
                            <MaterialIcons name="gavel" size={20} color="#962e2eff" style={styles.detailIcon} />
                            <View style={styles.detailContent}>
                              <Text style={styles.detailValue} numberOfLines={0}>{violation}</Text>
                            </View>
                          </View>
                        ))
                      ) : (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="gavel" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailValue} numberOfLines={0}>No violations listed</Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.violations_others_text && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="note" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Other Violations</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.violations_others_text}</Text>
                          </View>
                        </View>
                      )}

                      {/* Incident Information */}
                      <View style={styles.sectionHeader}>
                        <Text style={styles.sectionHeaderText}>Incident Information</Text>
                      </View>

                      {ticketDetails.place && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="place" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Location</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.place}</Text>
                          </View>
                        </View>
                      )}

                      {(ticketDetails.issued_date || ticketDetails.created_at) && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="calendar-today" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Issued Date</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>
                              {formatDateTime(ticketDetails.issued_date || ticketDetails.created_at)}
                            </Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.issued_time && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="access-time" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Issued Time</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{formatTime(ticketDetails.issued_time)}</Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.accident !== null && ticketDetails.accident !== undefined && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="warning" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Accident</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>
                              {ticketDetails.accident ? 'Yes' : 'No'}
                            </Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.incident_notes && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="notes" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Incident Notes</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.incident_notes}</Text>
                          </View>
                        </View>
                      )}

                      {ticketDetails.remarks && (
                        <View style={styles.detailRow}>
                          <MaterialIcons name="comment" size={20} color="#962e2eff" style={styles.detailIcon} />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Remarks</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.remarks}</Text>
                          </View>
                        </View>
                      )}

                      {/* Court Information */}
                      {(ticketDetails.court_date || ticketDetails.court_time) && (
                        <>
                          <View style={styles.sectionHeader}>
                            <Text style={styles.sectionHeaderText}>Court Information</Text>
                          </View>

                          {ticketDetails.court_date && (
                            <View style={styles.detailRow}>
                              <MaterialIcons name="event" size={20} color="#962e2eff" style={styles.detailIcon} />
                              <View style={styles.detailContent}>
                                <Text style={styles.detailLabel}>Court Date</Text>
                                <Text style={styles.detailValue} numberOfLines={0}>
                                  {formatDate(ticketDetails.court_date)}
                                </Text>
                              </View>
                            </View>
                          )}

                          {ticketDetails.court_time && (
                            <View style={styles.detailRow}>
                              <MaterialIcons name="schedule" size={20} color="#962e2eff" style={styles.detailIcon} />
                              <View style={styles.detailContent}>
                                <Text style={styles.detailLabel}>Court Time</Text>
                                <Text style={styles.detailValue} numberOfLines={0}>
                                  {formatTime(ticketDetails.court_time)}
                                </Text>
                              </View>
                            </View>
                          )}
                        </>
                      )}

                      {/* Officer Information */}
                      {(ticketDetails.apprehending_officer || ticketDetails.issued_by || ticketDetails.tomeco_did) && (
                        <>
                          <View style={styles.sectionHeader}>
                            <Text style={styles.sectionHeaderText}>Officer Information</Text>
                          </View>

                          {ticketDetails.apprehending_officer && (
                            <View style={styles.detailRow}>
                              <MaterialIcons name="local-police" size={20} color="#962e2eff" style={styles.detailIcon} />
                              <View style={styles.detailContent}>
                                <Text style={styles.detailLabel}>Apprehending Officer</Text>
                                <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.apprehending_officer}</Text>
                              </View>
                            </View>
                          )}

                          {ticketDetails.issued_by && (
                            <View style={styles.detailRow}>
                              <MaterialIcons name="person-pin" size={20} color="#962e2eff" style={styles.detailIcon} />
                              <View style={styles.detailContent}>
                                <Text style={styles.detailLabel}>Issued By</Text>
                                <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.issued_by}</Text>
                              </View>
                            </View>
                          )}

                          {ticketDetails.tomeco_did && (
                            <View style={styles.detailRow}>
                              <MaterialIcons name="fingerprint" size={20} color="#962e2eff" style={styles.detailIcon} />
                              <View style={styles.detailContent}>
                                <Text style={styles.detailLabel}>TOMECO DID</Text>
                                <Text style={styles.detailValue} numberOfLines={0}>{ticketDetails.tomeco_did}</Text>
                              </View>
                            </View>
                          )}
                        </>
                      )}

                      {/* Images */}
                      {ticketDetails.images && Array.isArray(ticketDetails.images) && ticketDetails.images.length > 0 && (
                        <>
                          <View style={styles.sectionHeader}>
                            <Text style={styles.sectionHeaderText}>Images</Text>
                          </View>
                          <View style={styles.imagesContainer}>
                            {ticketDetails.images.map((imagePath, idx) => {
                              const imageUrl = getImageUrl(imagePath, selectedTicket?.isOffline);
                              return imageUrl ? (
                                <Image
                                  key={idx}
                                  source={{ uri: imageUrl }}
                                  style={styles.ticketImage}
                                  resizeMode="cover"
                                />
                              ) : null;
                            })}
                          </View>
                        </>
                      )}

                      {/* Status */}
                      {selectedTicket && (
                        <View style={styles.sectionHeader}>
                          <Text style={styles.sectionHeaderText}>Status</Text>
                        </View>
                      )}
                      {selectedTicket && (
                        <View style={styles.detailRow}>
                          <MaterialIcons
                            name={getStatusIcon(selectedTicket.isOffline ? (selectedTicket.status || 'pending') : 'synced')}
                            size={20}
                            color="#962e2eff"
                            style={styles.detailIcon}
                          />
                          <View style={styles.detailContent}>
                            <Text style={styles.detailLabel}>Sync Status</Text>
                            <Text style={styles.detailValue} numberOfLines={0}>
                              {selectedTicket.isOffline
                                ? selectedTicket.status === 'pending'
                                  ? 'Pending Sync'
                                  : selectedTicket.status === 'syncing'
                                  ? 'Syncing...'
                                  : selectedTicket.status === 'failed'
                                  ? 'Sync Failed'
                                  : 'Offline'
                                : 'Synced Online'}
                            </Text>
                          </View>
                        </View>
                      )}
                    </View>
                  ) : (
                    <View style={styles.errorContainer}>
                      <MaterialIcons name="error-outline" size={48} color="#ccc" />
                      <Text style={styles.errorText}>Unable to load ticket details</Text>
                    </View>
                  )}
            </ScrollView>
          </TouchableOpacity>
        </TouchableOpacity>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
  },
  loadingText: {
    marginTop: 10,
    color: '#666',
    fontSize: 16,
  },
  header: {
    backgroundColor: '#962e2eff',
    paddingTop: 50,
    paddingBottom: 20,
    paddingHorizontal: 20,
    flexDirection: 'row',
    alignItems: 'center',
  },
  backButton: {
    marginRight: 15,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 15,
    paddingBottom: 100,
  },
  summaryCard: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    marginBottom: 15,
    flexDirection: 'row',
    justifyContent: 'space-around',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  summaryItem: {
    alignItems: 'center',
  },
  summaryLabel: {
    fontSize: 12,
    color: '#666',
    marginBottom: 5,
  },
  summaryValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#962e2eff',
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#666',
    marginTop: 15,
  },
  emptySubtext: {
    fontSize: 14,
    color: '#999',
    marginTop: 5,
  },
  ticketCard: {
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    flexDirection: 'row',
    overflow: 'hidden',
  },
  ticketContent: {
    flex: 1,
    padding: 15,
  },
  deleteButton: {
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 15,
    backgroundColor: '#ffebee',
    borderLeftWidth: 1,
    borderLeftColor: '#ffcdd2',
  },
  ticketHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 10,
  },
  ticketInfo: {
    flex: 1,
  },
  ticketDriverName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 4,
  },
  ticketDate: {
    fontSize: 12,
    color: '#666',
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 4,
  },
  statusText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
  },
  ticketDetails: {
    marginTop: 10,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#eee',
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 5,
    gap: 6,
  },
  detailText: {
    fontSize: 14,
    color: '#666',
  },
  // Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: '#fff',
    borderRadius: 20,
    width: Math.min(Dimensions.get('window').width * 0.9, 500),
    maxHeight: Dimensions.get('window').height * 0.8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.3,
    shadowRadius: 20,
    elevation: 10,
    overflow: 'hidden',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
    flexShrink: 0,
  },
  modalTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333',
    flex: 1,
  },
  modalHeaderActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  printButton: {
    padding: 5,
    borderRadius: 5,
  },
  closeButton: {
    padding: 5,
  },
  modalBody: {
    flexGrow: 0,
  },
  modalBodyContent: {
    padding: 20,
    paddingBottom: 20,
  },
  loadingDetailsContainer: {
    padding: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingDetailsText: {
    marginTop: 16,
    fontSize: 16,
    color: '#666',
  },
  detailSection: {
    marginBottom: 10,
  },
  sectionHeader: {
    marginTop: 20,
    marginBottom: 10,
    paddingBottom: 8,
    borderBottomWidth: 2,
    borderBottomColor: '#962e2eff',
  },
  sectionHeaderText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#962e2eff',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 15,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
    minHeight: 40,
    width: '100%',
  },
  detailContent: {
    flex: 1,
    marginLeft: 15,
    flexShrink: 1,
    minWidth: 0,
  },
  detailLabel: {
    fontSize: 12,
    color: '#999',
    marginBottom: 5,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    flexWrap: 'wrap',
  },
  detailValue: {
    fontSize: 16,
    color: '#333',
    fontWeight: '500',
    flexWrap: 'wrap',
    flexShrink: 1,
    minWidth: 0,
  },
  detailIcon: {
    marginTop: 2,
    flexShrink: 0,
  },
  imagesContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginTop: 10,
  },
  ticketImage: {
    width: 150,
    height: 150,
    borderRadius: 8,
    marginBottom: 10,
  },
  errorContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 40,
  },
  errorText: {
    marginTop: 16,
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
  },
});

