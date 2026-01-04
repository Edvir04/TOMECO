# Offline Features Documentation

This app now supports full offline functionality, allowing users to work without an internet connection and automatically sync data when connectivity is restored.

## Features

### 1. Offline Ticket Creation
- **Save tickets locally** when offline
- All ticket data, images, and signatures are stored on the device
- Tickets are automatically synced when connection is restored
- Manual sync option available

### 2. Offline Login
- **Login with cached credentials** when offline
- User data is cached after successful online login
- Offline login allows access to previously loaded data
- User data automatically syncs when online

### 3. Automatic Sync
- **Auto-sync on connection restore**
- Automatically attempts to sync pending tickets when device comes online
- Background sync of user data
- Sync status indicator in the UI

## How It Works

### Offline Storage

The app uses:
- **AsyncStorage** for ticket metadata and user data
- **Expo FileSystem** for images and signature files
- Local file storage in `offline_files/` directory

### Network Detection

The app detects online/offline status using:
- Periodic connectivity checks (every 5 seconds)
- Fetch-based connectivity test
- Real-time status updates

### Sync Process

1. **When offline:**
   - Tickets are saved locally with unique IDs
   - Files (images, signatures) are copied to local storage
   - Ticket status is marked as "pending"

2. **When online:**
   - Auto-sync triggers automatically
   - Pending tickets are uploaded one by one
   - Files are attached to FormData
   - Successful uploads remove local files
   - Failed uploads are retried on next sync

3. **Manual Sync:**
   - Users can manually trigger sync from the Dashboard
   - Sync button appears when there are pending tickets
   - Shows sync progress and results

## Services

### NetworkStatus (`services/NetworkStatus.js`)
- Detects online/offline status
- Provides React hook `useNetworkStatus()`
- Subscribes to network changes

### OfflineStorage (`services/OfflineStorage.js`)
- Saves tickets locally
- Manages pending tickets queue
- Handles file storage and cleanup

### SyncService (`services/SyncService.js`)
- Syncs pending tickets to server
- Handles retry logic
- Manages sync status

### OfflineAuth (`services/OfflineAuth.js`)
- Caches user credentials (hashed)
- Enables offline login
- Syncs user data when online

## UI Components

### SyncIndicator (`components/SyncIndicator.js`)
- Shows offline status badge
- Displays pending tickets count
- Shows sync progress
- Provides manual sync button

## Usage

### For Users

1. **Offline Ticket Creation:**
   - Create tickets normally
   - If offline, ticket is saved locally
   - Notification confirms offline save
   - Ticket syncs automatically when online

2. **Offline Login:**
   - Login online first to cache credentials
   - When offline, use same credentials
   - App will use cached data
   - Limited features in offline mode

3. **Manual Sync:**
   - Check Dashboard for sync indicator
   - Tap "Sync" button to manually sync
   - View pending tickets count

### For Developers

#### Saving a Ticket Offline

```javascript
import { saveTicketOffline } from '../services/OfflineStorage';
import { isOnline } from '../services/NetworkStatus';

if (!isOnline()) {
  const ticketId = await saveTicketOffline(
    formData,
    images,
    signature,
    driverSignature
  );
}
```

#### Syncing Pending Tickets

```javascript
import { syncPendingTickets } from '../services/SyncService';

const result = await syncPendingTickets();
// result: { success: number, failed: number }
```

#### Offline Login

```javascript
import { attemptOfflineLogin } from '../services/OfflineAuth';

const result = await attemptOfflineLogin(username, password);
if (result.success) {
  // Login successful with cached data
}
```

## Storage Structure

### AsyncStorage Keys
- `pending_tickets` - Array of pending ticket objects
- `cached_user` - Cached user/enforcer data
- `cached_credentials` - Cached login credentials (hashed)
- `last_user_sync` - Timestamp of last user data sync
- `auth_token` - Current authentication token
- `enforcer_data` - Current enforcer data

### File System
- `offline_files/` - Directory for offline files
  - `ticket_{id}_image_{index}_{timestamp}.jpg` - Ticket images
  - `ticket_{id}_signature_{timestamp}.png` - Officer signature
  - `ticket_{id}_driver_signature_{timestamp}.png` - Driver signature

## Security Notes

⚠️ **Important:** The current implementation uses simple encoding for password caching. For production:

1. Use proper encryption libraries (e.g., `expo-crypto`)
2. Implement secure key storage
3. Use proper password hashing (bcrypt, etc.)
4. Consider using biometric authentication
5. Implement proper session management

## Troubleshooting

### Tickets Not Syncing
1. Check network connection
2. Verify authentication token is valid
3. Check pending tickets count in Dashboard
4. Try manual sync
5. Check console for error messages

### Offline Login Not Working
1. Ensure you've logged in online at least once
2. Verify credentials match cached credentials
3. Check if cached user data exists
4. Try logging in online first

### Files Not Uploading
1. Check file permissions
2. Verify files exist in offline_files directory
3. Check file size limits
4. Review server logs for errors

## Future Enhancements

- [ ] Background sync service
- [ ] Conflict resolution for concurrent edits
- [ ] Encrypted local storage
- [ ] Biometric authentication for offline login
- [ ] Sync progress percentage
- [ ] Offline ticket editing
- [ ] Sync queue management UI

