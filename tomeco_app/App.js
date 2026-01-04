import React, { useEffect } from "react";
import { NavigationContainer } from "@react-navigation/native";
import { createStackNavigator } from "@react-navigation/stack";
import LoginScreen from "./Section/LoginScreen"; // import the login screen
import MainScreen from "./Section/MainScreen"; // import the main screen with bottom tabs
import IssueTicket from "./Section/IssueTicket"; // import the issue ticket screen
import OCRScan from "./Section/OCRScan"; // import the OCR scan screen
import TicketHistory from "./Section/TicketHistory"; // import the ticket history screen
import { setupAutoSync } from "./services/SyncService";

const Stack = createStackNavigator();

export default function App() {
  useEffect(() => {
    // Setup auto-sync when app starts
    setupAutoSync();
  }, []);

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        <Stack.Screen name="Login" component={LoginScreen} />
        <Stack.Screen name="MainScreen" component={MainScreen} />
        <Stack.Screen name="IssueTicket" component={IssueTicket} />
        <Stack.Screen name="OCRScan" component={OCRScan} />
        <Stack.Screen name="TicketHistory" component={TicketHistory} />
      </Stack.Navigator>
    </NavigationContainer>
  );
}
