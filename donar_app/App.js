// App.js
import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import LoginScreen from './auth/LoginScreen';
import RegisterScreen from './auth/RegisterScreen';
import MainScreen from './auth/MainScreen';
import TicketIssuedOwnCard from './cards/TicketIssuedOwnCard'; 

const Stack = createStackNavigator();

export default function App() {
  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName="Login" screenOptions={{ headerTitleAlign: 'center' }}>
        <Stack.Screen
          name="Login"
          component={LoginScreen}
          options={{ headerShown: false }}
        />
        <Stack.Screen
          name="Register"
          component={RegisterScreen}
          options={{
            headerLeft: () => null,
            headerStyle: { backgroundColor: 'darkred' },
            headerTintColor: 'white',
            title: 'Register',
          }}
        />
        <Stack.Screen
          name="Logout"
          component={MainScreen}
          options={{ headerShown: false }}
        />

        {/* New screen where the yellow box goes */}
        <Stack.Screen
          name="TicketHistory"
          component={TicketIssuedOwnCard}
          options={{
            title: 'Ticket History',
            headerStyle: { backgroundColor: '#962e2eff' },
            headerTintColor: 'white',
          }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}
