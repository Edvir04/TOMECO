# Fix: URL Format Issue

## The Problem

The URL `exp+tomecoapp://expo-development-client/?url=http%3A%2F%2F192.168.1.10%3A8081` is for **development build**, not Expo Go.

## Solutions

### Solution 1: Switch to Expo Go Mode ✅

In the terminal where Expo is running:
1. **Press `s`** to switch to Expo Go mode
2. You should see a different URL format: `exp://192.168.1.10:8081`
3. This is the correct format for Expo Go

### Solution 2: Use Correct URL Manually

If you want to enter manually in Expo Go app:

**Correct URL for Expo Go:**
```
exp://192.168.1.10:8081
```

**NOT:**
- `exp+tomecoapp://...` (development build format)
- `http://192.168.1.10:8081` (missing exp://)

### Solution 3: Use Tunnel Mode

Stop server (Ctrl+C) and run:
```bash
cd tomeco_app
expo start --tunnel
```

Then press `s` to switch to Expo Go mode.

## URL Format Reference

| Mode | URL Format | App Needed |
|------|------------|------------|
| **Expo Go** | `exp://192.168.1.10:8081` | Expo Go app |
| **Development Build** | `exp+tomecoapp://expo-development-client/?url=...` | Custom dev build |
| **Tunnel** | `exp://abc123.ngrok.io:80` | Expo Go app |

## Quick Fix Steps

1. **In terminal, press `s`** (switch to Expo Go)
2. **Look for URL like:** `exp://192.168.1.10:8081`
3. **In Expo Go app:**
   - Tap "Enter URL manually"
   - Enter: `exp://192.168.1.10:8081`
   - Connect

## If Still Not Working

Try tunnel mode:
```bash
# Stop server (Ctrl+C)
cd tomeco_app
expo start --tunnel
# Press 's' to switch to Expo Go
# Use the tunnel URL shown
```

