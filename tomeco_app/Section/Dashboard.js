// Dashboard.js
import React, { useState, useEffect } from "react";
import { 
  View, Text, StyleSheet, ScrollView, TouchableOpacity, 
  Image, Dimensions, ActivityIndicator, RefreshControl, Alert
} from "react-native";
import { MaterialIcons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { CommonActions } from '@react-navigation/native';
import API from "../config/api";
import SyncIndicator from "../components/SyncIndicator";

export default function Dashboard({ navigation, route }) {
  const { enforcer } = route.params || {};
  const [enforcerData, setEnforcerData] = useState(enforcer || null);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [stats, setStats] = useState({
    todayTickets: 0,
    weekTickets: 0,
    monthTickets: 0,
    totalTickets: 0,
  });

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      // Use route params if available, otherwise load from AsyncStorage
      if (enforcer) {
        setEnforcerData(enforcer);
      } else {
        // Load enforcer data from AsyncStorage
        const storedData = await AsyncStorage.getItem('enforcer_data');
        if (storedData) {
          const enforcer = JSON.parse(storedData);
          setEnforcerData(enforcer);
        }
      }
      
      const token = await AsyncStorage.getItem('auth_token');

      // Fetch profile data from API
      if (token) {
        await fetchProfile(token);
        await fetchStats(token);
      }
    } catch (error) {
      console.error('Error loading dashboard data:', error);
      Alert.alert("Error", "Failed to load dashboard data");
    } finally {
      setIsLoading(false);
    }
  };

  const fetchProfile = async (token) => {
    try {
      const response = await fetch(API.PROFILE, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data) {
          setEnforcerData(data.data);
          await AsyncStorage.setItem('enforcer_data', JSON.stringify(data.data));
        }
      }
    } catch (error) {
      console.error('Error fetching profile:', error);
    }
  };

  const fetchStats = async (token) => {
    try {
      // Fetch tickets from API
      const response = await fetch(API.TICKETS.LIST, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error(`Failed to fetch tickets: ${response.status}`);
      }

      const data = await response.json();
      const tickets = data.data || data.tickets || [];

      // Get current date boundaries (set to start of day for accurate comparison)
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      today.setHours(0, 0, 0, 0);
      
      const weekStart = new Date(today);
      weekStart.setDate(today.getDate() - today.getDay()); // Start of week (Sunday)
      weekStart.setHours(0, 0, 0, 0);
      
      const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
      monthStart.setHours(0, 0, 0, 0);

      // Calculate statistics
      let todayCount = 0;
      let weekCount = 0;
      let monthCount = 0;
      let totalCount = tickets.length;

      tickets.forEach(ticket => {
        if (!ticket.issued_date) return;

        // Parse the issued_date (could be a date string or Date object)
        const ticketDate = new Date(ticket.issued_date);
        ticketDate.setHours(0, 0, 0, 0); // Normalize to start of day
        
        // Today - tickets issued today
        if (ticketDate.getTime() === today.getTime()) {
          todayCount++;
        }

        // This week - tickets issued from start of week to today
        if (ticketDate >= weekStart && ticketDate <= today) {
          weekCount++;
        }

        // This month - tickets issued from start of month to today
        if (ticketDate >= monthStart && ticketDate <= today) {
          monthCount++;
        }
      });

      setStats({
        todayTickets: todayCount,
        weekTickets: weekCount,
        monthTickets: monthCount,
        totalTickets: totalCount,
      });
    } catch (error) {
      console.error('Error fetching stats:', error);
      // Set default values on error
      setStats({
        todayTickets: 0,
        weekTickets: 0,
        monthTickets: 0,
        totalTickets: 0,
      });
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadDashboardData();
    setRefreshing(false);
  };

  const handleLogout = async () => {
    Alert.alert(
      "Logout",
      "Are you sure you want to logout?",
      [
        { text: "Cancel", style: "cancel" },
        {
          text: "Logout",
          style: "destructive",
          onPress: async () => {
            try {
              const token = await AsyncStorage.getItem('auth_token');
              
              // Call logout API if available
              if (token && API.LOGOUT) {
                try {
                  await fetch(API.LOGOUT, {
                    method: 'POST',
                    headers: {
                      'Authorization': `Bearer ${token}`,
                      'Accept': 'application/json',
                    },
                  });
                } catch (error) {
                  console.error('Logout API error:', error);
                }
              }

              // Clear stored data
              await AsyncStorage.removeItem('auth_token');
              await AsyncStorage.removeItem('enforcer_data');
              
              // Navigate back to login using CommonActions
              navigation.dispatch(
                CommonActions.reset({
                  index: 0,
                  routes: [{ name: 'Login' }],
                })
              );
            } catch (error) {
              console.error('Error during logout:', error);
              Alert.alert("Error", "Failed to logout");
            }
          },
        },
      ]
    );
  };

  const handleIssueTicket = () => {
    // Navigate to Ticket tab
    navigation.navigate('Ticket');
  };

  const handleViewHistory = () => {
    // Navigate to Ticket tab (where history can be viewed)
    navigation.navigate('Ticket');
  };

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#962e2eff" />
        <Text style={styles.loadingText}>Loading dashboard...</Text>
      </View>
    );
  }

  const todayStr = new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });

  return (
    <View style={styles.container}>
      {/* Header - Large section taking up ~60% of screen */}
      <View style={styles.header}>
        <View style={styles.headerTop}>
          <View>
            <Text style={styles.greeting}>Welcome back,</Text>
            <Text style={styles.name}>{enforcerData?.fullname || enforcerData?.username || 'Enforcer'}</Text>
          </View>
        </View>
        <Text style={styles.date}>{todayStr}</Text>
      </View>

      {/* Logo - Positioned to overlap both sections with circular container */}
      <View style={styles.logoSection}>
        <View style={styles.logoContainer}>
          <Image 
            source={require("../assets/Tomeco_Square.png")} 
            style={styles.logo} 
          />
        </View>
      </View>

      {/* Main Content Area */}
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor="#962e2eff"
            colors={["#962e2eff"]}
          />
        }
      >
        {/* Sync Indicator */}
        <SyncIndicator />
        
        {/* Quick Actions */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Quick Actions</Text>
          <View style={styles.actionsRow}>
            <TouchableOpacity 
              style={[styles.actionCard, styles.primaryAction]}
              onPress={handleIssueTicket}
              activeOpacity={0.8}
            >
              <MaterialIcons name="receipt" size={32} color="#fff" />
              <Text style={styles.actionText}>Issue Ticket</Text>
            </TouchableOpacity>

            <TouchableOpacity 
              style={[styles.actionCard, styles.secondaryAction]}
              onPress={handleViewHistory}
              activeOpacity={0.8}
            >
              <MaterialIcons name="history" size={32} color="#fff" />
              <Text style={styles.actionText}>View History</Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Statistics */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Statistics</Text>
          <View style={styles.statsGrid}>
            <View style={styles.statCard}>
              <MaterialIcons name="today" size={28} color="#962e2eff" />
              <Text style={styles.statValue}>{stats.todayTickets}</Text>
              <Text style={styles.statLabel}>Today</Text>
            </View>

            <View style={styles.statCard}>
              <MaterialIcons name="date-range" size={28} color="#962e2eff" />
              <Text style={styles.statValue}>{stats.weekTickets}</Text>
              <Text style={styles.statLabel}>This Week</Text>
            </View>

            <View style={styles.statCard}>
              <MaterialIcons name="calendar-month" size={28} color="#962e2eff" />
              <Text style={styles.statValue}>{stats.monthTickets}</Text>
              <Text style={styles.statLabel}>This Month</Text>
            </View>

            <View style={styles.statCard}>
              <MaterialIcons name="assessment" size={28} color="#962e2eff" />
              <Text style={styles.statValue}>{stats.totalTickets}</Text>
              <Text style={styles.statLabel}>Total</Text>
            </View>
          </View>
        </View>

        {/* Mission and Vision */}
        <View style={styles.section}>
          <Text style={styles.missionVisionTitle}>MISSION AND VISSION</Text>
          
          <View style={styles.missionVisionCard}>
            <Text style={styles.missionVisionSubtitle}>MISSION</Text>
            <Text style={styles.missionVisionText}>
              A GLOBALLY COMPETITIVE, GREEN AND RESILIENT CITY, PROPELLED BY GOD-LOVING, GENDER RESPONSIVE LEADERS AND EMPOWERED CITIZENRY.
            </Text>
          </View>

          <View style={styles.missionVisionCard}>
            <Text style={styles.missionVisionSubtitle}>VISSION</Text>
            <Text style={styles.missionVisionText}>
              "TO BE AN AGRI-INDUSTRIAL PARK AND STRATEGIC HUB FOR EDUCATION EXCELLENCE IN EASTERN VISAYAS; TO ACHIEVE COMPETENT HUMAN CAPITAL IN A SECURED, WELL BALANCED ENVIRONMENT;"
            </Text>
          </View>
        </View>
      </ScrollView>
    </View>
  );
}

