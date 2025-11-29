# How to Run donar_app Using Expo Go

This guide will walk you through running the donar_app on your mobile device using Expo Go.

## Prerequisites

1. **Install Node.js** (if not already installed)
   - Download from: https://nodejs.org/
   - Recommended version: LTS (Long Term Support)

2. **Install Expo CLI** (globally)
   ```bash
   npm install -g expo-cli
   ```
   Or use npx (no global installation needed):
   ```bash
   npx expo-cli --version
   ```

3. **Install Expo Go App on Your Mobile Device**
   - **iOS**: Download from [App Store](https://apps.apple.com/app/expo-go/id982107779)
   - **Android**: Download from [Google Play Store](https://play.google.com/store/apps/details?id=host.exp.exponent)

## Step-by-Step Instructions

### Step 1: Navigate to the Project Directory

Open your terminal/command prompt and navigate to the donar_app folder:

```bash
cd donar_app
```

### Step 2: Install Dependencies

Install all required npm packages:

```bash
npm install
```

**Note**: This may take a few minutes on the first run as it downloads all dependencies.

### Step 3: Start the Expo Development Server

Run the following command to start the Expo development server:

```bash
npm start
```

Or alternatively:

```bash
expo start
```

This will:
- Start the Metro bundler
- Display a QR code in your terminal
- Open the Expo DevTools in your browser (usually at http://localhost:19002)

### Step 4: Connect Your Mobile Device

You have two options to connect:

#### Option A: Scan QR Code (Recommended)

1. **For Android**:
   - Open the Expo Go app on your Android device
   - Tap "Scan QR code"
   - Scan the QR code displayed in your terminal or browser

2. **For iOS**:
   - Open the Camera app on your iPhone
   - Point it at the QR code in your terminal or browser
   - Tap the notification that appears
   - This will open the app in Expo Go

#### Option B: Use Development Build Options

In the terminal where `npm start` is running, you can also:
- Press `a` to open on Android device/emulator
- Press `i` to open on iOS simulator
- Press `w` to open in web browser

### Step 5: Ensure Same Network Connection

**Important**: Your computer and mobile device must be on the same Wi-Fi network for Expo Go to connect.

If you're having connection issues:
- Make sure both devices are on the same Wi-Fi network
- Check that your firewall isn't blocking the connection
- Try using the "Tunnel" connection type (press `s` in the Expo CLI to switch connection types)

## Troubleshooting

### Issue: "Unable to connect to Metro bundler"

**Solutions**:
1. Make sure your phone and computer are on the same Wi-Fi network
2. Try switching to "Tunnel" mode (press `s` in the terminal)
3. Check your firewall settings
4. Restart the Expo server: Press `Ctrl+C` to stop, then run `npm start` again

### Issue: "Module not found" errors

**Solution**:
```bash
# Clear cache and reinstall
rm -rf node_modules
npm install
npm start -- --clear
```

### Issue: QR Code not scanning

**Solutions**:
1. Make sure the terminal window is large enough to display the full QR code
2. Try the browser version at http://localhost:19002 and scan from there
3. Manually enter the connection URL shown in the terminal

### Issue: App crashes on startup

**Solutions**:
1. Check the terminal for error messages
2. Make sure all dependencies are installed: `npm install`
3. Clear the Expo Go app cache on your device
4. Restart the development server with cleared cache: `npm start -- --clear`

## Additional Commands

- **Clear cache**: `npm start -- --clear`
- **Run on Android**: `npm run android` (requires Android Studio/emulator)
- **Run on iOS**: `npm run ios` (requires Xcode, macOS only)
- **Run on Web**: `npm run web`

## Development Tips

1. **Hot Reloading**: Changes you make to the code will automatically reload in Expo Go
2. **Shake Device**: Shake your device to open the Expo developer menu
3. **Reload**: In the developer menu, you can reload the app manually
4. **Debug**: Use React Native Debugger or Chrome DevTools for debugging

## Notes

- The app uses Expo SDK 53
- Make sure you have a stable internet connection
- First load may take longer as the app bundles

---

**Need Help?**
- Check Expo documentation: https://docs.expo.dev/
- Expo Go troubleshooting: https://docs.expo.dev/get-started/installation/

