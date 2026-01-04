import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Modal,
} from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';

export default function Ticket({ route, navigation }) {
  const { enforcer } = route.params || {};
  const [isLoading, setIsLoading] = useState(false);
  const [showChoiceModal, setShowChoiceModal] = useState(false);

  const handleIssueTicket = () => {
    setShowChoiceModal(true);
  };

  const handleManualInput = () => {
    setShowChoiceModal(false);
    navigation.navigate('IssueTicket', { enforcer, useOCR: false });
  };

  const handleOCRScan = () => {
    setShowChoiceModal(false);
    navigation.navigate('OCRScan', { enforcer });
  };

  const handleViewHistory = () => {
    navigation.navigate('TicketHistory', { enforcer });
  };

  return (
    <View style={styles.container}>
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Welcome Section */}
        <View style={styles.welcomeSection}>
          <MaterialIcons name="receipt" size={64} color="#962e2eff" />
          <Text style={styles.welcomeTitle}>Ticket Management</Text>
          <Text style={styles.welcomeSubtitle}>
            Issue tickets and view your ticket history
          </Text>
        </View>

        {/* Quick Actions */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Quick Actions</Text>
          
          <TouchableOpacity
            style={[styles.actionCard, styles.primaryAction]}
            onPress={handleIssueTicket}
            activeOpacity={0.8}
          >
            <MaterialIcons name="add-circle" size={32} color="#fff" />
            <Text style={styles.actionText}>Issue New Ticket</Text>
            <Text style={styles.actionSubtext}>
              Create a new traffic violation ticket
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.actionCard, styles.secondaryAction]}
            onPress={handleViewHistory}
            activeOpacity={0.8}
          >
            <MaterialIcons name="history" size={32} color="#fff" />
            <Text style={styles.actionText}>View Ticket History</Text>
            <Text style={styles.actionSubtext}>
              See all tickets you've issued
            </Text>
          </TouchableOpacity>
        </View>

        {/* Info Section */}
        <View style={styles.infoSection}>
          <MaterialIcons name="info" size={24} color="#666" />
          <Text style={styles.infoText}>
            Use the quick actions above to issue new tickets or view your ticket history.
            All tickets are automatically saved and synced with the server.
          </Text>
        </View>
      </ScrollView>

      {/* Choice Modal */}
      <Modal
        visible={showChoiceModal}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setShowChoiceModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>How would you like to proceed?</Text>
            <Text style={styles.modalSubtitle}>
              Choose to scan ID card with OCR or manually enter details
            </Text>

            <TouchableOpacity
              style={[styles.choiceButton, styles.ocrButton]}
              onPress={handleOCRScan}
              activeOpacity={0.8}
            >
              <MaterialIcons name="camera-alt" size={32} color="#fff" />
              <Text style={styles.choiceButtonText}>Scan ID Card (OCR)</Text>
              <Text style={styles.choiceButtonSubtext}>
                Automatically extract information from ID card
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={[styles.choiceButton, styles.manualButton]}
              onPress={handleManualInput}
              activeOpacity={0.8}
            >
              <MaterialIcons name="edit" size={32} color="#fff" />
              <Text style={styles.choiceButtonText}>Manual Input</Text>
              <Text style={styles.choiceButtonSubtext}>
                Enter all details manually
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.cancelButton}
              onPress={() => setShowChoiceModal(false)}
            >
              <Text style={styles.cancelButtonText}>Cancel</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 20,
    paddingBottom: 100, // Space for bottom tab
  },
  welcomeSection: {
    alignItems: 'center',
    paddingVertical: 30,
    marginBottom: 20,
  },
  welcomeTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginTop: 15,
    marginBottom: 8,
  },
  welcomeSubtitle: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
  },
  section: {
    marginBottom: 25,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  actionCard: {
    padding: 20,
    borderRadius: 15,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  primaryAction: {
    backgroundColor: '#962e2eff',
  },
  secondaryAction: {
    backgroundColor: '#1a1a1aff',
  },
  actionText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '600',
    marginTop: 10,
    marginBottom: 5,
  },
  actionSubtext: {
    color: '#fff',
    fontSize: 14,
    opacity: 0.9,
  },
  infoSection: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 10,
    marginTop: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  infoText: {
    flex: 1,
    marginLeft: 10,
    fontSize: 14,
    color: '#666',
    lineHeight: 20,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  modalContent: {
    backgroundColor: '#fff',
    borderRadius: 20,
    padding: 24,
    width: '100%',
    maxWidth: 400,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 10,
  },
  modalTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333',
    textAlign: 'center',
    marginBottom: 8,
  },
  modalSubtitle: {
    fontSize: 14,
    color: '#666',
    textAlign: 'center',
    marginBottom: 24,
  },
  choiceButton: {
    padding: 20,
    borderRadius: 15,
    marginBottom: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  ocrButton: {
    backgroundColor: '#962e2eff',
  },
  manualButton: {
    backgroundColor: '#1a1a1aff',
  },
  choiceButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '600',
    marginTop: 10,
    marginBottom: 5,
  },
  choiceButtonSubtext: {
    color: '#fff',
    fontSize: 13,
    opacity: 0.9,
    textAlign: 'center',
  },
  cancelButton: {
    marginTop: 10,
    padding: 12,
    alignItems: 'center',
  },
  cancelButtonText: {
    fontSize: 16,
    color: '#666',
    fontWeight: '500',
  },
});

