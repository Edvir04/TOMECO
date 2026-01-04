# Quick Sanctum Installation Guide

Your `composer install` is stuck on a GitHub token request. Here's how to proceed:

## Option 1: Cancel and Install Sanctum Only (Recommended)

1. **Cancel the current composer process:**
   - Press `Ctrl + C` in the Command Prompt
   - Type `Y` if asked to terminate

2. **Install Sanctum directly:**
   ```cmd
   composer require laravel/sanctum --no-interaction
   ```

3. **If that doesn't work, try with update:**
   ```cmd
   composer update laravel/sanctum --no-interaction
   ```

## Option 2: Provide GitHub Token (If needed for other packages)

If you want to complete the full `composer install`:

1. Go to: https://github.com/settings/tokens/new
2. Generate a new token (no special scopes needed for public repos)
3. Copy the token
4. Paste it in the Composer prompt
5. Press Enter

## Option 3: Skip the Problematic Package

If the issue persists, you can try:

```cmd
composer install --ignore-platform-reqs --no-scripts
```

Then install Sanctum:
```cmd
composer require laravel/sanctum
```

## After Installing Sanctum:

1. **Uncomment API routes** in `bootstrap/app.php`:
   - Line 11: Change `// api: __DIR__.'/../routes/api.php',` 
   - To: `api: __DIR__.'/../routes/api.php',`

2. **Run migrations** (if needed):
   ```cmd
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag="sanctum-migrations"
   php artisan migrate
   ```

3. **Clear caches:**
   ```cmd
   php artisan config:clear
   composer dump-autoload
   ```

4. **Test your admin login** at: `http://localhost:8000/admin/login`

## Current Status

✅ Sanctum is in `composer.json`
✅ Code is ready (HasApiTokens trait in TomecoEnforcer)
⏳ Need to: Cancel composer install and run `composer require laravel/sanctum`

