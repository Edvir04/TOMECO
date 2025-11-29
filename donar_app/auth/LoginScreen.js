import React, { useState } from 'react';
import {
  View, Text, TextInput, TouchableOpacity, StyleSheet, Alert,
  ImageBackground, Image, ActivityIndicator,
  KeyboardAvoidingView, Platform, ScrollView, TouchableWithoutFeedback, Keyboard
} from 'react-native';
import Constants from 'expo-constants';
import { Ionicons } from '@expo/vector-icons';

export default function LoginScreen({ navigation }) {
  const getBaseUrl = () => {
    if (Constants.expoConfig?.hostUri) {
      const baseUrl = Constants.expoConfig.hostUri.split(':').shift();
      return `http://${baseUrl}:3000`;
    }
    return 'http://localhost:3000';
  };

  const [email, setEmail] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleLogin = async () => {
    setIsLoading(true);
    try {
      const response = await fetch(`${getBaseUrl()}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      if (!response.ok) throw new Error(await response.text());
      const responseData = await response.json();
      const { user } = responseData;
      navigation.navigate('Logout', { user });
      Alert.alert('Success', 'Login successful!');
    } catch (error) {
      console.error('Error logging in user', error);
      Alert.alert('Error', 'Invalid credentials. Please try again.');
    } finally {
      setTimeout(() => setIsLoading(false), 3000);
    }
  };

  return (
    <KeyboardAvoidingView
      style={{ flex: 1 }}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      keyboardVerticalOffset={Platform.select({ ios: 80, android: 0 })} // tweak if header overlaps
    >
      <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
        <ImageBackground style={styles.container}>
          <Image source={require('../assets/icon.png')} style={styles.icon} />
          <Text style={styles.eticket}>E-TICKET</Text>

          <View style={styles.innerContainer}>
            <ScrollView
              style={{ width: '100%' }}
              contentContainerStyle={{ alignItems: 'center', paddingBottom: 20 }}
              keyboardShouldPersistTaps="handled"
              showsVerticalScrollIndicator={false}
            >
              <Text style={styles.textLogin}>Login</Text>

              <TextInput
                style={styles.input1}
                placeholder="Email"
                placeholderTextColor="white"
                value={email}
                onChangeText={setEmail}
                returnKeyType="next"
              />

              <View style={styles.passwordContainer}>
                <TextInput
                  style={[styles.input, { flex: 1 }]}
                  placeholder="Password"
                  placeholderTextColor="white"
                  secureTextEntry={!showPassword}
                  value={password}
                  onChangeText={setPassword}
                  returnKeyType="done"
                />
                <TouchableOpacity
                  onPress={() => setShowPassword(!showPassword)}
                  hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
                >
                  <Ionicons
                    name={showPassword ? 'eye-off' : 'eye'}
                    size={22}
                    color="white"
                    style={{ marginRight: 10 }}
                  />
                </TouchableOpacity>
              </View>

              {/* <TouchableOpacity style={styles.forgot} onPress={() => navigation.navigate('Register')}>
                <Text style={styles.registerText}>Forgot Password?</Text>
              </TouchableOpacity> */}

              <TouchableOpacity style={styles.button} onPress={handleLogin}>
                <Text style={styles.buttonText}>Login</Text>
              </TouchableOpacity>

              {isLoading && <ActivityIndicator size="large" color="white" />}
            </ScrollView>
          </View>
        </ImageBackground>
      </TouchableWithoutFeedback>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    resizeMode: 'cover',
    alignItems: 'center',
  },
  passwordContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    width: '85%',
    borderBottomWidth: 1,
    borderBottomColor: 'white',
    marginBottom: 20,
  },
  input1: {
    width: '85%',
    paddingLeft: 5,
    height: 50,
    borderBottomWidth: 1,
    borderBottomColor: '#fff',
    color: '#fff',
    marginBottom: 20,
    fontSize: 14,
  },
  input: {
    color: 'white',
  },
  innerContainer: {
    bottom: 0,
    position: 'absolute',
    width: '100%',
    height: '45%',                 // bottom sheet height; it will shift up with keyboard
    borderTopRightRadius: 40,
    backgroundColor: '#962e2eff',
    borderTopLeftRadius: 40,
    alignItems: 'center',
    paddingTop: 10,                 // a bit of breathing room when pushed up
    paddingBottom: 10,
  },
  textLogin: {
    color: 'white',
    alignSelf: 'flex-start',
    marginLeft: 30,
    marginTop: 30,
    marginBottom: 26,
    fontWeight: 'bold',
    fontSize: 34,
  },
  registerText: {
    marginBottom: 35,
    color: 'white',
    paddingLeft: 5,
  },
  forgot: {
    marginLeft: 30,
    alignSelf: 'flex-start',
  },
  icon: {
    top: 120,
    width: 200,
    height: 190,
    marginBottom: 30,
  },
  eticket: {
    marginBottom: 45,
    color: '#962e2eff',
    top: 120,
    fontWeight: 'bold',
    fontSize: 40,
    fontFamily: undefined,          // ensures fontWeight works on Android
  },
  button: {
    backgroundColor: 'black',
    borderRadius: 20,
    paddingVertical: 10,
    paddingHorizontal: 90,
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
  },
});
