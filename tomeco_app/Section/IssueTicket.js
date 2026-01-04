import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Image,
  Modal,
  Platform,
} from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';
import * as ImagePicker from 'expo-image-picker';
import AsyncStorage from '@react-native-async-storage/async-storage';
import SignatureCanvas from 'react-native-signature-canvas';
import API from '../config/api';
import { getAuthToken, isValidToken, handleAuthError } from '../services/AuthService';
import { isOnline } from '../services/NetworkStatus';
import { saveTicketOffline } from '../services/OfflineStorage';
import { syncPendingTickets } from '../services/SyncService';

// Violations list from blade template
const VIOLATIONS = [
  "Driving without D/L",
  "Unregistered Vehicle",
  "Illegal Parking",
  "Disregarding Traffic Sign",
  "Obstruction",
  "Truck Ban",
  "Operating Along National Highway",
  "No Helmet",
  "Defective Head Light",
  "Violation to CO # 2007-10-31 \"The Anti-Littering Ordinance\"",
  "Violation to CO # 2009-10-160 \"The Anti-Smoking Ordinance.\"",
  "Violation to CO # 2007-10-66 \"The anti-urinating and Defecating Ordinance.\"",
  "Sec. 1: Failure to give right of way to Police and other emergency vehicles giving audible signals.",
  "Sec. 2: Allowing passengers to ride on board or, hitch to one's vehicle.",
  "Sec. 3: Driving or parking on a side walk.",
  "Sec. 4: Obscure or dirty plate number.",
  "Sec. 5: Defective headlights, taillights, stop lights, wiper and other accessories.",
  "Sec. 6: Failure to give the necessary signal when starting or stopping.",
  "Sec. 7: Illegal Parking.",
  "Sec. 8: Failure to carry the official receipt of registration of the current year.",
  "Sec. 9: Operating an unsafe, unsightly or dilapidated vehicle.",
  "Sec. 10: Unauthorized use of improvised plates.",
  "Sec. 11: Driving a vehicle with passengers in excess of capacity.",
  "Sec. 12: Driving a vehicle with horn that emits exceptionally loud and startling or disagreeable sound.",
  "Sec. 13: Driving a vehicle with a defective brake system.",
  "Sec. 14: Driving a freight or cargo vehicle loaded in excess of authorized capacity.",
  "Sec. 15: Driving vehicle recklessly.",
  "Sec. 16: Obstructing or impeding the passage of other vehicles, loading and unloading of passengers at intersections or within prohibited areas.",
  "Sec. 17: Driving with unsigned license.",
  "Sec. 18: Driving with invalid or delinquent driver license.",
  "Sec. 19: Driving a vehicle with a delinquent suspended or invalid registration or without the proper license plate for the current year of registration.",
  "Sec. 20: Driving without first securing a driver's license.",
  "Sec. 21: Driving without carrying a driver's license with him.",
  "Sec. 22: Using or attempting to use a fake license, identification card, registration, certificate, vehicle plate number, and tag or sticker.",
  "Sec. 23: Falsely or fraudulently representing as valid and enforced a delinquent suspended or revoked license.",
  "Sec. 24: Using a vehicle registered for private use as that for hire or allowing another person to use the driver's license of the authorized or real driver of the vehicle.",
  "Sec. 25: Cutting corners of blind curbs.",
  "Sec. 26: Making U-Turn on the approach or on top of the bridge or elsewhere but not at intersection.",
  "Sec. 27: Overtaking or passing on curb, at intersection and approaches of bridge, bill and along places where overtaking is prohibited.",
  "Sec. 28: Coming out of Side Street or driveways without precautions.",
  "Sec. 29: Vehicle racing on roads or streets.",
  "Sec. 30: Failure to stop on entering a thru-street.",
  "Sec. 31: Failure to consider proper clearance when overtaking.",
  "Sec. 32: Failure to observe the right-hand rule to yield the right-of-way at highway intersection.",
  "Sec. 33: Driving on a wrong side of the street.",
  "Sec. 34: Backing against the flow of traffic.",
  "Sec. 35: Turning from wrong lane.",
  "Sec. 36: Driving without lights during the hours prescribed by law.",
  "Sec. 37: Driving or crossing the safety island not intended for motor vehicle.",
  "Sec. 38: Disregarding automatic signaling devices, lights or any traffic signal, sign or makings.",
  "Sec. 39: Failure to stop or slow down on crosswalk or pedestrian lanes with or without pedestrians crossing.",
  "Sec. 40: Over-speeding or fast driving.",
  "Sec. 41: Failure to slow down on school zones, hospital zones, churches, courtrooms and the likes.",
  "Sec. 42: Entering a \"DO NOT ENTER\" sign.",
  "Sec. 43: Disregarding a \"NO LEFT TURN\" sign.",
  "Sec. 44: Passing a \"THRU-RED LIGHT\".",
  "Sec. 45: Allowing passengers in excess of the capacity of the front seat.",
  "Sec. 46: Loading or unloading passengers within the prohibited zone.",
  "Sec. 47: Soliciting passengers at street corners.",
  "Sec. 48: Loading and unloading passenger in the middle of the road.",
  "Sec. 49: Loading and unloading passenger at intersection.",
  "Sec. 50: Parking a vehicle or permit the same to stand attended or unattended upon a highway.",
  "Sec. 51: Driving a vehicle with open muffler or making unnecessary noise.",
  "Sec. 52: Failure to display a red flag or red light at the rear end of the load which extends beyond the projected length of the vehicle.",
  "Sec. 53: Driving a vehicle emitting excessive smoke.",
  "Sec. 54: Driving along a highway without proper permit for motor vehicles with metallic tires.",
  "Sec. 55: Operating a service vehicle without a commercial or trade name and the words \"NOT FOR HIRE\" painted in both sides of the motor vehicle.",
  "Sec. 56: Driving a motor truck without capacity marking plainly lettered on both sides of the motor vehicle.",
  "Sec. 57: Driving a vehicle with a broken windshield.",
  "Sec. 58: Driving a motor vehicle with a red light or halogen lamp forward or overhead of the same.",
  "Sec. 59: Driving with inappropriate driver's license or conductor's license.",
  "Sec. 60: Refusal to show or surrender the driver's license and/or conductor's license.",
  "Sec. 61: Operating a vehicle loaded with soil, sand, gravel, stones and the likes without canvass covering.",
  "Sec. 62: Operating a motor vehicle equipped with an unauthorized siren.",
  "Sec. 63: Driving while under the influence of liquor or narcotics drugs.",
  "Sec. 64: Failure to carry the conductor's license.",
  "Sec. 65: Serving as conductor without first securing a conductor's permit.",
  "Sec. 66: Carrying freight or cargo in excess of the registered net carrying capacity.",
  "Sec. 67: Hostile or arrogant attitude of a driver or conductor towards a lawful Authority or improper conduct or behavior like bribery and other similar offenses tending to corrupt a police officer including discourteous to passenger.",
  "Sec. 68: Transferring, lending or otherwise allowing any person to use his driver's license for the purpose of enabling such person to operate a motor vehicle.",
  "Sec. 69: Engaging, Employing or hiring any person to operate a motor vehicle other than a duly license professional driver.",
  "Sec. 70: Operating in a prohibited route.",
  "Sec. 71: Constructing structures, edifices or stand that may obstruct the free passage of pedestrians with the side walk.",
  "Sec. 72: Refusal to convey passenger or having agreed to convey the same, negligently, culpably or unreasonably failed to convey said passenger to his place or destination.",
  "Sec. 73: To demand and collect a fare more than the existing rate as authorized by law, rules and regulations."
];

