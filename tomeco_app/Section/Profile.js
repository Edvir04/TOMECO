import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Image,
  Modal,
  ScrollView,
  Dimensions,
} from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { CommonActions } from '@react-navigation/native';
import API from '../config/api';

export default function Profile({ route, navigation }) {
  const { enforcer } = route.params || {};
  const [enforcerData, setEnforcerData] = useState(enforcer || null);
  const [isLoading, setIsLoading] = useState(true);
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [showDetailsModal, setShowDetailsModal] = useState(false);

  useEffect(() => {
    loadProfileData();
  }, []);

  // Reload when route params change
  useEffect(() => {
    if (route.params?.enforcer) {
      setEnforcerData(route.params.enforcer);
    }
  }, [route.params]);

  const loadProfileData = async () => {
    try {
      setIsLoading(true);
      
      // First, try to get from route params
      if (enforcer) {
        setEnforcerData(enforcer);
      }
      
      // Then try AsyncStorage
      const storedData = await AsyncStorage.getItem('enforcer_data');
      if (storedData) {
        try {
          const parsedData = JSON.parse(storedData);
          setEnforcerData(parsedData);
        } catch (parseError) {
          console.error('Error parsing stored data:', parseError);
        }
      }

      // Finally, fetch latest from API
      const token = await AsyncStorage.getItem('auth_token');
      if (token) {
        await fetchProfile(token);
      } else {
        setIsLoading(false);
      }
    } catch (error) {
      console.error('Error loading profile data:', error);
      setIsLoading(false);
    }
  };

  const fetchProfile = async (token) => {
    try {
      // Get user ID from stored enforcer data
      const storedData = await AsyncStorage.getItem('enforcer_data');
      let userId = null;
      
      if (storedData) {
        try {
          const parsed = JSON.parse(storedData);
          userId = parsed.id;
        } catch (e) {
          console.error('Error parsing stored data for user ID:', e);
        }
      }
      
      // If no user ID, try to get from current enforcerData
      if (!userId && enforcerData?.id) {
        userId = enforcerData.id;
      }
      
      if (!userId) {
        console.warn('No user ID available for profile fetch');
        setIsLoading(false);
        return;
      }
      
      // Add user_id as query parameter
      const profileUrl = `${API.PROFILE}?user_id=${userId}`;
      
      const response = await fetch(profileUrl, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data) {
          setEnforcerData(data.data);
          await AsyncStorage.setItem('enforcer_data', JSON.stringify(data.data));
        } else {
          console.warn('Profile API returned unsuccessful response:', data);
        }
      } else {
        const errorData = await response.json().catch(() => ({}));
        console.error('Profile API error:', response.status, errorData);
      }
    } catch (error) {
      console.error('Error fetching profile:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleLogout = async () => {
    Alert.alert(
      "Sign Out",
      "Are you sure you want to sign out?",
      [
        { text: "Cancel", style: "cancel" },
        {
          text: "Sign Out",
          style: "destructive",
          onPress: async () => {
            try {
              setIsLoggingOut(true);
              const token = await AsyncStorage.getItem('auth_token');
              
              // Call logout API if available
              if (token && API.LOGOUT) {
                try {
                  await fetch(API.LOGOUT, {
                    method: 'POST',
                    headers: {
                      'Authorization': `Bearer ${token}`,
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                    },
                  });
                } catch (error) {
                  console.error('Logout API error:', error);
                  // Continue with logout even if API call fails
                }
              }

              // Clear stored data
              await AsyncStorage.multiRemove(['auth_token', 'enforcer_data']);
              
              // Navigate back to login using CommonActions
              navigation.dispatch(
                CommonActions.reset({
                  index: 0,
                  routes: [{ name: 'Login' }],
                })
              );
            } catch (error) {
              console.error('Error during logout:', error);
              Alert.alert("Error", "Failed to sign out. Please try again.");
              setIsLoggingOut(false);
            }
          },
        },
      ]
    );
  };

  const handleViewAccountDetails = () => {
    setShowDetailsModal(true);
  };

  const handleTermsPrivacy = () => {
    Alert.alert("Terms & Privacy Policy", "Terms and Privacy Policy content will be available soon.");
  };

  const handleAboutApp = () => {
    Alert.alert("About App", "Tomeco App - Traffic Ordinance Management and Enforcement System");
  };

  // Construct full URL for profile picture
  const getProfilePictureUrl = () => {
    if (!enforcerData?.profile_picture) {
      console.log('No profile picture in enforcerData');
      return null;
    }
    
    const picUrl = enforcerData.profile_picture;
    console.log('Original profile_picture value:', picUrl);
    
    // If it's already a full URL, return it
    if (picUrl.startsWith('http://') || picUrl.startsWith('https://')) {
      console.log('Profile picture is already a full URL:', picUrl);
      return picUrl;
    }
    
    // Use Node.js server URL for storage files (since Laravel may not be accessible)
    // The Node.js server now serves static files from Laravel storage
    const nodeBaseUrl = API.BASE_URL.replace('/api', '');
    console.log('Node.js base URL:', nodeBaseUrl);
    
    // If it starts with /storage, use as is
    if (picUrl.startsWith('/storage/')) {
      const fullUrl = `${nodeBaseUrl}${picUrl}`;
      console.log('Constructed URL (with /storage):', fullUrl);
      return fullUrl;
    }
    
    // Otherwise, prepend /storage/ to the path
    // picUrl format: "profile-pictures/filename.jpg"
    // Result: "http://node-server-url/storage/profile-pictures/filename.jpg"
    const fullUrl = `${nodeBaseUrl}/storage/${picUrl}`;
    console.log('Constructed URL (prepended /storage):', fullUrl);
    return fullUrl;
  };

  // Show loading state
  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#962e2eff" />
        <Text style={styles.loadingText}>Loading profile...</Text>
      </View>
    );
  }

  // Show error state if no data
  if (!enforcerData) {
    return (
      <View style={styles.loadingContainer}>
        <MaterialIcons name="error-outline" size={64} color="#962e2eff" />
        <Text style={styles.errorText}>Unable to load profile data</Text>
        <TouchableOpacity
          style={styles.retryButton}
          onPress={loadProfileData}
        >
          <Text style={styles.retryButtonText}>Retry</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const profilePictureUrl = getProfilePictureUrl();
  const displayName = (enforcerData?.fullname || enforcerData?.username || 'Enforcer').toUpperCase();
  const employeeId = enforcerData?.id_number || 'N/A';

  return (
    <View style={styles.container}>
      {/* Red Header Section */}
      <View style={styles.headerSection}>
        <View style={styles.profilePictureContainer}>
          {profilePictureUrl ? (
            <Image
              source={{ uri: profilePictureUrl }}
              style={styles.profilePicture}
              onError={(error) => {
                console.log('Failed to load profile picture:', error.nativeEvent.error);
                console.log('Failed URL:', profilePictureUrl);
                // Try to use Node.js server as fallback if Laravel fails
                const nodeBaseUrl = API.BASE_URL.replace('/api', '');
                const fallbackUrl = `${nodeBaseUrl}/storage/${enforcerData.profile_picture}`;
                console.log('Attempting fallback URL:', fallbackUrl);
              }}
              onLoad={() => {
                console.log('Profile picture loaded successfully:', profilePictureUrl);
              }}
              resizeMode="cover"
            />
          ) : (
            <View style={styles.profilePicturePlaceholder}>
              <MaterialIcons name="account-circle" size={100} color="#fff" />
            </View>
          )}
          <TouchableOpacity
            style={styles.profileIconContainer}
            onPress={handleViewAccountDetails}
            activeOpacity={0.8}
            disabled={isLoggingOut}
          >
            <MaterialIcons name="person" size={18} color="#fff" />
          </TouchableOpacity>
        </View>

        <Text style={styles.nameText}>{displayName}</Text>
        <Text style={styles.employeeIdText}>EMPLOYEE ID: {employeeId}</Text>
      </View>

      {/* Cream/Yellow Section with Options */}
      <View style={styles.optionsSection}>
        <TouchableOpacity
          style={styles.optionButton}
          onPress={handleTermsPrivacy}
          activeOpacity={0.8}
          disabled={isLoggingOut}
        >
          <View style={styles.optionIconContainer}>
            <MaterialIcons name="description" size={24} color="#333" />
          </View>
          <Text style={styles.optionText}>TERMS & PRIVACY POLICY</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.optionButton}
          onPress={handleAboutApp}
          activeOpacity={0.8}
          disabled={isLoggingOut}
        >
          <View style={styles.optionIconContainer}>
            <MaterialIcons name="info" size={24} color="#333" />
          </View>
          <Text style={styles.optionText}>ABOUT APP</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.optionButton, isLoggingOut && styles.optionButtonDisabled]}
          onPress={handleLogout}
          activeOpacity={0.8}
          disabled={isLoggingOut}
        >
          <View style={styles.optionIconContainer}>
            {isLoggingOut ? (
              <ActivityIndicator size="small" color="#333" />
            ) : (
              <MaterialIcons name="exit-to-app" size={24} color="#333" />
            )}
          </View>
          <Text style={styles.optionText}>
            {isLoggingOut ? 'SIGNING OUT...' : 'SIGN OUT'}
          </Text>
        </TouchableOpacity>
      </View>

      {/* Account Details Modal */}
      <Modal
        visible={showDetailsModal}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setShowDetailsModal(false)}
      >
        <TouchableOpacity
          style={styles.modalOverlay}
          activeOpacity={1}
          onPress={() => setShowDetailsModal(false)}
        >
          <TouchableOpacity
            activeOpacity={1}
            onPress={(e) => e.stopPropagation()}
            style={styles.modalContent}
          >
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Account Details</Text>
              <TouchableOpacity
                onPress={() => setShowDetailsModal(false)}
                style={styles.closeButton}
              >
                <MaterialIcons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>

            <ScrollView 
              style={styles.modalBody} 
              contentContainerStyle={styles.modalBodyContent}
              showsVerticalScrollIndicator={false}
            >
              <View style={styles.detailSection}>
                <View style={styles.detailRow}>
                  <MaterialIcons name="person" size={20} color="#962e2eff" />
                  <View style={styles.detailContent}>
                    <Text style={styles.detailLabel}>Full Name</Text>
                    <Text style={styles.detailValue}>
                      {enforcerData?.fullname || 'N/A'}
                    </Text>
                  </View>
                </View>

                <View style={styles.detailRow}>
                  <MaterialIcons name="badge" size={20} color="#962e2eff" />
                  <View style={styles.detailContent}>
                    <Text style={styles.detailLabel}>Employee ID</Text>
                    <Text style={styles.detailValue}>
                      {enforcerData?.id_number || 'N/A'}
                    </Text>
                  </View>
                </View>

                <View style={styles.detailRow}>
                  <MaterialIcons name="account-circle" size={20} color="#962e2eff" />
                  <View style={styles.detailContent}>
                    <Text style={styles.detailLabel}>Username</Text>
                    <Text style={styles.detailValue}>
                      {enforcerData?.username || 'N/A'}
                    </Text>
                  </View>
                </View>

                {enforcerData?.gender && (
                  <View style={styles.detailRow}>
                    <MaterialIcons name="wc" size={20} color="#962e2eff" />
                    <View style={styles.detailContent}>
                      <Text style={styles.detailLabel}>Gender</Text>
                      <Text style={styles.detailValue}>
                        {enforcerData.gender.charAt(0).toUpperCase() + enforcerData.gender.slice(1)}
                      </Text>
                    </View>
                  </View>
                )}

                {enforcerData?.dob && (
                  <View style={styles.detailRow}>
                    <MaterialIcons name="cake" size={20} color="#962e2eff" />
                    <View style={styles.detailContent}>
                      <Text style={styles.detailLabel}>Date of Birth</Text>
                      <Text style={styles.detailValue}>
                        {new Date(enforcerData.dob).toLocaleDateString('en-US', {
                          year: 'numeric',
                          month: 'long',
                          day: 'numeric'
                        })}
                      </Text>
                    </View>
                  </View>
                )}

                {enforcerData?.contact_number && (
                  <View style={styles.detailRow}>
                    <MaterialIcons name="phone" size={20} color="#962e2eff" />
                    <View style={styles.detailContent}>
                      <Text style={styles.detailLabel}>Contact Number</Text>
                      <Text style={styles.detailValue}>
                        {enforcerData.contact_number}
                      </Text>
                    </View>
                  </View>
                )}

                <View style={styles.detailRow}>
                  <MaterialIcons name="location-on" size={20} color="#962e2eff" />
                  <View style={styles.detailContent}>
                    <Text style={styles.detailLabel}>Address</Text>
                    <Text style={styles.detailValue}>
                      {enforcerData?.address || 'N/A'}
                    </Text>
                  </View>
                </View>

                {enforcerData?.created_at && (
                  <View style={styles.detailRow}>
                    <MaterialIcons name="calendar-today" size={20} color="#962e2eff" />
                    <View style={styles.detailContent}>
                      <Text style={styles.detailLabel}>Account Created</Text>
                      <Text style={styles.detailValue}>
                        {new Date(enforcerData.created_at).toLocaleDateString('en-US', {
                          year: 'numeric',
                          month: 'long',
                          day: 'numeric'
                        })}
                      </Text>
                    </View>
                  </View>
                )}
              </View>
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
    padding: 20,
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: '#666',
  },
  errorText: {
    marginTop: 16,
    fontSize: 16,
    color: '#962e2eff',
    textAlign: 'center',
    marginBottom: 20,
  },
  retryButton: {
    backgroundColor: '#962e2eff',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 8,
  },
  retryButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  // Red Header Section - Reduced Height
  headerSection: {
    backgroundColor: '#962e2eff',
    paddingTop: 40,
    paddingBottom: 30,
    paddingHorizontal: 20,
    borderBottomLeftRadius: 30,
    borderBottomRightRadius: 30,
    alignItems: 'center',
    justifyContent: 'center',
  },
  profilePictureContainer: {
    position: 'relative',
    marginBottom: 15,
  },
  profilePicture: {
    width: 120,
    height: 120,
    borderRadius: 60,
    borderWidth: 4,
    borderColor: '#fff',
    backgroundColor: '#fff',
  },
  profilePicturePlaceholder: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 4,
    borderColor: '#fff',
  },
  profileIconContainer: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#962e2eff',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#fff',
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
  detailSection: {
    marginBottom: 10,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 15,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  detailContent: {
    flex: 1,
    marginLeft: 15,
  },
  detailLabel: {
    fontSize: 12,
    color: '#999',
    marginBottom: 5,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  detailValue: {
    fontSize: 16,
    color: '#333',
    fontWeight: '500',
  },
  nameText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 6,
    letterSpacing: 0.5,
  },
  employeeIdText: {
    fontSize: 13,
    color: '#e0e0e0',
    letterSpacing: 0.5,
  },
  // Cream/Yellow Options Section
  optionsSection: {
    flex: 1,
    backgroundColor: '#fef9e7',
    paddingTop: 25,
    paddingHorizontal: 20,
    paddingBottom: 20,
  },
  optionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fef9e7',
    paddingVertical: 14,
    paddingHorizontal: 18,
    borderRadius: 12,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  optionButtonDisabled: {
    opacity: 0.6,
  },
  optionIconContainer: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#fff',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  optionText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#333',
    letterSpacing: 0.5,
  },
});

