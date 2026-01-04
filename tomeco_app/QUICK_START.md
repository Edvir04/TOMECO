# Quick Start Guide - TOMECO Mobile App

## Step 1: Install Dependencies

```bash
cd tomeco_app
npm install
```

## Step 2: Configure API URL

1. Open `config/api.js`
2. For **physical device testing**, replace `localhost` with your computer's IP address:
   ```javascript
   const API_BASE_URL = 'http://YOUR_IP_ADDRESS:8000/api';
   ```
3. To find your IP address:
   - **Windows**: Open Command Prompt and run `ipconfig`, look for "IPv4 Address"
   - **Mac/Linux**: Run `ifconfig` or `ip addr`, look for your network interface IP

## Step 3: Start Laravel Backend

Open a new terminal:

```bash
cd tomeco_web
php artisan serve
```

The server will run on `http://localhost:8000`

## Step 4: Start Mobile App

In the tomeco_app directory:

```bash
npm start
# or
expo start
```

## Step 5: Run on Device

- **Physical Device**: Scan QR code with Expo Go app
- **Android Emulator**: Press `a`
- **iOS Simulator**: Press `i`

## Troubleshooting

### Can't Connect to Server

1. **Check API URL**: Make sure `config/api.js` has the correct IP address (not localhost for physical devices)
2. **Check Server**: Verify Laravel server is running on port 8000
3. **Check Network**: Ensure device and computer are on the same Wi-Fi network
4. **Check Firewall**: Allow port 8000 through your firewall

### Invalid Credentials

- Make sure you have an enforcer account in the database
- Check username and password are correct
- Verify the enforcer account exists in `tomeco_enforcers` table

## Testing the Connection

1. Open the app
2. Enter username and password
3. Tap "Login"
4. You should see a success message if everything is configured correctly

## Next Steps

After successful login, you can:
- Create a Home/Dashboard screen
- Add navigation to other features
- Implement ticket issuance functionality

