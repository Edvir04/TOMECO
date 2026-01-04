# EAS Build Instructions for Android Production

Follow these steps in **Command Prompt (cmd.exe)** to build your Android production app:

## Step 1: Open Command Prompt
- Press `Win + R`, type `cmd`, and press Enter
- Or search for "Command Prompt" in the Start menu

## Step 2: Navigate to Your Project
```cmd
cd /d C:\Users\Charles Rosete\Capstone_Gigs\tomeco_app
```

## Step 3: Login to EAS (if not already logged in)
```cmd
npx eas-cli login
```
- This will open a browser window for you to login with your Expo account
- If you don't have an account, you'll need to create one at https://expo.dev

## Step 4: Start the Android Production Build
```cmd
npx eas-cli build --platform android --profile production
```

## What to Expect:
1. **First time**: EAS may ask you to confirm some settings:
   - Android package name: `com.babifran.tomeco_app` (should auto-detect)
   - Build type: APK (for production)
   - You may be asked about keystore (EAS can generate one for you)

2. **Build Process**:
   - Your project will be uploaded to Expo's servers
   - The build will run on Expo's cloud infrastructure
   - This typically takes 10-20 minutes

3. **Completion**:
   - You'll get a download link when the build completes
   - The APK file will be available for download
   - You can also check build status at https://expo.dev

## Alternative: Non-Interactive Build
If you want to skip prompts and use defaults:
```cmd
npx eas-cli build --platform android --profile production --non-interactive
```

## Check Build Status
To check the status of your builds:
```cmd
npx eas-cli build:list
```

## Download Your Build
Once complete, you can download the APK:
```cmd
npx eas-cli build:download
```
Or visit https://expo.dev and go to your project's builds section.

## Troubleshooting

### If you get "not logged in" error:
Run `npx eas-cli login` again

### If you get slug mismatch error:
The slug has been fixed to `tomeco-app` in app.json. If you still see errors, verify:
- Your EAS project ID matches: `0367f0df-79fd-4378-8c95-ea3065899395`
- The slug in app.json is `tomeco-app`

### If build fails:
- Check the error message in the terminal
- Visit https://expo.dev to see detailed build logs
- Common issues: missing dependencies, configuration errors, or network issues

## Notes:
- Make sure you have a stable internet connection
- The build runs on Expo's servers, so your local machine doesn't need to be powerful
- You can close the terminal after starting the build - it will continue on Expo's servers
- Check your email or Expo dashboard for build completion notifications

