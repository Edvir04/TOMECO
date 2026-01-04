// LoginScreen.js
import React, { useState, useEffect, useRef } from "react";
import { 
  View, Text, StyleSheet, TextInput, TouchableOpacity, Image, 
  Dimensions, Alert, ActivityIndicator, KeyboardAvoidingView, 
  Platform, ScrollView, TouchableWithoutFeedback, Keyboard,
  Animated
} from "react-native";
import { MaterialIcons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import API from "../config/api";
import { isOnline } from "../services/NetworkStatus";
import { 
  attemptOfflineLogin, 
  cacheCredentials, 
  cacheUserData, 
  syncUserData,
  getCachedCredentials,
  getCachedUserData
} from "../services/OfflineAuth";

export default function LoginScreen({ navigation }) {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [keyboardVisible, setKeyboardVisible] = useState(false);
  const scrollViewRef = useRef(null);
  const usernameInputRef = useRef(null);
  const passwordInputRef = useRef(null);

  useEffect(() => {
    const keyboardWillShow = Keyboard.addListener(
      Platform.OS === 'ios' ? 'keyboardWillShow' : 'keyboardDidShow',
      () => {
        setKeyboardVisible(true);
      }
    );

    const keyboardWillHide = Keyboard.addListener(
      Platform.OS === 'ios' ? 'keyboardWillHide' : 'keyboardDidHide',
      () => {
        setKeyboardVisible(false);
      }
    );

    return () => {
      keyboardWillShow.remove();
      keyboardWillHide.remove();
    };
  }, []);

  // Auto-load cached credentials on mount
  useEffect(() => {
    const loadCachedCredentials = async () => {
      try {
        const cached = await getCachedCredentials();
        if (cached && cached.username) {
          setUsername(cached.username);
          // Don't auto-fill password for security
        }
      } catch (error) {
        console.log('No cached credentials found or error loading:', error);
      }
    };

    loadCachedCredentials();
  }, []);

  const handleLogin = async () => {
    if (!username || !password) {
      Alert.alert("Error", "Please enter both username and password");
      return;
    }

    setIsLoading(true);
    try {
      const online = isOnline();
      
      // If offline, try offline login
      if (!online) {
        const offlineResult = await attemptOfflineLogin(username, password);
        
        if (offlineResult.success) {
          // Store token and user data (use a placeholder token for offline)
          await AsyncStorage.setItem('auth_token', 'offline_token_' + Date.now());
          await AsyncStorage.setItem('enforcer_data', JSON.stringify(offlineResult.user));
          
          Alert.alert(
            "Offline Login",
            "Logged in with cached credentials (offline mode). Some features may be limited.",
            [
              {
                text: "OK",
                onPress: () => {
                  navigation.navigate('MainScreen', { enforcer: offlineResult.user });
                }
              }
            ]
          );
          setIsLoading(false);
          return;
        } else {
          Alert.alert(
            "Offline Login Failed",
            offlineResult.message + "\n\nPlease connect to the internet to login.",
            [{ text: "OK" }]
          );
          setIsLoading(false);
          return;
        }
      }
      
      // Online login
      console.log('Attempting to login to:', API.LOGIN);
      
      const response = await fetch(API.LOGIN, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ username, password }),
      });

      // Check if response is actually JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Server returned non-JSON response:', text.substring(0, 200));
        throw new Error(`Server error: Expected JSON but got ${contentType || 'unknown'}. Make sure the server is running.`);
      }

      const data = await response.json();
      
      // Debug: Log full response structure
      console.log('Login - Full response data:', JSON.stringify(data, null, 2));

      if (!response.ok) {
        throw new Error(data.message || `Server error: ${response.status}`);
      }

      if (data.success) {
        // Store token and user data
        const token = data.data?.token;
        const enforcerData = data.data?.enforcer;
        
        console.log('Login - Token received, length:', token?.length);
        console.log('Login - Enforcer data received:', enforcerData ? 'Yes' : 'No');
        
        if (!token) {
          throw new Error('Token not found in response');
        }
        
        if (!enforcerData) {
          throw new Error('Enforcer data not found in response');
        }
        
        // Validate token format - accept both UUID tokens (Node.js server) and Sanctum tokens (Laravel)
        // UUID format: "db48e16a-b6e2-4f3c-a86e-823c4013e31d" (36 characters)
        // Sanctum format: "1|abc123..." (starts with number and pipe)
        const isUUID = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(token);
        const isSanctum = /^\d+\|/.test(token);
        
        if (!isUUID && !isSanctum) {
          console.error('Invalid token format received from server:', token.substring(0, 20));
          throw new Error('Invalid token format received from server. Please try again.');
        }
        
        // Store token and enforcer data
        await AsyncStorage.setItem('auth_token', token);
        await AsyncStorage.setItem('enforcer_data', JSON.stringify(enforcerData));
        
        // IMPORTANT: Cache credentials and user data for offline login
        // This allows the user to login offline in the future
        await cacheCredentials(username, password);
        await cacheUserData(enforcerData);
        
        console.log('Credentials and user data cached for offline login');
        
        // Sync user data in background to get latest updates
        // This ensures cached data is always up-to-date
        if (isOnline()) {
          syncUserData(token).then(result => {
            if (result.success && result.user) {
              console.log('User data synced and updated in cache');
              // Update AsyncStorage with synced data
              AsyncStorage.setItem('enforcer_data', JSON.stringify(result.user));
            }
          }).catch(error => {
            console.log('Background user data sync failed (non-critical):', error);
            // Don't fail login if sync fails - we already have the data from login
          });
        }
        
        Alert.alert("Success", "Login successful!", [
          {
            text: "OK",
            onPress: () => {
              // Navigate to MainScreen with bottom tabs
              navigation.navigate('MainScreen', { enforcer: enforcerData });
              console.log('Login successful, user:', enforcerData);
            }
          }
        ]);
      } else {
        throw new Error(data.message || 'Login failed');
      }
    } catch (error) {
      console.error('Error logging in:', error);
      console.error('Error details:', error.message);
      
      let errorMessage = 'Failed to connect to server. ';
      if (error.message.includes('JSON Parse error') || error.message.includes('Unexpected character')) {
        errorMessage = 'Server returned invalid response. Please check:\n';
        errorMessage += '1. Server is running (node server.js or npm run server)\n';
        errorMessage += '2. Server is accessible at ' + API.LOGIN + '\n';
        errorMessage += '3. Check server console for errors';
      } else if (error.message.includes('Network request failed') || error.message.includes('Failed to fetch')) {
        errorMessage += 'Please check:\n';
        errorMessage += '1. Server is running (node server.js or npm run server)\n';
        errorMessage += '2. API URL is correct in config/api.js\n';
        errorMessage += '3. Device and computer are on same network\n';
        errorMessage += '4. Use your computer IP instead of localhost';
      } else if (error.message.includes('Invalid username') || error.message.includes('401')) {
        errorMessage = 'Invalid username or password. Please try again.';
      } else if (error.message.includes('Server error')) {
        errorMessage = error.message;
      } else {
        errorMessage += error.message || 'Unknown error occurred.';
      }
      
      Alert.alert("Login Error", errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={{ flex: 1 }}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={Platform.select({ ios: 0, android: 0 })}
      enabled={false}
    >
      <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
        <View style={styles.container}>
          {/* Top Section */}
          <View style={[styles.topSection, keyboardVisible && styles.topSectionKeyboard]}>
            <Image 
              source={require("../assets/Tomeco.png")} 
              style={styles.logo} 
            />
            <Text style={styles.appName}>E-TIKET</Text>
          </View>

          {/* Bottom Login Box */}
          <View style={[styles.loginBox, keyboardVisible && styles.loginBoxKeyboard]}>
            <ScrollView
              ref={scrollViewRef}
              style={{ width: "100%" }}
              contentContainerStyle={[
                { alignItems: "center" },
                keyboardVisible ? styles.scrollContentKeyboard : { paddingBottom: 20 }
              ]}
              keyboardShouldPersistTaps="handled"
              showsVerticalScrollIndicator={false}
              nestedScrollEnabled={true}
            >
              <Text style={[styles.loginTitle, keyboardVisible && styles.loginTitleKeyboard]}>Login</Text>

              <View onLayout={() => {}} style={styles.inputContainer}>
                <TextInput 
                  ref={usernameInputRef}
                  placeholder="Username"
                  placeholderTextColor="#f5f5f5"
                  style={[styles.input, keyboardVisible && styles.inputKeyboard]}
                  value={username}
                  onChangeText={setUsername}
                  autoCapitalize="none"
                  returnKeyType="next"
                  onFocus={() => {
                    // Auto-scroll when username input is focused
                    setTimeout(() => {
                      scrollViewRef.current?.scrollTo({ y: 0, animated: true });
                    }, 300);
                  }}
                  onSubmitEditing={() => {
                    passwordInputRef.current?.focus();
                  }}
                />
              </View>
              
              <View onLayout={() => {}} style={styles.inputContainer}>
                <TextInput 
                  ref={passwordInputRef}
                  placeholder="Password" 
                  placeholderTextColor="#f5f5f5"
                  secureTextEntry={!showPassword}
                  style={[styles.input, styles.passwordInput, keyboardVisible && styles.inputKeyboard]}
                  value={password}
                  onChangeText={setPassword}
                  returnKeyType="done"
                  onFocus={() => {
                    // Auto-scroll when password input is focused
                    setTimeout(() => {
                      scrollViewRef.current?.scrollToEnd({ animated: true });
                    }, 300);
                  }}
                  onSubmitEditing={handleLogin}
                />
                <TouchableOpacity 
                  style={styles.eyeIcon}
                  onPress={() => setShowPassword(!showPassword)}
                  activeOpacity={0.7}
                >
                  <MaterialIcons 
                    name={showPassword ? 'visibility-off' : 'visibility'} 
                    size={24} 
                    color="#fff" 
                  />
                </TouchableOpacity>
              </View>

              <TouchableOpacity 
                style={[styles.button, isLoading && styles.buttonDisabled, keyboardVisible && styles.buttonKeyboard]} 
                onPress={handleLogin}
                disabled={isLoading}
              >
                {isLoading ? (
                  <ActivityIndicator size="small" color="#fff" />
                ) : (
                  <Text style={styles.buttonText}>Login</Text>
                )}
              </TouchableOpacity>
            </ScrollView>
          </View>
        </View>
      </TouchableWithoutFeedback>
    </KeyboardAvoidingView>
  );
}