// Violation prices (all set to 500.00)
const VIOLATION_PRICES = {};
VIOLATIONS.forEach(violation => {
  VIOLATION_PRICES[violation] = 500.00;
});

export default function IssueTicket({ navigation, route }) {
  const { enforcer } = route.params || {};
  const [isLoading, setIsLoading] = useState(false);
  const [enforcerData, setEnforcerData] = useState(enforcer || null);

  // Form state
  const [formData, setFormData] = useState({
    citation_number: '',
    issued_date: new Date(),
    issued_time: new Date(),
    issued_by: '',
    driver_lastname: '',
    driver_firstname: '',
    driver_middlename: '',
    driver_address: '',
    dl_number: '',
    dl_type: '',
    driver_contact: '',
    plate_number: '',
    cr_number: '',
    vehicle_year: '',
    vehicle_make: '',
    vehicle_model: '',
    vehicle_type: '',
    or_number: '',
    owner_name: '',
    owner_address: '',
    violations: [],
    violations_others_text: '',
    place: '',
    accident: null,
    incident_notes: '',
    remarks: '',
    admitted_or_protest: '',
    court_date: null,
    court_time: null,
    apprehending_officer: '',
    tomeco_did: '',
    signature: '',
    driver_signature: '',
    images: [],
  });

  // UI state
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [datePickerField, setDatePickerField] = useState(null);
  const [showTimePicker, setShowTimePicker] = useState(false);
  const [timePickerField, setTimePickerField] = useState(null);
  const [violationSearch, setViolationSearch] = useState('');
  const [showViolationModal, setShowViolationModal] = useState(false);
  const [filteredViolations, setFilteredViolations] = useState(VIOLATIONS);
  const [showSignatureModal, setShowSignatureModal] = useState(false);
  const [signatureType, setSignatureType] = useState('officer'); // 'officer' or 'driver'
  const signatureRef = useRef(null);

  useEffect(() => {
    // Set current date and time when component mounts
    const now = new Date();
    setFormData(prev => ({
      ...prev,
      issued_date: prev.issued_date || now,
      issued_time: prev.issued_time || now,
      court_date: prev.court_date || now,
      court_time: prev.court_time || now,
    }));
    
    loadEnforcerData();
    requestPermissions();
    
    // Check if OCR data is available from navigation
    const ocrData = route.params?.ocrData;
    const useOCR = route.params?.useOCR;
    
    if (useOCR && ocrData) {
      populateFormFromOCR(ocrData);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [route.params]);

  useEffect(() => {
    // Filter violations based on search
    if (violationSearch.trim()) {
      const filtered = VIOLATIONS.filter(v =>
        v.toLowerCase().includes(violationSearch.toLowerCase())
      );
      setFilteredViolations(filtered);
    } else {
      setFilteredViolations(VIOLATIONS);
    }
  }, [violationSearch]);

  const loadEnforcerData = async () => {
    try {
      const currentTime = new Date();
      
      // Use a ref or check to prevent multiple updates
      if (enforcer && (!enforcerData || enforcerData.id !== enforcer.id)) {
        setEnforcerData(enforcer);
        setFormData(prev => {
          const issuedBy = enforcer.fullname || enforcer.username || '';
          const apprehendingOfficer = enforcer.fullname || enforcer.username || '';
          const tomecoDid = enforcer.id_number || '';
          // Only update if values actually changed
          if (prev.issued_by === issuedBy && 
              prev.apprehending_officer === apprehendingOfficer && 
              prev.tomeco_did === tomecoDid) {
            return prev;
          }
          return {
            ...prev,
            issued_by: issuedBy,
            apprehending_officer: apprehendingOfficer,
            tomeco_did: tomecoDid,
            issued_date: currentTime,
            issued_time: currentTime,
            court_date: currentTime,
            court_time: currentTime,
          };
        });
      } else if (!enforcer && !enforcerData) {
        const storedData = await AsyncStorage.getItem('enforcer_data');
        if (storedData) {
          const storedEnforcer = JSON.parse(storedData);
          setEnforcerData(storedEnforcer);
          setFormData(prev => {
            const issuedBy = storedEnforcer.fullname || storedEnforcer.username || '';
            const apprehendingOfficer = storedEnforcer.fullname || storedEnforcer.username || '';
            const tomecoDid = storedEnforcer.id_number || '';
            // Only update if values actually changed
            if (prev.issued_by === issuedBy && 
                prev.apprehending_officer === apprehendingOfficer && 
                prev.tomeco_did === tomecoDid) {
              return prev;
            }
            return {
              ...prev,
              issued_by: issuedBy,
              apprehending_officer: apprehendingOfficer,
              tomeco_did: tomecoDid,
              issued_date: currentTime,
              issued_time: currentTime,
            };
          });
        } else {
          // If no enforcer data found, at least set current time
          setFormData(prev => ({
            ...prev,
            issued_date: currentTime,
            issued_time: currentTime,
            court_date: currentTime,
            court_time: currentTime,
          }));
        }
      }
    } catch (error) {
      console.error('Error loading enforcer data:', error);
    }
  };

  const requestPermissions = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission needed', 'Camera and media library permissions are required to capture photos.');
    }
  };

  const updateFormData = (field, value) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const populateFormFromOCR = (ocrData) => {
    // Auto-fill form fields from OCR extracted data
    // Only extract: lastname, firstname, middlename, and address
    setFormData(prev => ({
      ...prev,
      driver_lastname: ocrData.lastname || prev.driver_lastname,
      driver_firstname: ocrData.firstname || prev.driver_firstname,
      driver_middlename: ocrData.middlename || prev.driver_middlename,
      driver_address: ocrData.address || prev.driver_address,
    }));

    // Show success message if at least name was extracted
    if (ocrData.lastname || ocrData.firstname) {
      const extractedFields = [];
      if (ocrData.lastname) extractedFields.push('Last Name');
      if (ocrData.firstname) extractedFields.push('First Name');
      if (ocrData.middlename) extractedFields.push('Middle Name');
      if (ocrData.address) extractedFields.push('Address');
      
      Alert.alert(
        'OCR Success',
        `Extracted: ${extractedFields.join(', ')}\n\nPlease review and complete any missing fields.`,
        [{ text: 'OK' }]
      );
    } else {
      Alert.alert(
        'OCR Completed',
        'Could not extract name information from the ID card. Please enter the details manually.',
        [{ text: 'OK' }]
      );
    }
  };

  const handleDateChange = (event, selectedDate) => {
    setShowDatePicker(Platform.OS === 'ios');
    if (selectedDate && datePickerField) {
      updateFormData(datePickerField, selectedDate);
    }
  };

  const handleTimeChange = (event, selectedTime) => {
    setShowTimePicker(Platform.OS === 'ios');
    if (selectedTime && timePickerField) {
      updateFormData(timePickerField, selectedTime);
    }
  };

  const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().split('T')[0];
  };

  const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
  };

  const toggleViolation = (violation) => {
    setFormData(prev => {
      const violations = [...prev.violations];
      const index = violations.indexOf(violation);
      if (index > -1) {
        violations.splice(index, 1);
      } else {
        violations.push(violation);
      }
      return { ...prev, violations };
    });
  };

  const calculateTotalPrice = () => {
    return formData.violations.reduce((total, violation) => {
      return total + (VIOLATION_PRICES[violation] || 0);
    }, 0);
  };

  const pickImage = async () => {
    try {
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsMultipleSelection: true,
        quality: 0.8,
      });

      if (!result.canceled && result.assets) {
        const newImages = result.assets.map(asset => ({
          uri: asset.uri,
          type: 'image/jpeg',
          name: `image_${Date.now()}_${Math.random().toString(36).substring(7)}.jpg`,
        }));
        setFormData(prev => ({
          ...prev,
          images: [...prev.images, ...newImages],
        }));
      }
    } catch (error) {
      console.error('Error picking image:', error);
      Alert.alert('Error', 'Failed to pick image');
    }
  };

  const takePhoto = async () => {
    try {
      const result = await ImagePicker.launchCameraAsync({
        quality: 0.8,
        allowsEditing: false,
      });

      if (!result.canceled && result.assets && result.assets[0]) {
        const newImage = {
          uri: result.assets[0].uri,
          type: 'image/jpeg',
          name: `camera_${Date.now()}_${Math.random().toString(36).substring(7)}.jpg`,
        };
        setFormData(prev => ({
          ...prev,
          images: [...prev.images, newImage],
        }));
      }
    } catch (error) {
      console.error('Error taking photo:', error);
      Alert.alert('Error', 'Failed to take photo');
    }
  };

  const removeImage = (index) => {
    setFormData(prev => {
      const images = [...prev.images];
      images.splice(index, 1);
      return { ...prev, images };
    });
  };

  const handleSubmit = async () => {
    // Validation
    if (!formData.driver_lastname.trim() || !formData.driver_firstname.trim()) {
      Alert.alert('Validation Error', 'Driver first name and last name are required');
      return;
    }

    if (formData.violations.length === 0) {
      Alert.alert('Validation Error', 'Please select at least one violation');
      return;
    }

    setIsLoading(true);

    try {
      // Get and validate token
      const token = await getAuthToken();
      if (!token) {
        Alert.alert(
          'Authentication Required',
          'Please login again to create tickets.',
          [
            {
              text: 'OK',
              onPress: () => {
                // Clear any invalid data and navigate to login
                AsyncStorage.multiRemove(['auth_token', 'enforcer_data']).then(() => {
                  navigation.dispatch(
                    require('@react-navigation/native').CommonActions.reset({
                      index: 0,
                      routes: [{ name: 'Login' }],
                    })
                  );
                });
              },
            },
          ]
        );
        setIsLoading(false);
        return;
      }
      
      // Check network connectivity first
      const online = isOnline();
      
      // Validate token format
      // Allow offline tokens when device is offline
      const isOfflineToken = token && token.startsWith('offline_token_');
      if (!isValidToken(token) && !(isOfflineToken && !online)) {
        // Only reject invalid tokens if we're online or it's not an offline token
        Alert.alert(
          'Invalid Session',
          'Your session has expired. Please login again.',
          [
            {
              text: 'OK',
              onPress: async () => {
                await handleAuthError({ message: 'Invalid token' });
                navigation.dispatch(
                  require('@react-navigation/native').CommonActions.reset({
                    index: 0,
                    routes: [{ name: 'Login' }],
                  })
                );
              },
            },
          ]
        );
        setIsLoading(false);
        return;
      }
      
      // If offline, save locally
      if (!online) {
        try {
          // Ensure enforcer fields are populated from formData or enforcerData
          const apprehendingOfficer = formData.apprehending_officer || enforcerData?.fullname || '';
          const issuedBy = formData.issued_by || enforcerData?.fullname || '';
          const tomecoDid = formData.tomeco_did || enforcerData?.id_number || '';
          
          // Prepare ticket data for offline storage
          const ticketData = {
            driver_lastname: formData.driver_lastname,
            driver_firstname: formData.driver_firstname,
            driver_middlename: formData.driver_middlename || '',
            driver_address: formData.driver_address || '',
            dl_number: formData.dl_number || '',
            dl_type: formData.dl_type || '',
            driver_contact: formData.driver_contact || '',
            plate_number: formData.plate_number || '',
            cr_number: formData.cr_number || '',
            vehicle_year: formData.vehicle_year || '',
            vehicle_make: formData.vehicle_make || '',
            vehicle_model: formData.vehicle_model || '',
            vehicle_type: formData.vehicle_type || '',
            or_number: formData.or_number || '',
            owner_name: formData.owner_name || '',
            owner_address: formData.owner_address || '',
            place: formData.place || '',
            incident_notes: formData.incident_notes || '',
            remarks: formData.remarks || '',
            apprehending_officer: apprehendingOfficer,
            issued_by: issuedBy,
            tomeco_did: tomecoDid,
            violations: formData.violations,
            violations_others_text: formData.violations_others_text || '',
            issued_date: formData.issued_date ? formatDate(formData.issued_date) : null,
            issued_time: formData.issued_time ? formatTime(formData.issued_time) : null,
            court_date: formData.court_date ? formatDate(formData.court_date) : null,
            court_time: formData.court_time ? formatTime(formData.court_time) : null,
            accident: formData.accident || false,
            admitted_or_protest: formData.admitted_or_protest || '',
            price: calculateTotalPrice().toFixed(2),
          };
          
          // Save ticket offline
          const localTicketId = await saveTicketOffline(
            ticketData,
            formData.images,
            formData.signature || '',
            formData.driver_signature || ''
          );
          
          Alert.alert(
            'Ticket Saved Offline',
            'Your ticket has been saved locally and will be synced automatically when you go online.',
            [
              {
                text: 'OK',
                onPress: () => navigation.goBack(),
              },
            ]
          );
          setIsLoading(false);
          return;
        } catch (offlineError) {
          console.error('Error saving ticket offline:', offlineError);
          Alert.alert(
            'Error',
            'Failed to save ticket offline. Please try again or connect to the internet.',
            [{ text: 'OK' }]
          );
          setIsLoading(false);
          return;
        }
      }

      // Prepare form data
      const submitData = new FormData();

      // Add all form fields
      submitData.append('driver_lastname', formData.driver_lastname);
      submitData.append('driver_firstname', formData.driver_firstname);
      submitData.append('driver_middlename', formData.driver_middlename || '');
      submitData.append('driver_address', formData.driver_address || '');
      submitData.append('dl_number', formData.dl_number || '');
      submitData.append('dl_type', formData.dl_type || '');
      submitData.append('driver_contact', formData.driver_contact || '');
      submitData.append('plate_number', formData.plate_number || '');
      submitData.append('cr_number', formData.cr_number || '');
      submitData.append('vehicle_year', formData.vehicle_year || '');
      submitData.append('vehicle_make', formData.vehicle_make || '');
      submitData.append('vehicle_model', formData.vehicle_model || '');
      submitData.append('vehicle_type', formData.vehicle_type || '');
      submitData.append('or_number', formData.or_number || '');
      submitData.append('owner_name', formData.owner_name || '');
      submitData.append('owner_address', formData.owner_address || '');
      submitData.append('place', formData.place || '');
      submitData.append('incident_notes', formData.incident_notes || '');
      submitData.append('remarks', formData.remarks || '');
      // Ensure apprehending_officer and issued_by are set from enforcer data
      const apprehendingOfficer = formData.apprehending_officer || enforcerData?.fullname || '';
      const issuedBy = formData.issued_by || enforcerData?.fullname || '';
      
      console.log('Submitting ticket with enforcer info:', {
        apprehending_officer: apprehendingOfficer,
        issued_by: issuedBy,
        enforcer_fullname: enforcerData?.fullname,
      });
      
      submitData.append('apprehending_officer', apprehendingOfficer);
      submitData.append('tomeco_did', formData.tomeco_did || '');
      submitData.append('issued_by', issuedBy);
      submitData.append('violations_others_text', formData.violations_others_text || '');

      // Add dates and times
      if (formData.issued_date) {
        submitData.append('issued_date', formatDate(formData.issued_date));
      }
      if (formData.issued_time) {
        submitData.append('issued_time', formatTime(formData.issued_time));
      }
      if (formData.court_date) {
        submitData.append('court_date', formatDate(formData.court_date));
      }
      if (formData.court_time) {
        submitData.append('court_time', formatTime(formData.court_time));
      }

      // Add violations array
      formData.violations.forEach((violation, index) => {
        submitData.append(`violations[${index}]`, violation);
      });

      // Add accident (convert boolean to string)
      if (formData.accident !== null && formData.accident !== undefined) {
        submitData.append('accident', formData.accident ? '1' : '0');
      }

      // Add admitted/protest
      if (formData.admitted_or_protest) {
        submitData.append('admitted_or_protest', formData.admitted_or_protest);
      }

      // Add signatures (base64)
      if (formData.signature) {
        submitData.append('signature', formData.signature);
      }
      if (formData.driver_signature) {
        submitData.append('driver_signature', formData.driver_signature);
      }

      // Add price
      const totalPrice = calculateTotalPrice();
      submitData.append('price', totalPrice.toFixed(2));

      // Add images
      formData.images.forEach((image, index) => {
        submitData.append(`images[${index}]`, {
          uri: image.uri,
          type: image.type || 'image/jpeg',
          name: image.name || `image_${index}.jpg`,
        });
      });

      // Check if device is offline BEFORE making API call
      const deviceIsOnline = isOnline();
      
      if (!deviceIsOnline) {
        // Device is offline - save directly to offline storage
        console.log('Device is offline - saving ticket locally');
        try {
          // Ensure enforcer fields are populated from formData or enforcerData
          const apprehendingOfficer = formData.apprehending_officer || enforcerData?.fullname || '';
          const issuedBy = formData.issued_by || enforcerData?.fullname || '';
          const tomecoDid = formData.tomeco_did || enforcerData?.id_number || '';
          
          const ticketData = {
            driver_lastname: formData.driver_lastname,
            driver_firstname: formData.driver_firstname,
            driver_middlename: formData.driver_middlename || '',
            driver_address: formData.driver_address || '',
            dl_number: formData.dl_number || '',
            dl_type: formData.dl_type || '',
            driver_contact: formData.driver_contact || '',
            plate_number: formData.plate_number || '',
            cr_number: formData.cr_number || '',
            vehicle_year: formData.vehicle_year || '',
            vehicle_make: formData.vehicle_make || '',
            vehicle_model: formData.vehicle_model || '',
            vehicle_type: formData.vehicle_type || '',
            or_number: formData.or_number || '',
            owner_name: formData.owner_name || '',
            owner_address: formData.owner_address || '',
            place: formData.place || '',
            incident_notes: formData.incident_notes || '',
            remarks: formData.remarks || '',
            apprehending_officer: apprehendingOfficer,
            issued_by: issuedBy,
            tomeco_did: tomecoDid,
            violations: formData.violations,
            violations_others_text: formData.violations_others_text || '',
            issued_date: formData.issued_date ? formatDate(formData.issued_date) : null,
            issued_time: formData.issued_time ? formatTime(formData.issued_time) : null,
            court_date: formData.court_date ? formatDate(formData.court_date) : null,
            court_time: formData.court_time ? formatTime(formData.court_time) : null,
            accident: formData.accident || false,
            admitted_or_protest: formData.admitted_or_protest || '',
            price: calculateTotalPrice().toFixed(2),
          };
          
          await saveTicketOffline(
            ticketData,
            formData.images,
            formData.signature || '',
            formData.driver_signature || ''
          );
          
          Alert.alert(
            'Saved Offline',
            'Your ticket has been saved locally and will be synced automatically when you go online.',
            [
              {
                text: 'OK',
                onPress: () => navigation.goBack(),
              },
            ]
          );
          setIsLoading(false);
          return;
        } catch (offlineError) {
          console.error('Error saving offline:', offlineError);
          Alert.alert(
            'Error',
            'Failed to save ticket offline. Please try again.',
            [{ text: 'OK' }]
          );
          setIsLoading(false);
          return;
        }
      }

      // Device is online - submit to API
      console.log('Submitting ticket to:', API.TICKETS.CREATE);
      console.log('Token length:', token.length);
      console.log('Token preview:', token.substring(0, 20) + '...');
      
      let response;
      try {
        response = await fetch(API.TICKETS.CREATE, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
          },
          body: submitData,
        });
      } catch (fetchError) {
        // Network error during fetch - save offline
        console.log('Fetch error (network issue):', fetchError.message);
        if (fetchError.message.includes('Network') || fetchError.message.includes('fetch')) {
          // This will be handled in the catch block below
          throw fetchError;
        }
        throw fetchError;
      }
      
      console.log('Response status:', response.status);
      console.log('Response headers:', Object.fromEntries(response.headers.entries()));

      // Only check for authentication errors if we got a valid HTTP response
      // Network errors will be caught in the catch block
      if (response.status === 401 || response.status === 403) {
        await handleAuthError(response);
        Alert.alert(
          'Authentication Failed',
          'Your session has expired. Please login again.',
          [
            {
              text: 'OK',
              onPress: () => {
                navigation.dispatch(
                  require('@react-navigation/native').CommonActions.reset({
                    index: 0,
                    routes: [{ name: 'Login' }],
                  })
                );
              },
            },
          ]
        );
        setIsLoading(false);
        return;
      }

      // Check if response is JSON
      let result;
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        result = await response.json();
      } else {
        const text = await response.text();
        throw new Error(text || `Server error: ${response.status}`);
      }

      if (response.ok) {
        console.log('Ticket created successfully:', {
          ticket_id: result.data?.id,
          citation_number: result.data?.citation_number,
          apprehending_officer: result.data?.apprehending_officer,
          issued_by: result.data?.issued_by,
          enforcer_fullname: enforcerData?.fullname,
        });
        
        // Verify the ticket was saved with correct officer names
        if (result.data?.apprehending_officer && result.data?.issued_by) {
          console.log('Ticket saved with officer names:', {
            saved_apprehending_officer: result.data.apprehending_officer,
            saved_issued_by: result.data.issued_by,
            enforcer_fullname: enforcerData?.fullname,
            match_apprehending: result.data.apprehending_officer.toLowerCase().trim() === (enforcerData?.fullname || '').toLowerCase().trim(),
            match_issued_by: result.data.issued_by.toLowerCase().trim() === (enforcerData?.fullname || '').toLowerCase().trim(),
          });
        }
        
        Alert.alert(
          'Success',
          'Ticket created successfully!',
          [
            {
              text: 'OK',
              onPress: () => {
                // Try to sync any pending offline tickets
                syncPendingTickets().catch(err => {
                  console.log('Background sync attempt:', err.message);
                });
                navigation.goBack();
              },
            },
          ]
        );
      } else {
        // Online submission failed - show error
        const errorMessage = result.message || result.error || 'Failed to create ticket';
        
        // Check if it's an authentication error
        if (errorMessage.includes('Unauthenticated') || errorMessage.includes('Unauthorized')) {
          await handleAuthError({ message: errorMessage });
          Alert.alert(
            'Authentication Failed',
            'Your session has expired. Please login again.',
            [
              {
                text: 'OK',
                onPress: () => {
                  navigation.dispatch(
                    require('@react-navigation/native').CommonActions.reset({
                      index: 0,
                      routes: [{ name: 'Login' }],
                    })
                  );
                },
              },
            ]
          );
        } else {
          Alert.alert('Error', errorMessage);
        }
      }
    } catch (error) {
      console.error('Error submitting ticket:', error);
      console.error('Error type:', error.constructor.name);
      console.error('Error message:', error.message);
      
      // Show appropriate error message
      let errorMessage = 'Failed to create ticket. Please try again.';
      
      // Check if it's a network error (not an authentication error)
      const isNetworkError = error.message.includes('Network') || 
                            error.message.includes('fetch') ||
                            error.message.includes('Failed to fetch') ||
                            error.message.includes('Network request failed') ||
                            (error.name && error.name === 'TypeError');
      
      // Only treat as network error if it's NOT an authentication error
      const isAuthError = error.message.includes('Unauthenticated') || 
                         error.message.includes('Unauthorized') ||
                         error.message.includes('401') ||
                         error.message.includes('403');
      
      if (isNetworkError && !isAuthError) {
        // Network error - try to save offline
        console.log('Network error detected - attempting to save offline');
        try {
          // Ensure enforcer fields are populated from formData or enforcerData
          const apprehendingOfficer = formData.apprehending_officer || enforcerData?.fullname || '';
          const issuedBy = formData.issued_by || enforcerData?.fullname || '';
          const tomecoDid = formData.tomeco_did || enforcerData?.id_number || '';
          
          const ticketData = {
            driver_lastname: formData.driver_lastname,
            driver_firstname: formData.driver_firstname,
            driver_middlename: formData.driver_middlename || '',
            driver_address: formData.driver_address || '',
            dl_number: formData.dl_number || '',
            dl_type: formData.dl_type || '',
            driver_contact: formData.driver_contact || '',
            plate_number: formData.plate_number || '',
            cr_number: formData.cr_number || '',
            vehicle_year: formData.vehicle_year || '',
            vehicle_make: formData.vehicle_make || '',
            vehicle_model: formData.vehicle_model || '',
            vehicle_type: formData.vehicle_type || '',
            or_number: formData.or_number || '',
            owner_name: formData.owner_name || '',
            owner_address: formData.owner_address || '',
            place: formData.place || '',
            incident_notes: formData.incident_notes || '',
            remarks: formData.remarks || '',
            apprehending_officer: apprehendingOfficer,
            issued_by: issuedBy,
            tomeco_did: tomecoDid,
            violations: formData.violations,
            violations_others_text: formData.violations_others_text || '',
            issued_date: formData.issued_date ? formatDate(formData.issued_date) : null,
            issued_time: formData.issued_time ? formatTime(formData.issued_time) : null,
            court_date: formData.court_date ? formatDate(formData.court_date) : null,
            court_time: formData.court_time ? formatTime(formData.court_time) : null,
            accident: formData.accident || false,
            admitted_or_protest: formData.admitted_or_protest || '',
            price: calculateTotalPrice().toFixed(2),
          };
          
          await saveTicketOffline(
            ticketData,
            formData.images,
            formData.signature || '',
            formData.driver_signature || ''
          );
          
          Alert.alert(
            'Network Error - Saved Offline',
            'Your ticket has been saved locally and will be synced automatically when you go online.',
            [
              {
                text: 'OK',
                onPress: () => navigation.goBack(),
              },
            ]
          );
          return;
        } catch (offlineError) {
          console.error('Error saving offline after network error:', offlineError);
          errorMessage = 'Network error. Failed to save offline. Please try again.';
        }
      } else if (isAuthError) {
        // Authentication error - clear auth and redirect to login
        await handleAuthError({ message: error.message });
        Alert.alert(
          'Authentication Failed',
          'Your session has expired. Please login again.',
          [
            {
              text: 'OK',
              onPress: () => {
                navigation.dispatch(
                  require('@react-navigation/native').CommonActions.reset({
                    index: 0,
                    routes: [{ name: 'Login' }],
                  })
                );
              },
            },
          ]
        );
        setIsLoading(false);
        return;
      } else if (error.message) {
        errorMessage = error.message;
      }
      
      Alert.alert('Error', errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity
            style={styles.backButton}
            onPress={() => navigation.goBack()}
          >
            <MaterialIcons name="arrow-back" size={24} color="#fff" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Issue New Ticket</Text>
        </View>

        {/* Issuance Details */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Issuance Details</Text>
          
          <View style={styles.row}>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Date</Text>
              <TouchableOpacity
                style={styles.input}
                onPress={() => {
                  setDatePickerField('issued_date');
                  setShowDatePicker(true);
                }}
              >
                <Text>{formatDate(formData.issued_date)}</Text>
              </TouchableOpacity>
            </View>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Time</Text>
              <TouchableOpacity
                style={styles.input}
                onPress={() => {
                  setTimePickerField('issued_time');
                  setShowTimePicker(true);
                }}
              >
                <Text>{formatTime(formData.issued_time)}</Text>
              </TouchableOpacity>
            </View>
          </View>

          <Text style={styles.label}>Issued By</Text>
          <TextInput
            style={[styles.input, styles.autoFilledInput]}
            value={formData.issued_by || enforcerData?.fullname || ''}
            placeholder="Officer name"
            editable={false}
          />
        </View>

        {/* Driver Details */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Driver Details</Text>
          
          <View style={styles.row}>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Last Name *</Text>
              <TextInput
                style={styles.input}
                value={formData.driver_lastname}
                onChangeText={(text) => updateFormData('driver_lastname', text)}
                placeholder="Last name"
              />
            </View>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>First Name *</Text>
              <TextInput
                style={styles.input}
                value={formData.driver_firstname}
                onChangeText={(text) => updateFormData('driver_firstname', text)}
                placeholder="First name"
              />
            </View>
          </View>

          <Text style={styles.label}>Middle Name</Text>
          <TextInput
            style={styles.input}
            value={formData.driver_middlename}
            onChangeText={(text) => updateFormData('driver_middlename', text)}
            placeholder="Middle name"
          />

          <Text style={styles.label}>Address</Text>
          <TextInput
            style={styles.input}
            value={formData.driver_address}
            onChangeText={(text) => updateFormData('driver_address', text)}
            placeholder="Driver's address"
          />

          <View style={styles.row}>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Driver's License #</Text>
              <TextInput
                style={styles.input}
                value={formData.dl_number}
                onChangeText={(text) => updateFormData('dl_number', text)}
                placeholder="DL number"
              />
            </View>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Contact #</Text>
              <TextInput
                style={styles.input}
                value={formData.driver_contact}
                onChangeText={(text) => updateFormData('driver_contact', text)}
                placeholder="Contact number"
                keyboardType="phone-pad"
              />
            </View>
          </View>

          <Text style={styles.label}>License Type</Text>
          <View style={styles.radioGroup}>
            {['Prof', 'N/P', 'S/P', 'Others'].map((type) => (
              <TouchableOpacity
                key={type}
                style={[
                  styles.radioOption,
                  formData.dl_type === type && styles.radioOptionSelected,
                ]}
                onPress={() => updateFormData('dl_type', type)}
              >
                <Text
                  style={[
                    styles.radioText,
                    formData.dl_type === type && styles.radioTextSelected,
                  ]}
                >
                  {type}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        {/* Vehicle Details */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Vehicle & Owner Details</Text>
          
          <View style={styles.row}>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Plate #</Text>
              <TextInput
                style={styles.input}
                value={formData.plate_number}
                onChangeText={(text) => updateFormData('plate_number', text)}
                placeholder="Plate number"
              />
            </View>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>CR #</Text>
              <TextInput
                style={styles.input}
                value={formData.cr_number}
                onChangeText={(text) => updateFormData('cr_number', text)}
                placeholder="CR number"
              />
            </View>
          </View>

          <View style={styles.row}>
            <View style={styles.thirdWidth}>
              <Text style={styles.label}>Year</Text>
              <TextInput
                style={styles.input}
                value={formData.vehicle_year}
                onChangeText={(text) => updateFormData('vehicle_year', text)}
                placeholder="Year"
                keyboardType="numeric"
                maxLength={4}
              />
            </View>
            <View style={styles.thirdWidth}>
              <Text style={styles.label}>Make</Text>
              <TextInput
                style={styles.input}
                value={formData.vehicle_make}
                onChangeText={(text) => updateFormData('vehicle_make', text)}
                placeholder="Make"
              />
            </View>
            <View style={styles.thirdWidth}>
              <Text style={styles.label}>Model</Text>
              <TextInput
                style={styles.input}
                value={formData.vehicle_model}
                onChangeText={(text) => updateFormData('vehicle_model', text)}
                placeholder="Model"
              />
            </View>
          </View>

          <View style={styles.row}>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Type</Text>
              <TextInput
                style={styles.input}
                value={formData.vehicle_type}
                onChangeText={(text) => updateFormData('vehicle_type', text)}
                placeholder="Vehicle type"
              />
            </View>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>OR #</Text>
              <TextInput
                style={styles.input}
                value={formData.or_number}
                onChangeText={(text) => updateFormData('or_number', text)}
                placeholder="OR number"
              />
            </View>
          </View>

          <Text style={styles.label}>Owner's Name (if not driver)</Text>
          <TextInput
            style={styles.input}
            value={formData.owner_name}
            onChangeText={(text) => updateFormData('owner_name', text)}
            placeholder="Owner name"
          />

          <Text style={styles.label}>Owner's Address</Text>
          <TextInput
            style={styles.input}
            value={formData.owner_address}
            onChangeText={(text) => updateFormData('owner_address', text)}
            placeholder="Owner address"
          />
        </View>

        {/* Violations */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Violations *</Text>
          
          <TouchableOpacity
            style={styles.violationButton}
            onPress={() => setShowViolationModal(true)}
          >
            <Text style={styles.violationButtonText}>
              {formData.violations.length > 0
                ? `${formData.violations.length} violation(s) selected`
                : 'Select Violations'}
            </Text>
            <MaterialIcons name="arrow-forward-ios" size={16} color="#666" />
          </TouchableOpacity>

          {formData.violations.length > 0 && (
            <View style={styles.selectedViolations}>
              {formData.violations.map((violation, index) => (
                <View key={index} style={styles.violationTag}>
                  <Text style={styles.violationTagText} numberOfLines={2} ellipsizeMode="tail">{violation}</Text>
                  <TouchableOpacity
                    onPress={() => toggleViolation(violation)}
                    style={styles.removeTag}
                  >
                    <MaterialIcons name="close" size={16} color="#fff" />
                  </TouchableOpacity>
                </View>
              ))}
            </View>
          )}

          <Text style={styles.label}>Other Violations (if any)</Text>
          <TextInput
            style={styles.input}
            value={formData.violations_others_text}
            onChangeText={(text) => updateFormData('violations_others_text', text)}
            placeholder="Describe other violations"
            multiline
          />

          <View style={styles.priceContainer}>
            <Text style={styles.priceLabel}>Total Fine Amount:</Text>
            <Text style={styles.priceValue}>₱{calculateTotalPrice().toFixed(2)}</Text>
          </View>
        </View>

        {/* Violation Details */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Violation Details</Text>
          
          <Text style={styles.label}>Place of Violation</Text>
          <TextInput
            style={styles.input}
            value={formData.place}
            onChangeText={(text) => updateFormData('place', text)}
            placeholder="Location"
          />

          <Text style={styles.label}>Accident</Text>
          <View style={styles.radioGroup}>
            <TouchableOpacity
              style={[
                styles.radioOption,
                formData.accident === true && styles.radioOptionSelected,
              ]}
              onPress={() => updateFormData('accident', true)}
            >
              <Text
                style={[
                  styles.radioText,
                  formData.accident === true && styles.radioTextSelected,
                ]}
              >
                Yes
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[
                styles.radioOption,
                formData.accident === false && styles.radioOptionSelected,
              ]}
              onPress={() => updateFormData('accident', false)}
            >
              <Text
                style={[
                  styles.radioText,
                  formData.accident === false && styles.radioTextSelected,
                ]}
              >
                No
              </Text>
            </TouchableOpacity>
          </View>

          <Text style={styles.label}>Incident Notes</Text>
          <TextInput
            style={[styles.input, styles.textArea]}
            value={formData.incident_notes}
            onChangeText={(text) => updateFormData('incident_notes', text)}
            placeholder="Incident details"
            multiline
            numberOfLines={4}
          />

          <Text style={styles.label}>Remarks</Text>
          <TextInput
            style={[styles.input, styles.textArea]}
            value={formData.remarks}
            onChangeText={(text) => updateFormData('remarks', text)}
            placeholder="Additional remarks"
            multiline
            numberOfLines={3}
          />
        </View>

        {/* Driver Promise */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Driver's Promise & Signature</Text>
          
          <Text style={styles.label}>Admitted / Under Protest</Text>
          <View style={styles.radioGroup}>
            <TouchableOpacity
              style={[
                styles.radioOption,
                formData.admitted_or_protest === 'Admitted' && styles.radioOptionSelected,
              ]}
              onPress={() => updateFormData('admitted_or_protest', 'Admitted')}
            >
              <Text
                style={[
                  styles.radioText,
                  formData.admitted_or_protest === 'Admitted' && styles.radioTextSelected,
                ]}
              >
                Admitted
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[
                styles.radioOption,
                formData.admitted_or_protest === 'Under Protest' && styles.radioOptionSelected,
              ]}
              onPress={() => updateFormData('admitted_or_protest', 'Under Protest')}
            >
              <Text
                style={[
                  styles.radioText,
                  formData.admitted_or_protest === 'Under Protest' && styles.radioTextSelected,
                ]}
              >
                Under Protest
              </Text>
            </TouchableOpacity>
          </View>

          <TouchableOpacity
            style={styles.signatureButton}
            onPress={() => {
              setSignatureType('driver');
              setShowSignatureModal(true);
            }}
          >
            <MaterialIcons name="edit" size={20} color="#962e2eff" />
            <Text style={styles.signatureButtonText}>
              {formData.driver_signature ? 'Driver Signature Captured' : 'Capture Driver Signature'}
            </Text>
          </TouchableOpacity>
        </View>

        {/* Evidence Photos */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Evidence / Photo Upload</Text>
          
          <View style={styles.imageButtons}>
            <TouchableOpacity style={styles.imageButton} onPress={pickImage}>
              <MaterialIcons name="photo-library" size={24} color="#962e2eff" />
              <Text style={styles.imageButtonText}>Upload Photo</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.imageButton} onPress={takePhoto}>
              <MaterialIcons name="camera-alt" size={24} color="#962e2eff" />
              <Text style={styles.imageButtonText}>Take Photo</Text>
            </TouchableOpacity>
          </View>

          {formData.images.length > 0 && (
            <View style={styles.imageGrid}>
              {formData.images.map((image, index) => (
                <View key={index} style={styles.imageContainer}>
                  <Image source={{ uri: image.uri }} style={styles.imagePreview} />
                  <TouchableOpacity
                    style={styles.removeImageButton}
                    onPress={() => removeImage(index)}
                  >
                    <MaterialIcons name="close" size={20} color="#fff" />
                  </TouchableOpacity>
                </View>
              ))}
            </View>
          )}
        </View>

        {/* Officer Signature */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>E-Signature</Text>
          
          <TouchableOpacity
            style={styles.signatureButton}
            onPress={() => {
              setSignatureType('officer');
              setShowSignatureModal(true);
            }}
          >
            <MaterialIcons name="edit" size={20} color="#962e2eff" />
            <Text style={styles.signatureButtonText}>
              {formData.signature ? 'Officer Signature Captured' : 'Capture Officer Signature'}
            </Text>
          </TouchableOpacity>
        </View>

        {/* Court & Officer Details */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Officer & Court Details</Text>
          
          <View style={styles.row}>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Court Date</Text>
              <TouchableOpacity
                style={styles.input}
                onPress={() => {
                  setDatePickerField('court_date');
                  setShowDatePicker(true);
                }}
              >
                <Text>{formatDate(formData.court_date)}</Text>
              </TouchableOpacity>
            </View>
            <View style={styles.halfWidth}>
              <Text style={styles.label}>Court Time</Text>
              <TouchableOpacity
                style={styles.input}
                onPress={() => {
                  setTimePickerField('court_time');
                  setShowTimePicker(true);
                }}
              >
                <Text>{formatTime(formData.court_time)}</Text>
              </TouchableOpacity>
            </View>
          </View>

          <Text style={styles.label}>Apprehending Officer</Text>
          <TextInput
            style={[styles.input, styles.autoFilledInput]}
            value={formData.apprehending_officer || enforcerData?.fullname || ''}
            placeholder="Officer name"
            editable={false}
          />

          <Text style={styles.label}>TOMECO DID</Text>
          <TextInput
            style={[styles.input, styles.autoFilledInput]}
            value={formData.tomeco_did || enforcerData?.id_number || ''}
            placeholder="TOMECO DID"
            editable={false}
          />
        </View>

        {/* Submit Button */}
        <TouchableOpacity
          style={[styles.submitButton, isLoading && styles.submitButtonDisabled]}
          onPress={handleSubmit}
          disabled={isLoading}
        >
          {isLoading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <>
              <MaterialIcons name="check-circle" size={24} color="#fff" />
              <Text style={styles.submitButtonText}>Submit Ticket</Text>
            </>
          )}
        </TouchableOpacity>
      </ScrollView>

      {/* Date Picker Modal */}
      {showDatePicker && (
        <DateTimePicker
          value={formData[datePickerField] || new Date()}
          mode="date"
          display="default"
          onChange={handleDateChange}
        />
      )}

      {/* Time Picker Modal */}
      {showTimePicker && (
        <DateTimePicker
          value={formData[timePickerField] || new Date()}
          mode="time"
          display="default"
          onChange={handleTimeChange}
        />
      )}

      {/* Violation Selection Modal */}
      <Modal
        visible={showViolationModal}
        animationType="slide"
        transparent={false}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>Select Violations</Text>
            <TouchableOpacity
              onPress={() => setShowViolationModal(false)}
              style={styles.closeButton}
            >
              <MaterialIcons name="close" size={24} color="#333" />
            </TouchableOpacity>
          </View>

          <TextInput
            style={styles.searchInput}
            placeholder="Search violations..."
            value={violationSearch}
            onChangeText={setViolationSearch}
          />

          <ScrollView style={styles.violationList}>
            {filteredViolations.map((violation, index) => {
              const isSelected = formData.violations.includes(violation);
              return (
                <TouchableOpacity
                  key={index}
                  style={[
                    styles.violationItem,
                    isSelected && styles.violationItemSelected,
                  ]}
                  onPress={() => toggleViolation(violation)}
                >
                  <Text
                    style={[
                      styles.violationItemText,
                      isSelected && styles.violationItemTextSelected,
                    ]}
                  >
                    {violation}
                  </Text>
                  {isSelected && (
                    <MaterialIcons name="check-circle" size={20} color="#962e2eff" />
                  )}
                </TouchableOpacity>
              );
            })}
          </ScrollView>

          <TouchableOpacity
            style={styles.doneButton}
            onPress={() => setShowViolationModal(false)}
          >
            <Text style={styles.doneButtonText}>Done</Text>
          </TouchableOpacity>
        </View>
      </Modal>

      {/* Signature Modal */}
      <Modal
        visible={showSignatureModal}
        animationType="slide"
        transparent={false}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>
              {signatureType === 'officer' ? 'Officer Signature' : 'Driver Signature'}
            </Text>
            <TouchableOpacity
              onPress={() => setShowSignatureModal(false)}
              style={styles.closeButton}
            >
              <MaterialIcons name="close" size={24} color="#333" />
            </TouchableOpacity>
          </View>

          <View style={styles.signatureContainer}>
            <Text style={styles.signatureNote}>
              Please sign in the box below using your finger or stylus
            </Text>
            {(signatureType === 'officer' && formData.signature) || 
             (signatureType === 'driver' && formData.driver_signature) ? (
              <View style={styles.signaturePreviewContainer}>
                <Text style={styles.signaturePreviewLabel}>Current Signature:</Text>
                <Image
                  source={{
                    uri: signatureType === 'officer' 
                      ? (formData.signature.startsWith('data:') ? formData.signature : `data:image/png;base64,${formData.signature}`)
                      : (formData.driver_signature.startsWith('data:') ? formData.driver_signature : `data:image/png;base64,${formData.driver_signature}`)
                  }}
                  style={styles.signaturePreview}
                  resizeMode="contain"
                />
                <Text style={styles.signaturePreviewNote}>
                  Tap "Clear" to remove and create a new signature
                </Text>
              </View>
            ) : null}
            <View style={styles.signatureCanvasContainer}>
              <SignatureCanvas
                ref={signatureRef}
                onOK={(signature) => {
                  // Signature is captured as base64
                  if (signatureType === 'officer') {
                    updateFormData('signature', signature);
                  } else {
                    updateFormData('driver_signature', signature);
                  }
                  setShowSignatureModal(false);
                  Alert.alert('Success', 'Signature captured successfully!');
                }}
                onEmpty={() => {
                  Alert.alert('Warning', 'Please provide a signature');
                }}
                descriptionText=""
                clearText="Clear"
                confirmText="Save"
                webStyle={`
                  .m-signature-pad {
                    box-shadow: none;
                    border: 2px solid #962e2eff;
                    border-radius: 8px;
                  }
                  .m-signature-pad--body {
                    border: none;
                  }
                  .m-signature-pad--body canvas {
                    border-radius: 8px;
                    background-color: #fff;
                  }
                  .m-signature-pad--footer {
                    border-top: 1px solid #ddd;
                    background-color: #f9f9f9;
                  }
                  .button {
                    background-color: #962e2eff;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    font-size: 16px;
                    font-weight: 600;
                    margin: 5px;
                  }
                  .button.clear {
                    background-color: #e53935;
                  }
                `}
                autoClear={false}
                imageType="image/png"
              />
            </View>
          </View>

          <View style={styles.signatureActions}>
            <TouchableOpacity
              style={styles.clearButton}
              onPress={() => {
                if (signatureRef.current) {
                  signatureRef.current.clear();
                }
                // Also clear the stored signature
                if (signatureType === 'officer') {
                  updateFormData('signature', '');
                } else {
                  updateFormData('driver_signature', '');
                }
              }}
            >
              <MaterialIcons name="refresh" size={20} color="#333" />
              <Text style={styles.clearButtonText}>Clear</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.saveButton}
              onPress={() => {
                if (signatureRef.current) {
                  signatureRef.current.readSignature();
                } else {
                  Alert.alert('Error', 'Signature canvas not ready. Please try again.');
                }
              }}
            >
              <MaterialIcons name="check" size={20} color="#fff" />
              <Text style={styles.saveButtonText}>Save Signature</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 100,
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
  section: {
    backgroundColor: '#fff',
    margin: 15,
    padding: 15,
    borderRadius: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#962e2eff',
    marginBottom: 15,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 5,
    marginTop: 10,
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: '#fff',
    minHeight: 44,
  },
  textArea: {
    minHeight: 100,
    textAlignVertical: 'top',
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 10,
  },
  halfWidth: {
    flex: 1,
  },
  thirdWidth: {
    flex: 1,
  },
  radioGroup: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 5,
  },
  radioOption: {
    flex: 1,
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#ddd',
    alignItems: 'center',
    backgroundColor: '#fff',
  },
  radioOptionSelected: {
    backgroundColor: '#962e2eff',
    borderColor: '#962e2eff',
  },
  radioText: {
    fontSize: 14,
    color: '#333',
  },
  radioTextSelected: {
    color: '#fff',
    fontWeight: '600',
  },
  violationButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    backgroundColor: '#fff',
  },
  violationButtonText: {
    fontSize: 16,
    color: '#333',
  },
  selectedViolations: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 10,
    marginBottom: 10,
  },
  violationTag: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#962e2eff',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    marginRight: 8,
    marginBottom: 8,
    maxWidth: '100%',
    flexShrink: 1,
    alignSelf: 'flex-start',
  },
  violationTagText: {
    color: '#fff',
    fontSize: 12,
    flexShrink: 1,
    maxWidth: '85%',
  },
  removeTag: {
    backgroundColor: 'rgba(255,255,255,0.3)',
    borderRadius: 10,
    width: 20,
    height: 20,
    alignItems: 'center',
    justifyContent: 'center',
  },
  priceContainer: {
    marginTop: 15,
    padding: 15,
    backgroundColor: '#eff6ff',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#3b82f6',
  },
  priceLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1e40af',
  },
  priceValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#3b82f6',
    marginTop: 5,
  },
  imageButtons: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 10,
  },
  imageButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 15,
    borderRadius: 8,
    backgroundColor: '#f0f0f0',
    gap: 8,
  },
  imageButtonText: {
    fontSize: 14,
    color: '#962e2eff',
    fontWeight: '600',
  },
  imageGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 15,
    gap: 10,
  },
  imageContainer: {
    width: '48%',
    position: 'relative',
  },
  imagePreview: {
    width: '100%',
    height: 150,
    borderRadius: 8,
    resizeMode: 'cover',
  },
  removeImageButton: {
    position: 'absolute',
    top: 5,
    right: 5,
    backgroundColor: '#e53935',
    borderRadius: 15,
    width: 30,
    height: 30,
    alignItems: 'center',
    justifyContent: 'center',
  },
  signatureButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 15,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#962e2eff',
    backgroundColor: '#fff',
    gap: 10,
    marginTop: 10,
  },
  signatureButtonText: {
    fontSize: 16,
    color: '#962e2eff',
    fontWeight: '600',
  },
  submitButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#962e2eff',
    padding: 18,
    borderRadius: 10,
    margin: 15,
    gap: 10,
  },
  submitButtonDisabled: {
    opacity: 0.6,
  },
  submitButtonText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
  },
  modalContainer: {
    flex: 1,
    backgroundColor: '#fff',
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#ddd',
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  closeButton: {
    padding: 5,
  },
  searchInput: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    margin: 15,
    fontSize: 16,
  },
  violationList: {
    flex: 1,
    padding: 15,
  },
  violationItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 15,
    borderRadius: 8,
    backgroundColor: '#f9f9f9',
    marginBottom: 10,
  },
  violationItemSelected: {
    backgroundColor: '#eff6ff',
    borderWidth: 1,
    borderColor: '#962e2eff',
  },
  violationItemText: {
    flex: 1,
    fontSize: 14,
    color: '#333',
  },
  violationItemTextSelected: {
    color: '#962e2eff',
    fontWeight: '600',
  },
  doneButton: {
    backgroundColor: '#962e2eff',
    padding: 18,
    alignItems: 'center',
    justifyContent: 'center',
  },
  doneButtonText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
  },
  signatureContainer: {
    flex: 1,
    padding: 20,
  },
  signatureNote: {
    fontSize: 14,
    color: '#666',
    textAlign: 'center',
    marginBottom: 15,
  },
  signatureCanvasContainer: {
    flex: 1,
    borderWidth: 2,
    borderColor: '#962e2eff',
    borderRadius: 8,
    overflow: 'hidden',
    backgroundColor: '#fff',
    minHeight: 300,
  },
  signaturePreviewContainer: {
    marginBottom: 15,
    padding: 15,
    backgroundColor: '#f9f9f9',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  signaturePreviewLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 10,
  },
  signaturePreview: {
    width: '100%',
    height: 150,
    borderRadius: 8,
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
  },
  signaturePreviewNote: {
    fontSize: 12,
    color: '#666',
    marginTop: 8,
    fontStyle: 'italic',
  },
  signatureActions: {
    flexDirection: 'row',
    padding: 20,
    gap: 10,
  },
  clearButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 15,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#ddd',
    backgroundColor: '#fff',
    gap: 8,
  },
  clearButtonText: {
    fontSize: 16,
    color: '#333',
    fontWeight: '600',
  },
  saveButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 15,
    borderRadius: 8,
    backgroundColor: '#962e2eff',
    gap: 8,
  },
  saveButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#fff',
  },
  autoFilledInput: {
    backgroundColor: '#f0f7ff',
    borderColor: '#3b82f6',
  },
});

