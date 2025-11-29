import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import HomeScreen from '../bottomTabs/HomeScreen';
import TicketIssuance from '../bottomTabs/TicketIssuance';
import Notification from '../bottomTabs/Notification';
import Event from '../bottomTabs/Event';
import Profile from '../bottomTabs/Profile';
import { Alert, View } from 'react-native';

const Tab = createBottomTabNavigator();

const MainScreen = ({ route }) => {
    const { user } = route.params;
    const navigation = useNavigation();

    // const handleLogout = () => {
    //     Alert.alert(
    //         'Logout',
    //         'Are you sure you want to logout?',
    //         [
    //             {
    //                 text: 'Cancel',
    //                 style: 'cancel',
    //             },
    //             {
    //                 text: 'Logout',
    //                 onPress: () => {
    //                     navigation.navigate('Login');
    //                 },
    //             },
    //         ],
    //         { cancelable: false }
    //     );
    // };

    return (
      <Tab.Navigator
        screenOptions={{
            headerStyle: { backgroundColor: '#962e2eff' },
            headerTintColor: 'white',
            tabBarShowLabel: false, // hide labels globally
            tabBarActiveTintColor: 'black',
            tabBarInactiveTintColor: 'white',
            tabBarItemStyle: { justifyContent: 'center', alignItems: 'center' }, // center each tab
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
            height: 72,          // more room for bigger icons
            paddingTop: 8,
            paddingBottom: 12,
            },
        }}
        >


            <Tab.Screen
                name=" "
                component={HomeScreen}
                options={{
                    headerShown: false,
                    tabBarIcon: ({ focused }) => (
                    <View style={{ justifyContent: 'center', alignItems: 'center' }}>
                        <Ionicons name="home" size={32} color={focused ? 'black' : 'white'} />
                    </View>
                    ),
                    // headerRight: () => (
                    // <Ionicons
                    //     style={{ marginRight: 20 }}
                    //     name="log-out"
                    //     size={24}
                    //     color="white"
                    //     onPress={handleLogout}
                    // />
                    // ),
                }}
                initialParams={{ user }}
                />

                <Tab.Screen
                name="Ticket Issuance"
                component={TicketIssuance}
                options={{
                    tabBarIcon: ({ focused }) => (
                    <View style={{ justifyContent: 'center', alignItems: 'center' }}>
                        <Ionicons name="receipt" size={32} color={focused ? 'black' : 'white'} />
                    </View>
                    ),
                }}
                initialParams={{ user }}
                />
           
            {/* <Tab.Screen
                name="Event"
                component={Event}
                options={{
                    tabBarLabel: 'Events',
                    tabBarIcon: ({ focused, size }) => (
                        <Ionicons name="calendar" color={focused ? 'red' : 'white'} size={size} />
                    ),
                    tabBarLabelStyle: {
                        color: 'white'
                    }
                }}
                initialParams={{ user }}
            />

            <Tab.Screen
                name="Notification"
                component={Notification}
                options={{
                    tabBarLabel: 'Notification',
                    tabBarIcon: ({ focused, size }) => (
                        <Ionicons name="notifications" color={focused ? 'red' : 'white'} size={size} />
                    ),
                    tabBarLabelStyle: {
                        color: 'white'
                    }
                }}
                initialParams={{ user }}
            /> */}

           <Tab.Screen
                name="Profile"
                component={Profile}
                options={{
                    tabBarIcon: ({ focused }) => (
                    <View style={{ justifyContent: 'center', alignItems: 'center' }}>
                        <Ionicons name="person" size={32} color={focused ? 'black' : 'white'} />
                    </View>
                    ),
                }}
                initialParams={{ user }}
            />
        </Tab.Navigator>
    );
};

export default MainScreen;
