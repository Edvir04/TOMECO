import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { MaterialIcons } from '@expo/vector-icons';
import { View } from 'react-native';
import Dashboard from './Dashboard';
import Ticket from './Ticket';
import Profile from './Profile';

const Tab = createBottomTabNavigator();

const MainScreen = ({ route }) => {
    const { enforcer } = route.params || {};

    return (
        <Tab.Navigator
            screenOptions={{
                headerStyle: { backgroundColor: '#962e2eff' },
                headerTintColor: 'white',
                tabBarShowLabel: false,
                tabBarActiveTintColor: 'black',
                tabBarInactiveTintColor: 'white',
                tabBarItemStyle: { justifyContent: 'center', alignItems: 'center' },
                tabBarStyle: {
                    backgroundColor: '#962e2eff',
                    borderTopLeftRadius: 40,
                    borderTopRightRadius: 40,
                    overflow: 'hidden',
                    position: 'absolute',
                    left: 0, right: 0, bottom: 0,
                    borderTopWidth: 0,
                    elevation: 0,
                    shadowColor: 'transparent',
                    height: 72,
                    paddingTop: 8,
                    paddingBottom: 12,
                },
            }}
        >
            <Tab.Screen
                name="Dashboard"
                component={Dashboard}
                options={{
                    headerShown: false,
                    tabBarIcon: ({ focused }) => (
                        <View style={{ justifyContent: 'center', alignItems: 'center' }}>
                            <MaterialIcons 
                                name="dashboard" 
                                size={32} 
                                color={focused ? 'black' : 'white'} 
                            />
                        </View>
                    ),
                }}
                initialParams={{ enforcer }}
            />

            <Tab.Screen
                name="Ticket"
                component={Ticket}
                options={{
                    headerTitle: 'Ticket Issuance',
                    headerShown: true,
                    tabBarIcon: ({ focused }) => (
                        <View style={{ justifyContent: 'center', alignItems: 'center' }}>
                            <MaterialIcons 
                                name="receipt" 
                                size={32} 
                                color={focused ? 'black' : 'white'} 
                            />
                        </View>
                    ),
                }}
                initialParams={{ enforcer }}
            />

            <Tab.Screen
                name="Profile"
                component={Profile}
                options={{
                    headerTitle: 'Profile',
                    headerShown: true,
                    tabBarIcon: ({ focused }) => (
                        <View style={{ justifyContent: 'center', alignItems: 'center' }}>
                            <MaterialIcons 
                                name="person" 
                                size={32} 
                                color={focused ? 'black' : 'white'} 
                            />
                        </View>
                    ),
                }}
                initialParams={{ enforcer }}
            />
        </Tab.Navigator>
    );
};

export default MainScreen;

