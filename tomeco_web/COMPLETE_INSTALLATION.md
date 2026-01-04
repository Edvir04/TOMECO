# Complete Sanctum Installation

✅ **Good news:** Sanctum directory exists in vendor folder  
❌ **Issue:** Composer update was interrupted, so dependencies are incomplete

## What to Do:

**Complete the composer update (this is important!):**

```cmd
composer update --no-interaction --prefer-dist
```

**IMPORTANT:** 
- **DO NOT cancel** this command
- Let it run to completion (may take 2-5 minutes)
- It will install all missing dependencies

## After Update Completes:

1. **Verify Sanctum is fully installed:**
   ```cmd
   composer show laravel/sanctum
   ```

2. **The API routes are already uncommented** in `bootstrap/app.php` ✅

3. **Run Sanctum migrations** (if needed):
   ```cmd
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag="sanctum-migrations"
   php artisan migrate
   ```

4. **Clear caches:**
   ```cmd
   php artisan config:clear
   composer dump-autoload
   ```

5. **Test admin login:**
   - Start your admin server: `start-admin-server.bat`
   - Go to: `http://localhost:8000/admin/login`

## Current Status:

✅ Sanctum package directory exists  
✅ Code is ready (HasApiTokens trait in TomecoEnforcer)  
✅ API routes are enabled in bootstrap/app.php  
⏳ **Need to complete:** `composer update` to install all dependencies

## Why You're Getting Errors:

The vendor directory is incomplete because the composer update was interrupted. You need to complete it to install all Laravel framework files and dependencies.

