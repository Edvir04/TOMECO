import React, { useState, useEffect } from 'react';
import { View, Text, Modal, StyleSheet, TouchableOpacity, ScrollView  } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import BloodRequestCardOwn from '../cards/TicketIssuedOwnCard'; // Import BloodRequestCard component
import axios from 'axios'; // Import axios for making HTTP requests
import Constants from 'expo-constants';

const ViewModal = ({ visible, closeModal, user }) => {

  const getBaseUrl = () => {
    if (Constants.expoConfig?.hostUri) {
      const baseUrl = Constants.expoConfig.hostUri.split(':').shift();
      return `http://${baseUrl}:3000`; // Adjust to your backend port
    }
    return 'http://localhost:3000'; // Fallback if no IP detected
  };

  const [bloodRequests, setBloodRequests] = useState([]);

  useEffect(() => {
    const fetchBloodRequests = async () => {
      try {
        const response = await axios.get( `${getBaseUrl()}/ticket_issued_own` );
        const filteredData = response.data.filter(request => request.user_id === user.id);
        setBloodRequests(filteredData);
      } catch (error) {
        console.error('Error fetching data:', error);
      }
    };
  
    fetchBloodRequests();
  }, []);
  

 
return (
    <Modal transparent={true} visible={visible} animationType="slide">
      <View style={styles.modalBackground}>
        <ScrollView contentContainerStyle={styles.scrollViewContent}>
          <View style={styles.modalContent}>
            <TouchableOpacity onPress={closeModal} style={styles.closeButton}>
              <Ionicons name="close" size={24} color="black" />
            </TouchableOpacity>
            <View style={styles.modalTextContainer}>
              <Text style={styles.title}>
                Welcome to your Ticket Issued{'\n'}
                {/* <Text style={{ color: 'lightblue' }}>{user.username}</Text> */}
              </Text>
              {bloodRequests.map((request) => (
                <BloodRequestCardOwn key={request.id} request={request} />
              ))}
            </View>
          </View>
        </ScrollView>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  title: {
    fontSize: 18,
    textAlign: 'center',
    fontWeight: 'bold',
    color: 'pink',
  },
  modalBackground: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    padding: 10,
  },
  modalContent: {
    backgroundColor: 'darkred',
    borderRadius: 10,
    padding: 20,
    width: '100%',
    position: 'relative',
  },
  closeButton: {
    position: 'absolute',
    top: 10,
    right: 10,
    padding: 5,
    zIndex: 1,
  },
  modalTextContainer: {
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 20,
  },
});

export default ViewModal;
