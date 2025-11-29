import React, { useEffect, useState } from 'react';
import { View, StyleSheet, ScrollView, ImageBackground, ActivityIndicator } from 'react-native';
import EventCard from '../cards/EventCard'; 
import Constants from 'expo-constants';

const Event = ({ route }) => {

  const getBaseUrl = () => {
    if (Constants.expoConfig?.hostUri) {
      const baseUrl = Constants.expoConfig.hostUri.split(':').shift();
      return `http://${baseUrl}:3000`; // Adjust to your backend port
    }
    return 'http://localhost:3000'; // Fallback if no IP detected
  };

  const { user } = route.params;
  const [events, setEvents] = useState([]);
  const [isLoading, setIsLoading] = useState(true); // State variable for loading indicator

  useEffect(() => {
    fetchEvents();
  }, []);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoading(false); // Hide loading indicator after 3 seconds
    }, 1000);

    return () => clearTimeout(timer);
  }, []);

  const fetchEvents = async () => {
    try {
      const response = await fetch(`${getBaseUrl()}/events`);
      if (response.ok) {
        const data = await response.json();
        setEvents(data);
      } else {
        console.error('Failed to fetch events');
      }
    } catch (error) {
      console.error('Error fetching events:', error);
    }
  };

  return (
    <ImageBackground source={require('../assets/background1.png')} style={styles.background}>
      {isLoading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="darkred" />
        </View>
      ) : (
        <ScrollView>
          <View>
            {events.map((request) => (
              <EventCard key={request.id} request={request} />
            ))}
          </View>
        </ScrollView>
      )}
    </ImageBackground>
  );
};


const styles = StyleSheet.create({
  background: {
    flex: 1,
    resizeMode: 'cover',
    width: '100%',
    height: '100%',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
});

export default Event;
