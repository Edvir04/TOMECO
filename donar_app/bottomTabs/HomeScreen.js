// bottomTabs/HomeScreen.js
import React from 'react';
import { View, Text, ScrollView, StyleSheet, Image, TouchableOpacity, ImageBackground } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';

const HomeScreen = ({ route }) => {
  const navigation = useNavigation();
  const user = route?.params?.user ?? {};

  const todayStr = new Date().toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });

  const goToTicketIssuance = () => navigation.navigate('Ticket Issuance', { user });
  const goToTicketHistory = () => navigation.navigate('TicketHistory', { user });

  return (
    <ScrollView
      contentContainerStyle={styles.content}
      showsVerticalScrollIndicator={false}
    >
      {/* Header */}
      <View style={styles.headerTop}>
        <Text style={styles.dateText}>
          Today is <Text style={styles.dateValue}>{todayStr}</Text>
        </Text>

        <Text style={styles.helloText}>
          Hello, <Text style={{ fontWeight: '700' }}>{user?.username}</Text>!
        </Text>

        <Image
          source={require('../assets/newlogo.png')}
          style={styles.headerLogo}
          resizeMode="contain"
        />
      </View>

      {/* Issue ticket (orange) */}
      <TouchableOpacity
        style={[styles.issueTicket, styles.issueRow]}
        activeOpacity={0.85}
        onPress={goToTicketIssuance}
        accessibilityRole="button"
        accessibilityLabel="Issue Ticket"
      >
        <View style={styles.issueIconWrap}>
          <Ionicons name="pencil" size={22} color="#fff" />
        </View>
        <Text style={[styles.issueTicketText, { flex: 1, textAlign: 'center' }]}>
          ISSUE TICKET
        </Text>
        <View style={{ width: 44 }} />
      </TouchableOpacity>

      {/* Yellow stats -> History */}
      <TouchableOpacity
        style={[styles.yellowBox, styles.yellowRow]}
        activeOpacity={0.8}
        onPress={goToTicketHistory}
        accessibilityRole="button"
        accessibilityLabel="View Ticket History"
      >
        <View style={styles.leftWrap}>
          <View style={styles.topRow}>
            <Text style={styles.ticketCount}>0</Text>
            <View style={styles.topRight}>
              <Text style={styles.ticketLabel}>TOTAL ISSUED</Text>
              <Text style={styles.ticketLabel}>TICKET TODAY</Text>
            </View>
          </View>
          <Text style={styles.historyLink}>VIEW ALL TICKET HISTORY</Text>
        </View>

        <View style={styles.iconContainerYellow}>
          <Ionicons name="receipt" size={28} color="#fff" />
        </View>
      </TouchableOpacity>

      {/* Mission & Vision */}
      <Text style={styles.mvHeading}>MISSION AND VISION</Text>

      {/* Mission */}
      <ImageBackground
        source={require('../assets/1.jpg')}
        style={styles.missionBox}
        imageStyle={styles.missionImage}  // ensures full cover, no white strip
      >
        <View style={styles.overlay}>
          <Text style={styles.missionTitle}>MISSION</Text>
          <Text style={styles.missionText}>
            A GLOBALLY COMPETITIVE, GREEN AND RESILIENT CITY, PROPELLED BY GOD-LOVING,
            GENDER RESPONSIVE LEADERS AND EMPOWERED CITIZENRY.
          </Text>
        </View>
      </ImageBackground>

      {/* Vision */}
      <ImageBackground
        source={require('../assets/2.jpg')}
        style={styles.missionBox1}
        imageStyle={styles.missionImage}
      >
        <View style={styles.overlay}>
          <Text style={styles.missionTitle}>VISION</Text>
          <Text style={styles.missionText}>
            TO BE AN AGRI-INDUSTRIAL PARK AND STRATEGIC HUB FOR EDUCATION EXCELLENCE
            IN EASTERN VISAYAS; TO ACHIEVE COMPETENT HUMAN CAPITAL IN A SECURED,
            WELL-BALANCED COMMUNITY.
          </Text>
        </View>
      </ImageBackground>

      {/* bottom spacer so last card isn't clipped */}
      <View style={{ height: 24 }} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  content: {
    paddingBottom: 24,
  },

  /* Header */
  headerTop: {
    backgroundColor: '#962e2eff',
    height: 200,
    borderBottomLeftRadius: 40,
    borderBottomRightRadius: 40,
    paddingLeft: 40,
    paddingTop: 40,
    marginBottom: 56, // room for overlapping logo
  },
  dateText: { fontStyle: 'italic', color: '#d5d5d5ff' },
  dateValue: { color: '#fff' },
  helloText: { color: 'white', fontSize: 24 },
  headerLogo: {
    position: 'absolute',
    bottom: -50,
    alignSelf: 'center',
    width: 110,
    height: 110,
    borderRadius: 90,
    borderWidth: 4,
    borderColor: '#fff',
    backgroundColor: '#fff',
    shadowColor: '#000',
    shadowOpacity: 0.15,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 3 },
    elevation: 4,
  },

  /* Orange button */
  issueTicket: {
    backgroundColor: '#ED6B19',
    width: '90%',
    height: 90,
    alignSelf: 'center',
    borderRadius: 20,
    marginTop: 20,
    shadowColor: '#000',
    shadowOpacity: 0.15,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 3 },
    elevation: 4,
  },
  issueRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    gap: 12,
  },
  issueIconWrap: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(255,255,255,0.35)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  issueTicketText: {
    color: '#3F3B37',
    fontSize: 20,
    fontWeight: '800',
    letterSpacing: 0.3,
  },

  /* Yellow box */
  yellowBox: {
    backgroundColor: '#FFCC3F',
    width: '85%',
    height: 85,
    alignSelf: 'center',
    borderRadius: 20,
    shadowColor: '#000',
    shadowOpacity: 0.15,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 3 },
    elevation: 4,
    marginTop: 16,
  },
  yellowRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
  },
  leftWrap: { flex: 1, paddingLeft: 4, paddingRight: 12 },
  topRow: { flexDirection: 'row', alignItems: 'center' },
  ticketCount: {
    fontSize: 34,
    lineHeight: 36,
    fontWeight: '900',
    color: '#fff',
    marginRight: 12,
  },
  topRight: {},
  ticketLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: '#4f3f0a',
    letterSpacing: 0.3,
  },
  historyLink: { marginTop: 6, fontSize: 10, color: '#4f3f0a', opacity: 0.85 },
  iconContainerYellow: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#ffffff3a',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 16,
  },

  /* M & V */
  mvHeading: {
    alignSelf: 'center',
    marginTop: 24,
    fontSize: 20,
    fontWeight: 'bold',
    color: 'gray',
  },
  missionBox: {
    // width: '92%',
    marginLeft:40,
    marginRight:40,
    minHeight: 180,
    alignSelf: 'center',
    borderRadius: 20,
    overflow: 'hidden',
    marginTop: 16,
  },
  missionBox1: {
     marginLeft:40,
    marginRight:40,
    minHeight: 180,
    alignSelf: 'center',
    borderRadius: 20,
    overflow: 'hidden',
    marginTop: 16,
    marginBottom:60
  },
  missionImage: {
    // CRITICAL: fill entire box; prevents white strip
    resizeMode: 'cover',
  },
  overlay: {
    flex: 1,
    // tweak or remove this if you don't want any fade:
    backgroundColor: 'rgba(255,255,255,0.65)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  missionTitle: {
    fontSize: 15,
    fontWeight: 'bold',
    color: 'darkred',
    marginBottom: 12,
    textAlign: 'center',
  },
  missionText: {
    fontSize: 11,
    fontWeight: 'bold',
    color: 'darkred',
    textAlign: 'center',
    lineHeight: 20,
  },
});

export default HomeScreen;
