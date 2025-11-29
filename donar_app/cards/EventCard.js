import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

const EventCard = ({ request }) => {
  // Function to format the date
  const formatDate = (dateString, includeTime = false) => {
    const date = new Date(dateString);
    if (includeTime) {
      const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true, // Use 12-hour time format
      };
      return date.toLocaleDateString('en-US', options);
    } else {
      const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      };
      return date.toLocaleDateString('en-US', options);
    }
  };

  return (
    <View style={styles.card}>
      <View style={styles.rowContainer}>
        <Text style={styles.label}>Event Name:</Text>
        <Text style={styles.value}>{request.event_name}</Text>
        <Text style={styles.label}>ID:</Text>
        <Text style={styles.value}>{request.id}</Text>
      </View>
      <View style={styles.line} />
        <Text style={styles.cardText}>Description: {request.description}</Text>
        <Text style={styles.cardText}>Location: {request.address}</Text>
        {/* <Text style={styles.cardText}>Contact: {request.phone}</Text> */}
        <Text style={styles.cardText}>Date of Event: {formatDate(request.date_of_event)}</Text>
        <View style={styles.line} />
        <Text>Date Uploaded: {formatDate(request.created_at, true)}</Text>
    </View>
  );
};


const styles = StyleSheet.create({
    card: {
      backgroundColor: 'pink',
      padding: 15,
      marginTop: 10,
      marginBottom: 10,
      marginRight: 17,
      marginLeft: 17,    
      borderRadius: 10,
      elevation: 2,
    },
    rowContainer: {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
      marginBottom: 5,
    },
    label: {
      fontSize: 16,
      fontWeight: 'bold',
      marginRight: 5,
    },
    value: {
      fontSize: 16,
    },
    line: {
      borderBottomColor: 'gray',
      borderBottomWidth: 1,
      marginVertical: 10,
    },
    cardText: {
      fontSize: 16,
      marginBottom: 5,
    },
  });

export default EventCard;