const { width, height } = Dimensions.get("window");
const headerHeight = height * 0.28; // 25-30% of screen
const logoTopPosition = headerHeight - 60; // Adjusted for smaller logo

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "transparent",
  },
  loadingContainer: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: "#faf8f5",
  },
  loadingText: {
    marginTop: 10,
    color: "#666",
    fontSize: 16,
  },
  header: {
    backgroundColor: "#962e2eff",
    paddingTop: 50,
    paddingBottom: 20,
    paddingHorizontal: 20,
    height: headerHeight,
    justifyContent: "flex-start",
    borderBottomLeftRadius: 25,
    borderBottomRightRadius: 25,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 5,
  },
  headerTop: {
    flexDirection: "row",
    justifyContent: "flex-start",
    alignItems: "center",
    marginBottom: 10,
  },
  greeting: {
    color: "#fff",
    fontSize: 16,
    opacity: 0.9,
  },
  name: {
    color: "#fff",
    fontSize: 24,
    fontWeight: "bold",
    marginTop: 4,
  },
  date: {
    color: "#fff",
    fontSize: 14,
    opacity: 0.9,
  },
  logoSection: {
    position: "absolute",
    top: logoTopPosition,
    left: 0,
    right: 0,
    alignItems: "center",
    justifyContent: "center",
    zIndex: 3,
  },
  logoContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: "#fff",
    borderWidth: 3,
    borderColor: "#2196F3",
    alignItems: "center",
    justifyContent: "center",
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 5,
    padding: 2,
    overflow: "hidden",
  },
  logo: {
    width: 94,
    height: 94,
    resizeMode: "cover",
  },
  scrollView: {
    flex: 1,
    backgroundColor: "transparent",
    marginTop: 0,
  },
  scrollContent: {
    padding: 20,
    paddingTop: 70,
    paddingBottom: 100, // Extra padding for bottom tabs (72px + safe margin)
    backgroundColor: "#faf8f5",
  },
  section: {
    marginBottom: 25,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: "bold",
    color: "#333",
    marginBottom: 15,
  },
  actionsRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    gap: 15,
  },
  actionCard: {
    flex: 1,
    padding: 20,
    borderRadius: 15,
    alignItems: "center",
    justifyContent: "center",
    minHeight: 120,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  primaryAction: {
    backgroundColor: "#962e2eff",
  },
  secondaryAction: {
    backgroundColor: "#1a1a1aff",
  },
  actionText: {
    color: "#fff",
    fontSize: 16,
    fontWeight: "600",
    marginTop: 10,
  },
  statsGrid: {
    flexDirection: "row",
    flexWrap: "wrap",
    justifyContent: "space-between",
    gap: 12,
  },
  statCard: {
    width: (width - 52) / 2,
    backgroundColor: "#fff",
    padding: 20,
    borderRadius: 15,
    alignItems: "center",
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  statValue: {
    fontSize: 32,
    fontWeight: "bold",
    color: "#962e2eff",
    marginTop: 10,
    marginBottom: 5,
  },
  statLabel: {
    fontSize: 14,
    color: "#666",
    fontWeight: "500",
  },
  missionVisionTitle: {
    fontSize: 18,
    fontWeight: "600",
    color: "#1a1a1aff",
    textAlign: "center",
    marginBottom: 20,
    textTransform: "uppercase",
    letterSpacing: 1,
  },
  missionVisionCard: {
    backgroundColor: "#fff",
    borderRadius: 15,
    padding: 20,
    marginBottom: 20,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  missionVisionSubtitle: {
    fontSize: 18,
    fontWeight: "bold",
    color: "#962e2eff",
    textAlign: "center",
    marginBottom: 15,
    textTransform: "uppercase",
  },
  missionVisionText: {
    fontSize: 14,
    color: "#962e2eff",
    textAlign: "center",
    lineHeight: 22,
    letterSpacing: 0.3,
  },
});