const { height } = Dimensions.get("window");

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#fff",
    alignItems: "center",
  },
  topSection: {
    alignItems: "center",
    marginTop: 100,
    marginBottom: 20, // space before login box
    zIndex: 1,
    position: "absolute",
    top: 0,
    left: 0,
    right: 0,
  },
  topSectionKeyboard: {
    marginTop: 40, // Move up when keyboard is visible
  },
  logo: {
    width: 180,   // enlarged logo
    height: 180,
    resizeMode: "contain",
    marginBottom: 5,
  },
  appName: {
    fontSize: 28,
    fontWeight: "bold",
    color: "#962e2eff",
    letterSpacing: 1,
    marginTop: -30, // pulls text closer to logo 
  },
  loginBox: {
    backgroundColor: "#962e2eff",
    borderTopLeftRadius: 35,
    borderTopRightRadius: 35,
    padding: 30,
    width: "100%",
    alignItems: "center",
    position: "absolute",
    bottom: 0,
    minHeight: height * 0.55, // ~55% of screen height
    maxHeight: height * 0.75, // Allow expansion when keyboard is visible
    zIndex: 0, // Below logo when keyboard is hidden
  },
  loginBoxKeyboard: {
    top: 280, // Position just below the title (E-TIKET)
    bottom: 0, // Extend to bottom
    paddingTop: 15,
    paddingBottom: 10,
    zIndex: 2, // Above the logo when keyboard is visible
  },
  scrollContentKeyboard: {
    paddingTop: 20,
    paddingBottom: 0, // Minimal padding to overlap keyboard
  },
  loginTitle: {
    fontSize: 24,
    fontWeight: "bold",
    color: "#fff",
    marginBottom: 20,
    alignSelf: "flex-start",
  },
  loginTitleKeyboard: {
    marginBottom: 15,
  },
  input: {
    width: "100%",
    height: 50,
    borderBottomWidth: 1,
    borderBottomColor: "#fff",
    color: "#fff",
    marginBottom: 20,
    fontSize: 16,
  },
  inputKeyboard: {
    marginBottom: 15,
    height: 45,
  },
  inputContainer: {
    width: "100%",
    position: "relative",
  },
  passwordInput: {
    paddingRight: 50, // Make room for the eye icon
  },
  eyeIcon: {
    position: "absolute",
    right: 10,
    top: 12,
    padding: 5,
    justifyContent: "center",
    alignItems: "center",
  },
  button: {
    backgroundColor: "#1a1a1aff", // black button
    paddingVertical: 14,
    borderRadius: 25,
    width: "100%",
    alignItems: "center",
    shadowColor: "#000",
    shadowOpacity: 0.2,
    shadowRadius: 3,
    elevation: 4,
  },
  buttonKeyboard: {
    paddingVertical: 12,
    marginTop: 0,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  buttonText: {
    color: "#fff",
    fontWeight: "bold",
    fontSize: 16,
  },
});
