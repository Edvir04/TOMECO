import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Image,
  Modal,
  Dimensions,
  Platform,
} from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { CameraView, useCameraPermissions } from 'expo-camera';
import * as ImagePicker from 'expo-image-picker';
import API from '../config/api';
import AsyncStorage from '@react-native-async-storage/async-storage';

const { width, height } = Dimensions.get('window');

export default function OCRScan({ navigation, route }) {
  const { enforcer } = route.params || {};
  const [permission, requestPermission] = useCameraPermissions();
  const [isProcessing, setIsProcessing] = useState(false);
  const [capturedImage, setCapturedImage] = useState(null);
  const [showPreview, setShowPreview] = useState(false);
  const cameraRef = useRef(null);

  const handleCapture = async () => {
    if (!cameraRef.current) {
      Alert.alert('Error', 'Camera not ready');
      return;
    }

    try {
      const photo = await cameraRef.current.takePictureAsync({
        quality: 0.8,
        base64: true,
        skipProcessing: false,
      });

      if (photo) {
        setCapturedImage({
          uri: photo.uri,
          base64: photo.base64,
        });
        setShowPreview(true);
      }
    } catch (error) {
      console.error('Error capturing photo:', error);
      Alert.alert('Error', 'Failed to capture photo');
    }
  };

  const handleRetake = () => {
    setCapturedImage(null);
    setShowPreview(false);
  };

  const handleProcessOCR = async () => {
    if (!capturedImage) {
      Alert.alert('Error', 'No image captured');
      return;
    }

    setIsProcessing(true);

    try {
      let token = await AsyncStorage.getItem('auth_token');
      if (!token) {
        Alert.alert('Error', 'Authentication token not found. Please login again.');
        navigation.goBack();
        return;
      }
      
      // Clean token (remove any whitespace or quotes)
      token = token.trim().replace(/^["']|["']$/g, '');
      
      // Log token info for debugging (don't log full token for security)
      console.log('Token length:', token?.length);
      console.log('Token starts with:', token ? token.substring(0, 10) + '...' : 'none');
      
      if (!token) {
        Alert.alert(
          'Authentication Error',
          'No authentication token found. Please login again.',
          [
            { text: 'OK', onPress: () => navigation.navigate('Login') },
          ]
        );
        return;
      }
      
      // Validate token format - accept both UUID tokens (Node.js server) and Sanctum tokens (Laravel)
      // UUID format: "db48e16a-b6e2-4f3c-a86e-823c4013e31d" (36 characters)
      // Sanctum format: "1|abc123..." (starts with number and pipe)
      const isUUID = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(token);
      const isSanctum = /^\d+\|/.test(token);
      
      if (!isUUID && !isSanctum) {
        console.error('Invalid token format detected. Token does not match UUID or Sanctum format.');
        Alert.alert(
          'Invalid Token',
          'Your authentication token is invalid. Please login again to get a new token.',
          [
            {
              text: 'Login',
              onPress: async () => {
                await AsyncStorage.multiRemove(['auth_token', 'enforcer_data']);
                navigation.reset({
                  index: 0,
                  routes: [{ name: 'Login' }],
                });
              },
            },
          ]
        );
        setIsProcessing(false);
        return;
      }

      // First, test server connectivity
      try {
        console.log('Testing server connectivity...');
        const healthCheck = await fetch(API.HEALTH, {
          method: 'GET',
        });
        
        // Check if response is ok first
        if (!healthCheck.ok) {
          const errorText = await healthCheck.text();
          console.error('Health check failed with status:', healthCheck.status, 'Response:', errorText);
          throw new Error(`Server health check failed: ${healthCheck.status} ${errorText.substring(0, 100)}`);
        }
        
        // Check content-type before parsing JSON
        const contentType = healthCheck.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          const responseText = await healthCheck.text();
          console.error('Health check returned non-JSON response:', responseText.substring(0, 200));
          throw new Error(`Server returned non-JSON response: ${responseText.substring(0, 100)}`);
        }
        
        // Parse JSON response
        const healthResult = await healthCheck.json();
        console.log('Server health check:', healthResult);
      } catch (healthError) {
        console.error('Health check failed:', healthError);
        const errorMessage = healthError.message || 'Unknown error';
        Alert.alert(
          'Connection Error',
          `Cannot connect to server at ${API.HEALTH}\n\nError: ${errorMessage}\n\nPlease check:\n1. Node.js server is running\n2. Correct IP address in config\n3. Same network connection`,
          [
            { text: 'OK', onPress: () => setIsProcessing(false) },
          ]
        );
        return;
      }

      // Prepare form data for React Native
      const formData = new FormData();
      
      // React Native FormData format for file upload
      const imageUri = capturedImage.uri;
      const imageName = `id_card_${Date.now()}.jpg`;
      
      // Handle URI format for different platforms
      let fileUri = imageUri;
      if (Platform.OS === 'android') {
        // Android: keep as is
        fileUri = imageUri;
      } else if (Platform.OS === 'ios') {
        // iOS: remove file:// prefix if present
        fileUri = imageUri.replace('file://', '');
      }
      
      // For React Native, use this format
      formData.append('image', {
        uri: fileUri,
        type: 'image/jpeg',
        name: imageName,
      });

      // Send to OCR API
      console.log('Sending OCR request to:', API.OCR.SCAN_ID);
      console.log('Image URI:', imageUri);
      console.log('Processed URI:', fileUri);
      console.log('Token exists:', !!token);
      console.log('Token length:', token?.length);
      console.log('Token preview:', token ? `${token.substring(0, 20)}...` : 'none');
      
      // Ensure token is properly formatted
      const authToken = token ? token.trim() : '';
      if (!authToken) {
        Alert.alert('Error', 'Authentication token is missing. Please login again.');
        navigation.goBack();
        return;
      }
      
      const response = await fetch(API.OCR.SCAN_ID, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Accept': 'application/json',
          // Don't set Content-Type - React Native will set it automatically with boundary
        },
        body: formData,
      });

      console.log('OCR Response status:', response.status);
      console.log('OCR Response headers:', response.headers);

      if (!response.ok) {
        let errorText = '';
        try {
          errorText = await response.text();
          console.error('OCR API Error Response:', errorText);
        } catch (e) {
          errorText = `HTTP ${response.status}`;
        }
        
        // Handle 401 Unauthenticated specifically
        if (response.status === 401) {
          Alert.alert(
            'Authentication Error',
            'Your session has expired. Please login again.',
            [
              {
                text: 'Login',
                onPress: () => {
                  // Clear stored data and navigate to login
                  AsyncStorage.multiRemove(['auth_token', 'enforcer_data']).then(() => {
                    navigation.reset({
                      index: 0,
                      routes: [{ name: 'Login' }],
                    });
                  });
                },
              },
            ]
          );
          setIsProcessing(false);
          return;
        }
        
        throw new Error(`Server error: ${response.status} - ${errorText}`);
      }

      let result;
      try {
        const responseText = await response.text();
        console.log('OCR Response text:', responseText);
        result = JSON.parse(responseText);
        console.log('OCR Result:', result);
      } catch (parseError) {
        console.error('Failed to parse response:', parseError);
        throw new Error('Invalid response from server');
      }

      if (response.ok && result.success) {
        // Show validation status if available
        if (result.validation) {
          const validFields = [];
          const invalidFields = [];
          
          if (result.validation.lastname_valid) validFields.push('Last Name');
          else invalidFields.push('Last Name');
          
          if (result.validation.firstname_valid) validFields.push('First Name');
          else invalidFields.push('First Name');
          
          if (result.validation.middlename_valid) validFields.push('Middle Name');
          
          if (result.validation.address_valid) validFields.push('Address');
          else invalidFields.push('Address');
          
          let message = '';
          if (validFields.length > 0) {
            message = `Successfully extracted: ${validFields.join(', ')}`;
          }
          if (invalidFields.length > 0) {
            message += (message ? '\n\n' : '') + `Could not extract: ${invalidFields.join(', ')}`;
          }
          
          if (message) {
            Alert.alert(
              'OCR Processing Complete',
              message + '\n\nPlease review and complete any missing fields.',
              [{ text: 'OK' }]
            );
          }
        }
        
        // Navigate to IssueTicket with OCR data
        navigation.navigate('IssueTicket', {
          enforcer,
          useOCR: true,
          ocrData: result.data,
        });
      } else {
        Alert.alert(
          'OCR Error',
          result.message || 'Failed to process ID card. Please try again or use manual input.',
          [
            { text: 'Try Again', onPress: handleRetake },
            {
              text: 'Manual Input',
              onPress: () => navigation.navigate('IssueTicket', { enforcer, useOCR: false }),
            },
          ]
        );
      }
    } catch (error) {
      console.error('OCR processing error:', error);
      const errorMessage = error.message || 'Network request failed';
      
      // More detailed error message
      let userMessage = 'Failed to process ID card. ';
      if (errorMessage.includes('Network request failed')) {
        userMessage += 'Please check:\n\n';
        userMessage += '1. Laravel server is running (php artisan serve)\n';
        userMessage += '2. Server URL is correct in config/api.js\n';
        userMessage += '3. You are connected to the same network\n';
        userMessage += '4. Firewall is not blocking the connection';
      } else {
        userMessage += errorMessage;
      }
      
      Alert.alert(
        'OCR Error',
        userMessage,
        [
          { text: 'Try Again', onPress: handleRetake },
          {
            text: 'Manual Input',
            onPress: () => navigation.navigate('IssueTicket', { enforcer, useOCR: false }),
          },
        ]
      );
    } finally {
      setIsProcessing(false);
    }
  };

  const handlePickFromGallery = async () => {
    try {
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [3, 2],
        quality: 0.8,
        base64: true,
      });

      if (!result.canceled && result.assets && result.assets[0]) {
        setCapturedImage({
          uri: result.assets[0].uri,
          base64: result.assets[0].base64,
        });
        setShowPreview(true);
      }
    } catch (error) {
      console.error('Error picking image:', error);
      Alert.alert('Error', 'Failed to pick image');
    }
  };

  if (!permission) {
    return (
      <View style={styles.container}>
        <ActivityIndicator size="large" color="#962e2eff" />
      </View>
    );
  }

  if (!permission.granted) {
    return (
      <View style={styles.container}>
        <View style={styles.permissionContainer}>
          <MaterialIcons name="camera-alt" size={64} color="#962e2eff" />
          <Text style={styles.permissionTitle}>Camera Permission Required</Text>
          <Text style={styles.permissionText}>
            We need access to your camera to scan ID cards
          </Text>
          <TouchableOpacity
            style={styles.permissionButton}
            onPress={requestPermission}
          >
            <Text style={styles.permissionButtonText}>Grant Permission</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={styles.backButton}
            onPress={() => navigation.goBack()}
          >
            <Text style={styles.backButtonText}>Go Back</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {!showPreview ? (
        <>
          <CameraView
            ref={cameraRef}
            style={styles.camera}
            facing="back"
          >
            <View style={styles.cameraOverlay}>
              <View style={styles.overlayTop}>
                <TouchableOpacity
                  style={styles.closeButton}
                  onPress={() => navigation.goBack()}
                >
                  <MaterialIcons name="close" size={28} color="#fff" />
                </TouchableOpacity>
              </View>

              <View style={styles.idCardFrame}>
                <View style={styles.frameCorner} />
                <View style={[styles.frameCorner, styles.frameCornerTopRight]} />
                <View style={[styles.frameCorner, styles.frameCornerBottomLeft]} />
                <View style={[styles.frameCorner, styles.frameCornerBottomRight]} />
                <Text style={styles.frameText}>Position ID card here</Text>
              </View>

              <View style={styles.overlayBottom}>
                <TouchableOpacity
                  style={styles.galleryButton}
                  onPress={handlePickFromGallery}
                >
                  <MaterialIcons name="photo-library" size={24} color="#fff" />
                </TouchableOpacity>

                <TouchableOpacity
                  style={styles.captureButton}
                  onPress={handleCapture}
                >
                  <View style={styles.captureButtonInner} />
                </TouchableOpacity>

                <View style={styles.galleryButton} />
              </View>
            </View>
          </CameraView>
        </>
      ) : (
        <View style={styles.previewContainer}>
          <Image source={{ uri: capturedImage.uri }} style={styles.previewImage} />
          
          <View style={styles.previewActions}>
            <TouchableOpacity
              style={styles.retakeButton}
              onPress={handleRetake}
            >
              <MaterialIcons name="refresh" size={24} color="#962e2eff" />
              <Text style={styles.retakeButtonText}>Retake</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.processButton}
              onPress={handleProcessOCR}
              disabled={isProcessing}
            >
              {isProcessing ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <>
                  <MaterialIcons name="text-fields" size={24} color="#fff" />
                  <Text style={styles.processButtonText}>Process OCR</Text>
                </>
              )}
            </TouchableOpacity>
          </View>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  camera: {
    flex: 1,
  },
  cameraOverlay: {
    flex: 1,
    backgroundColor: 'transparent',
  },
  overlayTop: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    padding: 20,
    paddingTop: 50,
  },
  closeButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  idCardFrame: {
    flex: 1,
    marginHorizontal: 40,
    marginVertical: 100,
    borderWidth: 2,
    borderColor: '#962e2eff',
    borderRadius: 12,
    backgroundColor: 'transparent',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  frameCorner: {
    position: 'absolute',
    width: 30,
    height: 30,
    borderLeftWidth: 3,
    borderBottomWidth: 3,
    borderColor: '#962e2eff',
    bottom: -2,
    left: -2,
  },
  frameCornerTopRight: {
    borderLeftWidth: 0,
    borderRightWidth: 3,
    borderTopWidth: 3,
    borderBottomWidth: 0,
    top: -2,
    right: -2,
    left: 'auto',
    bottom: 'auto',
  },
  frameCornerBottomLeft: {
    borderLeftWidth: 3,
    borderRightWidth: 0,
    borderTopWidth: 3,
    borderBottomWidth: 0,
    top: -2,
    bottom: 'auto',
  },
  frameCornerBottomRight: {
    borderLeftWidth: 0,
    borderRightWidth: 3,
    borderTopWidth: 3,
    borderBottomWidth: 0,
    top: -2,
    right: -2,
    left: 'auto',
    bottom: 'auto',
  },
  frameText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
    textAlign: 'center',
    backgroundColor: 'rgba(150, 46, 46, 0.8)',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 8,
  },
  overlayBottom: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    alignItems: 'center',
    padding: 30,
    paddingBottom: 50,
  },
  galleryButton: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  captureButton: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#fff',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 4,
    borderColor: '#962e2eff',
  },
  captureButtonInner: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#962e2eff',
  },
  previewContainer: {
    flex: 1,
    backgroundColor: '#000',
  },
  previewImage: {
    flex: 1,
    width: '100%',
    resizeMode: 'contain',
  },
  previewActions: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    padding: 20,
    paddingBottom: 40,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
  },
  retakeButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 15,
    borderRadius: 10,
    backgroundColor: '#fff',
    gap: 8,
  },
  retakeButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#962e2eff',
  },
  processButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 15,
    paddingHorizontal: 30,
    borderRadius: 10,
    backgroundColor: '#962e2eff',
    gap: 8,
  },
  processButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#fff',
  },
  permissionContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 40,
    backgroundColor: '#fff',
  },
  permissionTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginTop: 20,
    marginBottom: 10,
  },
  permissionText: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
    marginBottom: 30,
  },
  permissionButton: {
    backgroundColor: '#962e2eff',
    paddingHorizontal: 30,
    paddingVertical: 15,
    borderRadius: 10,
    marginBottom: 15,
  },
  permissionButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  backButton: {
    padding: 15,
  },
  backButtonText: {
    color: '#666',
    fontSize: 16,
  },
});

