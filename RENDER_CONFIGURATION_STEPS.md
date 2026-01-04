# Render Configuration Steps

## Step-by-Step Guide for Render Configuration Screen

### Step 1: Configure Database (tomeco-db)

**For the `user` field:**
- Enter: `tomeco_user`
- This is the database username that will be created

**Note:** After the database is created, Render will provide you with:
- Database host (internal hostname)
- Database password
- Database port (usually 5432)

### Step 2: Configure Services (Fill in AFTER Database is Created)

**Important:** You have two options:

#### Option A: Configure Now (Recommended)
1. **First, create the database** by entering `tomeco_user` in the user field
2. **Click "Apply" or "Create"** to create the database
3. **Wait for database to be created** (takes 1-2 minutes)
4. **Go to the database dashboard** to get connection details
5. **Come back to this screen** and fill in the environment variables

#### Option B: Skip Database Variables for Now
1. Leave database environment variables empty for now
2. Click "Apply" to create services
3. Configure environment variables later in each service's dashboard

### Step 3: Environment Variables to Fill

#### For tomeco-api (Node.js API):
```
DB_USER = (from database - will be provided after DB creation)
DB_HOST = (from database - internal hostname like dpg-xxxxx-a.render.com)
DB_NAME = capstone_gigs
DB_PASSWORD = (from database - auto-generated password)
DB_PORT = 5432
```

#### For tomeco-web-admin (Laravel Admin):
```
DB_CONNECTION = pgsql
DB_HOST = (from database - internal hostname)
DB_PORT = 5432
DB_DATABASE = capstone_gigs
DB_USERNAME = (from database - usually same as DB_USER)
DB_PASSWORD = (from database - auto-generated password)
APP_URL = (will be auto-set, but you can verify)
```

#### For tomeco-web-violator (Laravel Violator):
```
DB_CONNECTION = pgsql
DB_HOST = (from database - internal hostname)
DB_PORT = 5432
DB_DATABASE = capstone_gigs
DB_USERNAME = (from database - usually same as DB_USER)
DB_PASSWORD = (from database - auto-generated password)
APP_URL = (will be auto-set, but you can verify)
```

#### For tomeco-ocr (Python OCR):
```
DB_USER = (from database)
DB_HOST = (from database - internal hostname)
DB_NAME = capstone_gigs
DB_PASSWORD = (from database)
DB_PORT = 5432
```

**Note:** The OCR service might not actually need database variables if it doesn't use a database. You can leave these empty if the OCR service doesn't connect to the database.

### Step 4: How to Get Database Connection Details

1. **After clicking "Apply"** and the database is created:
2. **Go to Render Dashboard** → Find "tomeco-db" database
3. **Click on the database** to open its dashboard
4. **Look for "Connections" or "Internal Database URL"** section
5. **Copy these values:**
   - **Internal Hostname** (e.g., `dpg-xxxxx-a.render.com`)
   - **Port** (usually `5432`)
   - **Database Name** (`capstone_gigs`)
   - **User** (usually `tomeco_user` or auto-generated)
   - **Password** (auto-generated, click "Show" to reveal)

### Step 5: Quick Action Plan

**If you want to proceed now:**

1. ✅ **Fill in database user:** `tomeco_user`
2. ✅ **Click "Apply"** to create the database
3. ⏳ **Wait 1-2 minutes** for database creation
4. 📋 **Get database credentials** from database dashboard
5. 🔄 **Come back to this screen** (or configure in service dashboards)
6. ✅ **Fill in all environment variables** with database credentials
7. ✅ **Click "Apply"** again to create all services

**OR - Simpler approach:**

1. ✅ **Fill in database user:** `tomeco_user`
2. ✅ **Leave other fields empty for now**
3. ✅ **Click "Apply"** to create everything
4. 📋 **After services are created**, configure environment variables in each service's dashboard:
   - Go to each service → Environment tab
   - Add environment variables there
   - This is easier and more flexible

### Step 6: Additional Variables Needed Later

After services are created, you'll also need to add:

**For Laravel services (tomeco-web-admin and tomeco-web-violator):**
- `APP_KEY` - Generate with: `php artisan key:generate --show` (or use a random 32-char string)
- `PAYMONGO_SECRET_KEY` - Your PayMongo secret key (if using payments)
- `PAYMONGO_PUBLIC_KEY` - Your PayMongo public key
- `PAYMONGO_WEBHOOK_SECRET` - Your PayMongo webhook secret
- `VIOLATOR_PORTAL_URL` - Will be auto-set from service URL

### Troubleshooting

**If you can't find database credentials:**
- Check the database dashboard
- Look for "Connections" tab
- Use the "Internal Database URL" format if shown

**If services fail to start:**
- Check service logs
- Verify all environment variables are set
- Make sure database is accessible (use internal hostname)

**If you need to update variables later:**
- Go to each service → Environment tab
- Add/edit variables there
- Services will automatically redeploy

