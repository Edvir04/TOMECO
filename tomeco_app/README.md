# TOMECO Mobile App

Mobile application for TOMECO (Traffic Operations Management, Enforcement & Control Office) enforcers.

## Prerequisites

- Node.js (v18 or higher)
- npm or yarn
- Expo CLI (`npm install -g expo-cli`)
- Laravel backend running (tomeco_web)

## Installation

1. Install dependencies:
```bash
npm install
```

2. Configure API URL:
   - Open `config/api.js`
   - Update `API_BASE_URL` with your Laravel backend URL
   - For local development on a physical device, use your computer's IP address instead of `localhost`
   - Example: `http://192.168.1.100:8000/api` (replace with your actual IP)

## Running the App

### Development

1. Start the Laravel backend server:
```bash
cd ../tomeco_web
php artisan serve
# Server will run on http://localhost:8000
```

2. Start the Expo development server:
```bash
npm start
# or
expo start
```

3. Run on your device:
   - Scan the QR code with Expo Go app (iOS/Android)
   - Or press `a` for Android emulator
   - Or press `i` for iOS simulator

### Important Notes for Local Development

- **Physical Device**: If testing on a physical device, you MUST use your computer's IP address in `config/api.js`, not `localhost`
- **Find your IP**: 
  - Windows: `ipconfig` (look for IPv4 Address)
  - Mac/Linux: `ifconfig` or `ip addr`
- **Same Network**: Ensure your device and computer are on the same Wi-Fi network
- **Firewall**: Make sure your firewall allows connections on port 8000

## API Configuration

The app connects to the Laravel backend API. Make sure:

1. Laravel server is running on port 8000 (or update the port in `config/api.js`)
2. API routes are accessible at `/api/mobile/login`
3. CORS is properly configured in Laravel (should be handled automatically)

## Project Structure

```
tomeco_app/
├── App.js                 # Main app component with navigation
├── Section/
│   └── LoginScreen.js     # Login screen for enforcers
├── config/
│   └── api.js            # API configuration
├── assets/               # Images and assets
└── package.json          # Dependencies
```

## Features

- Enforcer authentication
- Token-based API authentication
- Secure credential storage using AsyncStorage

## Troubleshooting

### Connection Issues

1. **"Failed to connect to server"**
   - Check if Laravel server is running
   - Verify API URL in `config/api.js`
   - For physical devices, ensure you're using IP address, not localhost
   - Check firewall settings

2. **"Network request failed"**
   - Ensure device and computer are on the same network
   - Try using your computer's IP address instead of localhost
   - Check if port 8000 is accessible

3. **"Invalid credentials"**
   - Verify enforcer account exists in database
   - Check username and password are correct

### Development Issues

- Clear Expo cache: `expo start -c`
- Reinstall dependencies: `rm -rf node_modules && npm install`
- Reset Metro bundler: `npm start -- --reset-cache`

## Next Steps

After successful login, you'll need to:
1. Create a Home/Dashboard screen
2. Add navigation to other screens
3. Implement ticket issuance features
4. Add profile management

