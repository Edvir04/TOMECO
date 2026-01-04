# PHP Artisan Serve Configuration

## Quick Serve Command

I've created a simple batch file (`serve.bat`) that runs `php artisan serve` on `0.0.0.0:8000`.

### Usage:

**Windows:**
```cmd
serve.bat
```

**Linux/Mac:**
```bash
chmod +x serve.sh
./serve.sh
```

This will start the server on `http://0.0.0.0:8000` (accessible from all network interfaces).

## Default Behavior

By default, Laravel's `php artisan serve` command binds to `127.0.0.1` (localhost only). To change this, you need to specify the `--host` parameter:

```cmd
php artisan serve --host=0.0.0.0 --port=8000
```

## Available Options

### Run on Localhost Only (Default):
```cmd
php artisan serve
# or explicitly:
php artisan serve --host=127.0.0.1 --port=8000
```

### Run on All Interfaces (Network Accessible):
```cmd
php artisan serve --host=0.0.0.0 --port=8000
```

### Custom Port:
```cmd
php artisan serve --host=0.0.0.0 --port=8080
```

## Making It Default (Optional)

If you want `php artisan serve` to always use `0.0.0.0` by default, you have a few options:

### Option 1: Use the Batch File (Recommended)
Just use `serve.bat` instead of typing the full command.

### Option 2: Create a PowerShell Alias (Windows)
Add to your PowerShell profile:
```powershell
function serve {
    php artisan serve --host=0.0.0.0 --port=8000
}
```

### Option 3: Create a Bash Alias (Linux/Mac)
Add to your `~/.bashrc` or `~/.zshrc`:
```bash
alias serve='php artisan serve --host=0.0.0.0 --port=8000'
```

### Option 4: Create a Composer Script
Add to `composer.json`:
```json
"scripts": {
    "serve": "php artisan serve --host=0.0.0.0 --port=8000"
}
```
Then run: `composer serve`

## Files Created

- `serve.bat` - Windows batch file for quick serve command
- `serve.sh` - Linux/Mac shell script for quick serve command

## Current Server Configurations

- **Admin Server** (`start-admin-server.bat`): `127.0.0.1:8000` (localhost only)
- **Violator Server** (`start-violator-server.bat`): `0.0.0.0:8001` (network accessible)
- **General Serve** (`serve.bat`): `0.0.0.0:8000` (network accessible)

