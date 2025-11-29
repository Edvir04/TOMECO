import React, { useState } from 'react';
import { View, Text, StyleSheet, TextInput, Button, Platform, Alert, ImageBackground, ScrollView, TouchableOpacity } from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import Constants from 'expo-constants';

export default function Profile({ route, navigation }) {

  const getBaseUrl = () => {
    if (Constants.expoConfig?.hostUri) {
      const baseUrl = Constants.expoConfig.hostUri.split(':').shift();
      return `http://${baseUrl}:3000`; // Adjust to your backend port
    }
    return 'http://localhost:3000'; // Fallback if no IP detected
  };

  const { user } = route.params;
  const [fullname, setFullname] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [gender, setGender] = useState('');
  const [birthday, setBirthday] = useState(new Date(user.birthdate));
  const [address, setAddress] = useState('');
  const [phone, setPhone] = useState('');
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [selectedDate, setSelectedDate] = useState(new Date(user.birthdate)); // Initialize with user's birthdate


  const deleteAccount = async () => {
    try {
      const response = await fetch(`${getBaseUrl()}/deleteAccount/${user.id}`, {
        method: 'DELETE',
      });
  
      if (response.ok) {
        Alert.alert('Account Deleted', 'Your account has been successfully deleted', [
          {
            text: 'OK',
            onPress: () => navigation.navigate('Login'), // Navigate to login screen
          },
        ]);
      } else {
        Alert.alert('Error', 'Failed to delete account');
      }
    } catch (error) {
      console.error('Error deleting account:', error);
      Alert.alert('Error', 'Failed to delete account');
    }
  };
  
  
  const confirmDelete = () => {
    Alert.alert(
      'Confirm Delete',
      'Are you sure you want to delete this account?',
      [
        {
          text: 'Cancel',
          style: 'cancel',
        },
        {
          text: 'Delete',
          onPress: deleteAccount,
          style: 'destructive',
        },
      ],
      { cancelable: true }
    );
  };

  const handleUpdate = () => {
    // Password validation regex
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    // Check if password is provided and if it meets the criteria
    if (password && !passwordRegex.test(password)) {
        Alert.alert(
            'Invalid Password',
            'Password must contain at least 8 characters, including one letter, one number, one symbol, one uppercase letter, and one lowercase letter.'
        );
        return;
    }

    // Add 1 day to the selected date
    const adjustedDate = new Date(selectedDate);
    adjustedDate.setDate(adjustedDate.getDate() + 1);

    // Construct the updated user object
    let updatedUser = {
        ...user,
        username: fullname || user.username,
        email: email || user.email,
        gender: gender || user.gender,
        address: address || user.address,
        phone: phone || user.phone,
        birthdate: adjustedDate.toISOString(), // Use adjustedDate instead of selectedDate
    };

    // Update password only if provided
    if (password) {
        updatedUser.password = password;
    }

    // Send a request to your backend API to update the user
    fetch(`${getBaseUrl()}/updateProfile`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(updatedUser),
        })
        .then((response) => {
            if (response.ok) {
                // Handle success
                Alert.alert(
                    'Success Updated',
                    'Profile updated successfully, it will redirect you to the Log-in page. Thank You!', [{
                        text: 'OK',
                        onPress: () => {
                            // Refresh the profile tab
                            // navigation.navigate('Profile', { user: updatedUser });
                            navigation.navigate('Login');
                        },
                    }, ],
                );
            } else {
                // Handle failure
                Alert.alert('Error', 'Failed to update profile');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            Alert.alert('Error', 'Failed to update profile');
        });
};


  

  const handleDateChange = (event, selectedDate) => {
    if (event.type === 'dismissed') {
      setShowDatePicker(false);
    } else {
      setShowDatePicker(Platform.OS === 'ios');
      const currentDate = selectedDate || birthday;
      setSelectedDate(currentDate); // Update selectedDate with the new date or keep it as the current birthday
      setBirthday(currentDate); // Update birthday state for displaying purpose
    }
  };

  const genderOptions = ['Male', 'Female', 'Other'];


  return (
    <>
    <ImageBackground source={require('../assets/background1.png')} style={styles.background}>
      <ScrollView contentContainerStyle={styles.container}>
        
      <Text style={styles.title}>
        Welcome to Profile{'\n'}
        <Text style={{ color: 'darkred' }}>{user.username}</Text>
      </Text>

        <Text style={styles.label}>Full Name: </Text>
        <TextInput
          editable={true}
          placeholder={user.username}
          placeholderTextColor={'black'}
          value={fullname}
          onChangeText={setFullname}
          style={styles.input}
          required={true}
        />

        <Text style={styles.label}>Email: </Text>
        <TextInput
          editable={true}
          placeholder={user.email}
          placeholderTextColor={'black'}
          value={email}
          onChangeText={setEmail}
          style={styles.input}
          required={true}
        />

        <Text style={styles.label}>Password: </Text>
        <TextInput
          editable={true}
          placeholder={user.password}
          placeholderTextColor={'black'}
          secureTextEntry
          value={password}
          onChangeText={setPassword}
          style={styles.input}
          required={true}
        />

        <Text style={styles.label}>Gender: </Text>
        <TextInput
          placeholder={user.gender}
          placeholderTextColor={'black'}
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

        <Text style={styles.label}>Date of Birth: <Text style={{fontWeight: 'bold'}}></Text></Text>
        <TextInput
          value={showDatePicker ? birthday.toLocaleDateString([], { year: 'numeric', month: 'long', day: 'numeric' }) : selectedDate.toLocaleDateString([], { year: 'numeric', month: 'long', day: 'numeric' })}
          style={styles.input}
          onFocus={() => setShowDatePicker(true)}
          required={true}
          editable={true}
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

        <Text style={styles.label}>Full Address: </Text>
        <TextInput
          editable={true}
          placeholder={user.address}
          placeholderTextColor={'black'}
          value={address}
          onChangeText={setAddress}
          style={styles.input}
          required={true}
        />

        <Text style={styles.label}>Phone: </Text>
        <TextInput
          editable={true}
          placeholder={user.phone}
          placeholderTextColor={'black'}
          value={phone}
          onChangeText={setPhone}
          style={styles.input}
          maxLength={13}
          keyboardType="numeric"
          required={true}
        />

        <View style={styles.buttonContainer}>
          <TouchableOpacity style={[styles.button, styles.updateButton]} onPress={handleUpdate}>
            <Text style={styles.buttonText}>Update</Text>
          </TouchableOpacity>
          <TouchableOpacity style={[styles.button, styles.deleteButton]} onPress={confirmDelete}>
            <Text style={styles.buttonText}>Delete Account</Text>
          </TouchableOpacity>
        </View>




      </ScrollView>
      </ImageBackground>
    </>
  );
}

const styles = StyleSheet.create({
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
  label: {
    color: 'white',
    fontWeight: 'bold',
    fontSize: 13,
    marginBottom: 3,
  },
  buttonContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  button: {
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
    margin: 10,
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
    textAlign: 'center',
  },
  updateButton: {
    backgroundColor: 'darkred', 
    borderRadius: 20,
    paddingVertical: 10, // Adjust the padding to make the button smaller
    paddingHorizontal: 20, // Adjust the padding to make the button smaller
    marginBottom: 10,
  },
  deleteButton: {
    backgroundColor: 'red',
    borderWidth: 1,
    borderRadius: 20,
    paddingVertical: 10, // Adjust the padding to make the button smaller
    paddingHorizontal: 20, // Adjust the padding to make the button smaller
    marginBottom: 10,
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
    justifyContent: 'center',
    color: 'darkred',
    alignItems: 'center',
    textAlign: 'center', // Add this line to center the text
    fontWeight: 'bold', // Add this line to make the text bold
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
  loginText: {
    marginTop: 20,
    color: 'blue',
  },
});
