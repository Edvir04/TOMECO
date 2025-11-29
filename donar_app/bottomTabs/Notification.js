import React from 'react';
import { View, Text, StyleSheet, ScrollView, ImageBackground } from 'react-native';

const Notification = ({ route }) => {
  const { user } = route.params;

  return (
    <>
      <ImageBackground source={require('../assets/background1.png')} style={styles.background}>
        <ScrollView contentContainerStyle={styles.container}>
          <View style={styles.userInfo}>
            <Text style={styles.infoText}>User ID: {user.id}</Text>
            <Text style={styles.infoText}>Username: {user.username}</Text>
          </View>
        </ScrollView>
      </ImageBackground>
    </>
  );
};

const styles = StyleSheet.create({
  container: {
    flexGrow: 1,
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  userInfo: {
    marginTop: 20,
    alignItems: 'center',
  },
  infoText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: 'white',
    marginBottom: 10,
  },
  background: {
    flex: 1,
    resizeMode: 'cover',
    justifyContent: 'center',
  },
});

export default Notification;
