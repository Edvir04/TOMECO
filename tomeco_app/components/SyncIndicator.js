import React, { useState, useEffect } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { useNetworkStatus } from '../services/NetworkStatus';
import { subscribeToSyncStatus, syncPendingTickets } from '../services/SyncService';
import { getPendingTicketsCount } from '../services/OfflineStorage';

export default function SyncIndicator() {
  const { isConnected } = useNetworkStatus();
  const [isSyncing, setIsSyncing] = useState(false);
  const [syncMessage, setSyncMessage] = useState('');
  const [pendingCount, setPendingCount] = useState(0);

  useEffect(() => {
    // Subscribe to sync status
    const unsubscribeSync = subscribeToSyncStatus((status) => {
      setIsSyncing(status.isSyncing || false);
      setSyncMessage(status.message || '');
    });

    // Update pending count
    const updatePendingCount = async () => {
      const count = await getPendingTicketsCount();
      setPendingCount(count);
    };

    updatePendingCount();
    const interval = setInterval(updatePendingCount, 5000); // Update every 5 seconds

    return () => {
      unsubscribeSync();
      clearInterval(interval);
    };
  }, []);

  const handleManualSync = async () => {
    if (!isConnected) {
      return;
    }

    setIsSyncing(true);
    setSyncMessage('Syncing...');
    
    try {
      const result = await syncPendingTickets();
      if (result.success > 0) {
        setSyncMessage(`Synced ${result.success} ticket(s)`);
      } else if (result.failed > 0) {
        setSyncMessage(`Failed to sync ${result.failed} ticket(s)`);
      } else {
        setSyncMessage('No pending tickets');
      }
    } catch (error) {
      setSyncMessage('Sync failed');
    } finally {
      setTimeout(() => {
        setIsSyncing(false);
        setSyncMessage('');
      }, 2000);
    }
  };

  // Don't show if online and no pending tickets
  if (isConnected && pendingCount === 0 && !isSyncing) {
    return null;
  }

  return (
    <View style={styles.container}>
      {!isConnected && (
        <View style={styles.offlineBadge}>
          <MaterialIcons name="cloud-off" size={16} color="#fff" />
          <Text style={styles.offlineText}>Offline</Text>
        </View>
      )}
      
      {pendingCount > 0 && (
        <View style={styles.pendingBadge}>
          <Text style={styles.pendingText}>{pendingCount} pending</Text>
        </View>
      )}
      
      {isSyncing && (
        <View style={styles.syncingBadge}>
          <ActivityIndicator size="small" color="#fff" />
          <Text style={styles.syncingText}>{syncMessage || 'Syncing...'}</Text>
        </View>
      )}
      
      {isConnected && pendingCount > 0 && !isSyncing && (
        <TouchableOpacity
          style={styles.syncButton}
          onPress={handleManualSync}
          activeOpacity={0.7}
        >
          <MaterialIcons name="sync" size={18} color="#962e2eff" />
          <Text style={styles.syncButtonText}>Sync</Text>
        </TouchableOpacity>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
    backgroundColor: '#fff',
    borderRadius: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    marginHorizontal: 15,
    marginTop: 10,
  },
  offlineBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#e53935',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 4,
  },
  offlineText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
  },
  pendingBadge: {
    backgroundColor: '#ff9800',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  pendingText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
  },
  syncingBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#2196F3',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 6,
  },
  syncingText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
  },
  syncButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f0f0f0',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    gap: 4,
  },
  syncButtonText: {
    color: '#962e2eff',
    fontSize: 12,
    fontWeight: '600',
  },
});

