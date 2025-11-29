import React, { useState } from 'react';
import { View, Text, StyleSheet, TextInput, Button, Platform, Alert, ScrollView,TouchableOpacity, ImageBackground } from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import Constants from 'expo-constants';


export default function RegisterScreen({ navigation }) {

  const getBaseUrl = () => {
    if (Constants.expoConfig?.hostUri) {
      const baseUrl = Constants.expoConfig.hostUri.split(':').shift();
      return `http://${baseUrl}:3000`; // Adjust to your backend port
    }
    return 'http://localhost:3000'; // Fallback if no IP detected
  };
  
  const [fullname, setFullname] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [gender, setGender] = useState('');
  const [birthday, setBirthday] = useState(new Date());
  const [address, setAddress] = useState('');
  const [phone, setPhone] = useState('');
  const [showDatePicker, setShowDatePicker] = useState(false);

  const handleDateChange = (event, selectedDate) => {
    if (event.type === 'dismissed') {
      setShowDatePicker(false);
      return;
    }
    const currentDate = selectedDate || birthday;
    setShowDatePicker(Platform.OS === 'ios');
    setBirthday(currentDate);
  };

  const isPasswordValid = (password) => {
    // Password must be at least 8 characters (any characters allowed)
    const passwordRegex = /^.{8,}$/;
    return passwordRegex.test(password);
  };

    
  // Frontend (React Native)
  const handleRegister = async () => {
    // Check if any of the required fields are empty
    if (!fullname || !email || !password || !gender || !birthday || !address || !phone) {
      Alert.alert(
        'Error',
        'Please fill in all required fields.',
        [{ text: 'OK', onPress: () => console.log('OK Pressed') }],
        { cancelable: false }
      );
      return;
    }

    // Check if password meets complexity requirements
    if (!isPasswordValid(password)) {
      Alert.alert(
        'Error',
        'Password must contain at least 8 characters, one letter, one number, one symbol, one uppercase letter, and one lowercase letter.',
        [{ text: 'OK', onPress: () => console.log('OK Pressed') }],
        { cancelable: false }
      );
      return;
    }

    try {
      const userData = {
        username: fullname,
        email,
        password,
        gender,
        birthdate: birthday,
        address,
        phone,
      };
      const response = await fetch(`${getBaseUrl()}/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData),
      });
      if (response.ok) {
        Alert.alert(
          'Success',
          'Registration successful',
          [
            {
              text: 'OK',
              onPress: () => {
                // Clear input fields
                setFullname('');
                setEmail('');
                setPassword('');
                setGender('');
                setBirthday(new Date());
                setAddress('');
                setPhone('');
                // Navigate to login screen
                navigation.navigate('Login');
              },
            },
          ],
          { cancelable: false }
        );
      } else {
        console.error('Registration failed');
        // Handle registration error
      }
    } catch (error) {
      console.error('Registration failed', error);
      // Handle registration error
    }
  };

  const genderOptions = ['Male', 'Female', 'Other'];


  return (
    <ImageBackground source={require('../assets/background.png')} style={styles.background}>
      <ScrollView contentContainerStyle={styles.container}>
        <Text style={styles.label}>Full Name: </Text>
        <TextInput
          placeholder="Family Name, First Name, Middle Name"
          value={fullname}
          onChangeText={setFullname}
          style={styles.input}
          required={true}
        />

        <Text style={styles.label}>Email: </Text>
        <TextInput
          placeholder="Email"
          value={email}
          onChangeText={setEmail}
          style={styles.input}
          required={true}
        />

        <Text style={styles.label}>Password: </Text>
        <TextInput
          placeholder="Password"
          secureTextEntry
          value={password}
          onChangeText={setPassword}
          style={styles.input}
          required={true}
        />

        <Text style={styles.label}>Gender: </Text>
        <TextInput
          placeholder="Select Gender"
          editable={true}
          value={gender}
          style={styles.input}
          required={true}
        />
      <View style={styles.dropdown}>
        {genderOptions.map((option, index) => (
          <TouchableOpacity
            key={index}
            style={[styles.genderButton, gender === option && styles.selectedGenderButton]}
            onPress={() => setGender(option)}
          >
            <Text style={styles.buttonText}>{option}</Text>
          </TouchableOpacity>
        ))}
      </View>

        <Text style={styles.label}>Date of Birth: </Text>
        <TextInput
          placeholder="Date of Birth"
          value={birthday.toLocaleDateString([], { year: 'numeric', month: 'long', day: 'numeric' })}
          style={styles.input}
          onFocus={() => setShowDatePicker(true)} 
          required={true}
        />

        <Text style={styles.label}>Full Address: </Text>
        <TextInput
          placeholder="Barangay 83-A (San Jose)"
          value={address}
          onChangeText={setAddress}
          style={styles.input}
          required={true}
        />

        <Text style={styles.label}>Phone: </Text>
        <TextInput
          placeholder="+63912 345 6789"
          value={phone}
          onChangeText={setPhone}
          style={styles.input}
          maxLength={13}
          keyboardType="numeric"
          required={true}
        />

        {showDatePicker && (
          <DateTimePicker
            testID="dateTimePicker"
            value={birthday}
            mode="date"
            display="default"
            onChange={handleDateChange}
          />
        )}

        <TouchableOpacity style={styles.button} onPress={handleRegister}>
          <Text style={styles.buttonText}>Register</Text>
        </TouchableOpacity>
        <TouchableOpacity onPress={() => navigation.navigate('Login')}>
          <Text style={styles.loginText}>Already have an account? Login here</Text>
        </TouchableOpacity>
      </ScrollView>
    </ImageBackground>
  );
}


const styles = StyleSheet.create({
  label: {
    color: 'white',
    fontWeight: 'bold',
    fontSize: 13,
    marginBottom: 3,
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
  },
  button: {
    backgroundColor: 'darkred',
    borderRadius: 20,
    paddingVertical: 10, // Adjust the padding to make the button smaller
    paddingHorizontal: 20, // Adjust the padding to make the button smaller
    marginBottom: 10,
  },
  background: {
    flex: 1,
    resizeMode: 'cover',
    justifyContent: 'center',
  },
  container: {
    flexGrow: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingBottom: 20,
    paddingTop: 20,
  },
  title: {
    fontSize: 24,
    marginBottom: 20,
  },
  input: {
    borderRadius: 20,
    height: 40,
    width: '85%',
    padding: 10,
    borderColor: 'gray',
    backgroundColor: 'white',
    marginBottom: 20,
    paddingHorizontal: 10,
  },
  dropdown: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    width: '80%',
    marginBottom: 20,
  },
  genderButton: {
    backgroundColor: 'darkred',
    borderRadius: 20,
    paddingVertical: 10,
    paddingHorizontal: 20,
    marginHorizontal: 5,
  },
  selectedGenderButton: {
    backgroundColor: 'red',
  },
  loginText: {
    marginTop: 20,
    color: 'white',
  },
});