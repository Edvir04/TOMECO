# Sanctum Installation & Setup Guide

Sanctum is already listed in your `composer.json` file. Follow these steps to complete the installation:

## Step 1: Install Sanctum via Composer

Run this command in your project directory:

```cmd
composer install
```

Or if you want to specifically update Sanctum:

```cmd
composer update laravel/sanctum
```

## Step 2: Publish Sanctum Configuration (Optional)

Publish the configuration file if you need to customize Sanctum settings:

```cmd
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

## Step 3: Run Sanctum Migrations

Sanctum requires a migration to create the `personal_access_tokens` table:

```cmd
php artisan migrate
```

If the migration doesn't exist, you may need to publish it first:

```cmd
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag="sanctum-migrations"
php artisan migrate
```

## Step 4: Configure Sanctum (if needed)

If you published the config file, you can customize it in `config/sanctum.php`.

For most applications, the default configuration works fine.

## Step 5: Verify Installation

After installation, verify that:

1. ✅ Sanctum is in `composer.json` (already done)
2. ✅ `TomecoEnforcer` model uses `HasApiTokens` trait (already done)
3. ✅ API routes are enabled in `bootstrap/app.php` (already done)
4. ✅ Migration for `personal_access_tokens` table exists and is run

## Troubleshooting

### If you get "Class not found" error:

1. Clear composer autoload:
   ```cmd
   composer dump-autoload
   ```

2. Clear Laravel caches:
   ```cmd
   php artisan config:clear
   php artisan cache:clear
   ```

### If migration fails:

Make sure your database is configured correctly in `.env` file and the database exists.

## Current Status

✅ Code is ready (HasApiTokens trait in TomecoEnforcer model)
✅ API routes configured
⏳ Need to run: `composer install` or `composer update`
⏳ Need to run: Migrations for Sanctum

After completing the steps above, your Sanctum setup will be complete and the admin login should work!

