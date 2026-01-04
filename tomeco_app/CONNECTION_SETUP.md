# Connection Setup - TOMECO Mobile App

## Database Connection

**You do NOT need a `server.js` file!** Your project is already connected to the database through Laravel.

### How It Works

1. **Laravel Backend (tomeco_web)** handles all database connections
2. **API Endpoints** in `tomeco_web/routes/api.php` provide the connection
3. **EnforcerAuthController** (`tomeco_web/app/Http/Controllers/Api/EnforcerAuthController.php`) handles authentication
4. **TomecoEnforcer Model** connects to the `tomeco_enforcers` table in your database

### Database Table Structure

The login uses the `tomeco_enforcers` table with these fields:
- `id` - Primary key
- `username` - Used for login
- `password` - Hashed password (Laravel handles hashing)
- `fullname` - Enforcer's full name
- `id_number` - Employee ID number
- `contact_number` - Contact information
- `profile_picture` - Profile image path
- `gender`, `dob`, `address` - Additional info
- `created_at`, `updated_at` - Timestamps

### Authentication Flow

1. **Mobile App** sends username and password to `/api/mobile/login`
2. **Laravel API** receives the request
3. **EnforcerAuthController** queries the `tomeco_enforcers` table
4. **Password Verification** uses Laravel's Hash::check() to verify the password
5. **Token Generation** creates a Sanctum token for authenticated requests
6. **Response** returns enforcer data and token to the mobile app

### API Endpoint

```
POST http://YOUR_IP:8000/api/mobile/login
Content-Type: application/json

{
  "username": "enforcer_username",
  "password": "enforcer_password"
}
```

### Response Format

**Success (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "enforcer": {
      "id": 1,
      "fullname": "John Doe",
      "username": "johndoe",
      "id_number": "EMP001",
      "contact_number": "09123456789",
      "profile_picture": null
    },
    "token": "1|abc123def456..."
  }
}
```

**Error (401):**
```json
{
  "success": false,
  "message": "Invalid username or password."
}
```

## Testing the Connection

1. **Start Laravel Server:**
   ```bash
   cd tomeco_web
   php artisan serve
   ```

2. **Verify Database:**
   - Make sure you have enforcer records in the `tomeco_enforcers` table
   - Passwords should be hashed using Laravel's Hash facade

3. **Test Login:**
   - Open the mobile app
   - Enter username and password from your database
   - The app will connect to Laravel API which queries the database

## Creating Test Enforcer

If you need to create a test enforcer, you can:

1. **Use Laravel Tinker:**
   ```bash
   cd tomeco_web
   php artisan tinker
   ```
   Then:
   ```php
   use App\Models\TomecoEnforcer;
   use Illuminate\Support\Facades\Hash;
   
   TomecoEnforcer::create([
       'fullname' => 'Test Enforcer',
       'username' => 'test',
       'password' => Hash::make('password123'),
       'id_number' => 'TEST001',
       'gender' => 'male',
       'dob' => '1990-01-01',
       'contact_number' => '09123456789',
       'address' => 'Test Address',
   ]);
   ```

2. **Or use a database seeder** (if you have one set up)

## Troubleshooting

### "Failed to connect to server"
- Check if Laravel server is running (`php artisan serve`)
- Verify API URL in `config/api.js` matches your server IP
- Ensure device and computer are on the same network

### "Invalid username or password"
- Verify the enforcer exists in the database
- Check that password is properly hashed (use `Hash::make()`)
- Verify username is correct (case-sensitive)

### Database Connection Issues
- Check `.env` file in `tomeco_web` directory
- Verify database credentials
- Run migrations: `php artisan migrate`

## Summary

✅ **No server.js needed** - Laravel handles everything
✅ **Database connection** - Already configured in Laravel
✅ **API endpoints** - Already set up in `routes/api.php`
✅ **Authentication** - Already working with Sanctum tokens
✅ **Mobile app** - Just needs to call the API endpoints

Your setup is complete! Just make sure:
1. Laravel server is running
2. Database has enforcer records
3. API URL in mobile app config is correct

