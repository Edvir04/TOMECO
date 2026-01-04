# Quick Start: Deploy to Render

## ✅ Prerequisites Checklist

- [x] Code is on GitHub (✅ Done - https://github.com/Edvir04/TOMECO)
- [x] render.yaml is configured
- [x] Dockerfiles are created
- [ ] Render account created
- [ ] Environment variables ready

## Step-by-Step Deployment

### Step 1: Create Render Account

1. Go to https://render.com
2. Sign up for a free account (or log in if you already have one)

### Step 2: Deploy Using Blueprint

1. **Go to Render Dashboard**
   - Click "New +" button (top right)
   - Select "Blueprint"

2. **Connect Your Repository**
   - Click "Connect account" if not already connected
   - Select GitHub
   - Authorize Render to access your repositories
   - Select repository: `Edvir04/TOMECO`
   - Click "Connect"

3. **Render Will Auto-Detect render.yaml**
   - Render will automatically detect your `render.yaml` file
   - It will show all 4 services + database
   - Review the services and click "Apply"

4. **Wait for Initial Deployment**
   - Render will start building all services
   - This may take 10-15 minutes for the first deployment
   - You can watch the build logs in real-time

### Step 3: Configure Environment Variables

After services are created, configure environment variables for each service:

#### For Database (tomeco-db):
- The database will be created automatically
- Note down the connection details from the database dashboard

#### For Node.js API (tomeco-api):
Go to the service → Environment tab → Add:
- `DB_USER` = (from database service)
- `DB_HOST` = (from database service - internal hostname)
- `DB_NAME` = `capstone_gigs`
- `DB_PASSWORD` = (from database service)
- `DB_PORT` = `5432`
- `DB_SSL` = `true`

#### For Laravel Admin Portal (tomeco-web-admin):
Go to the service → Environment tab → Add:
- `DB_CONNECTION` = `pgsql`
- `DB_HOST` = (from database service - internal hostname)
- `DB_PORT` = `5432`
- `DB_DATABASE` = `capstone_gigs`
- `DB_USERNAME` = (from database service)
- `DB_PASSWORD` = (from database service)
- `APP_KEY` = (generate with: `php artisan key:generate --show` or use a random 32-char string)
- `APP_URL` = (auto-set, but verify it matches your service URL)

#### For Laravel Violator Portal (tomeco-web-violator):
Same as admin portal, plus:
- `APP_PORTAL_TYPE` = `violator` (should already be set)

#### For Python OCR Service (tomeco-ocr):
- No additional environment variables needed (uses PORT from render.yaml)

### Step 4: Run Database Migrations

1. **Get Shell Access to Laravel Service**
   - Go to tomeco-web-admin service
   - Click "Shell" tab
   - Or use "Manual Deploy" → "Run Command"

2. **Run Migrations**
   ```bash
   cd tomeco_web
   php artisan migrate --force
   ```

### Step 5: Verify Deployment

1. **Check Service URLs**
   - Each service will have a URL like: `https://tomeco-api.onrender.com`
   - Note down all service URLs

2. **Test Health Endpoints**
   - Node.js API: `https://tomeco-api.onrender.com/health`
   - OCR Service: `https://tomeco-ocr.onrender.com/health`
   - Laravel Admin: `https://tomeco-web-admin.onrender.com`
   - Laravel Violator: `https://tomeco-web-violator.onrender.com`

3. **Update Mobile App Configuration**
   - Update `tomeco_app/config/api.js` with your Render URLs:
     ```javascript
     const PRODUCTION_API_URL = 'https://tomeco-api.onrender.com';
     const PRODUCTION_LARAVEL_URL = 'https://tomeco-web-admin.onrender.com';
     ```

## Important Notes

### Free Tier Limitations
- Services spin down after 15 minutes of inactivity
- First request after spin-down takes 30-60 seconds
- Consider upgrading for production use

### Database Connection
- Use the **internal hostname** from the database service (not the external URL)
- Format: `dpg-xxxxx-a.render.com` (internal)
- Enable SSL: `DB_SSL=true`

### Laravel APP_KEY
- Generate a new key: `php artisan key:generate --show`
- Or use a random 32-character string
- This is required for Laravel to work

### Service Communication
- Services can communicate using their service names
- OCR service is accessible at: `https://tomeco-ocr.onrender.com`
- Node.js API will automatically use the OCR service URL

## Troubleshooting

### Build Fails
- Check build logs for errors
- Verify Dockerfiles are correct
- Check that all dependencies are in requirements/package.json

### Database Connection Errors
- Verify you're using the internal database hostname
- Check DB_SSL is set to `true`
- Verify database credentials are correct

### Services Not Starting
- Check service logs
- Verify environment variables are set
- Check that PORT environment variable is set correctly

### Laravel Errors
- Make sure APP_KEY is set
- Run `php artisan config:clear` in shell
- Check that migrations have been run

## Next Steps After Deployment

1. ✅ Update mobile app with production URLs
2. ✅ Configure PayMongo webhooks with Render URLs
3. ✅ Set up monitoring and alerts
4. ✅ Test all functionality
5. ✅ Consider upgrading to paid plan for production

## Support

- Render Docs: https://render.com/docs
- Render Community: https://community.render.com
- Check service logs for detailed error messages

