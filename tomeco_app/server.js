const express = require('express');
const bodyParser = require('body-parser');
const { Pool } = require('pg');
const cors = require('cors');
const bcrypt = require('bcrypt');
const { v4: uuidv4 } = require('uuid');
const path = require('path');
const fs = require('fs');
const multer = require('multer');
const FormData = require('form-data');
const fetch = require('node-fetch');

const app = express();
const PORT = process.env.PORT || 3000;

// CORS Middleware - Allow requests from mobile app
app.use(cors({
  origin: '*', // Allow all origins for development (restrict in production)
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
  credentials: true
}));

// Middleware
app.use(bodyParser.json({limit: '50mb'}));
app.use(bodyParser.urlencoded({ extended: true, limit: '50mb' }));

// Configure multer for file uploads
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    const uploadDir = path.join(__dirname, 'uploads');
    if (!fs.existsSync(uploadDir)) {
      fs.mkdirSync(uploadDir, { recursive: true });
    }
    cb(null, uploadDir);
  },
  filename: function (req, file, cb) {
    cb(null, 'ocr_' + Date.now() + '_' + file.originalname);
  }
});

const upload = multer({ 
  storage: storage,
  limits: { fileSize: 10 * 1024 * 1024 }, // 10MB max
  fileFilter: function (req, file, cb) {
    // Accept only image files
    if (file.mimetype.startsWith('image/')) {
      cb(null, true);
    } else {
      cb(new Error('Only image files are allowed!'), false);
    }
  }
});

// Serve static files from Laravel storage (profile pictures)
// Path to Laravel storage directory (adjust if needed)
const laravelStoragePath = path.join(__dirname, '..', 'tomeco_web', 'storage', 'app', 'public');
app.use('/storage', express.static(laravelStoragePath));

// PostgreSQL configuration - Use environment variables for Render
const pool = new Pool({
  user: process.env.DB_USER || 'postgres',
  host: process.env.DB_HOST || 'localhost',
  database: process.env.DB_NAME || 'capstone_gigs',
  password: process.env.DB_PASSWORD || '1234',
  port: parseInt(process.env.DB_PORT || '5432'),
  // SSL configuration for Render PostgreSQL
  ssl: process.env.DB_SSL === 'true' ? { rejectUnauthorized: false } : false,
});

// Handle the login request for tomeco_enforcers table
app.post('/api/mobile/login', async (req, res) => { 
  try {
    const { username, password } = req.body;

    console.log('=== Login Attempt ===');
    console.log('Username:', username);

    // Validate input
    if (!username || !password) {
      console.log('Missing username or password');
      return res.status(400).json({
        success: false,
        message: 'Username and password are required'
      });
    }

    const client = await pool.connect();
    
    // Check if tomeco_enforcers table exists
    const tableCheck = await client.query(
      "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'tomeco_enforcers')"
    );
    
    if (!tableCheck.rows[0].exists) {
      console.error('ERROR: Table tomeco_enforcers does not exist in database');
      client.release();
      return res.status(500).json({
        success: false,
        message: 'Database table not found. Please run migrations.'
      });
    }

    // Query tomeco_enforcers table: SELECT by username field
    const query = 'SELECT * FROM tomeco_enforcers WHERE username = $1';
    const result = await client.query(query, [username]);

    console.log('Database query result:', result.rows.length, 'row(s) found');

    // Check if user exists
    if (result.rows.length === 0) {
      console.log('User not found in tomeco_enforcers table');
      client.release();
      return res.status(401).json({
        success: false,
        message: 'Invalid username or password'
      });
    }

    const user = result.rows[0];
    console.log('User found:', {
      id: user.id,
      username: user.username,
      fullname: user.fullname
    });
    
    // Validate password field exists
    if (!user.password) {
      console.error('ERROR: Password field is null or empty for user:', username);
      client.release();
      return res.status(500).json({
        success: false,
        message: 'User password not set in database'
      });
    }

    // Verify password: Compare provided password with stored password hash
    let isPasswordValid = false;
    try {
      // Try direct bcrypt comparison
      isPasswordValid = await bcrypt.compare(password, user.password);
      console.log('Password verification (direct):', isPasswordValid);
      
      // If failed and hash uses Laravel format ($2y$), try converting to $2a$ format
      if (!isPasswordValid && user.password.startsWith('$2y$')) {
        console.log('Attempting hash format conversion ($2y$ -> $2a$)');
        const convertedHash = user.password.replace(/^\$2y\$/, '$2a$');
        isPasswordValid = await bcrypt.compare(password, convertedHash);
        console.log('Password verification (converted):', isPasswordValid);
      }
    } catch (bcryptError) {
      console.error('ERROR: Bcrypt comparison failed:', bcryptError.message);
      console.error('Hash format:', user.password.substring(0, 10) + '...');
    }

    if (!isPasswordValid) {
      console.log('Password validation FAILED for user:', username);
      client.release();
      return res.status(401).json({
        success: false,
        message: 'Invalid username or password'
      });
    }

    console.log('Password validation SUCCESS');
    console.log('=== Login Successful ===');

    // Generate a token (you can use JWT here if needed)
    const token = uuidv4();

    // Return user data (matching tomeco_enforcers table structure)
    // Format matching what LoginScreen expects: { success: true, data: { token, enforcer } }
    const enforcer = {
      id: user.id,
      fullname: user.fullname,
      username: user.username,
      id_number: user.id_number || null,
      gender: user.gender,
      dob: user.dob,
      contact_number: user.contact_number,
      address: user.address,
      profile_picture: user.profile_picture || null,
      created_at: user.created_at,
      updated_at: user.updated_at
    };
    
    client.release();

    // Return response matching LoginScreen expected format
    res.status(200).json({ 
      success: true,
      data: {
        token: token,
        enforcer: enforcer
      },
      message: 'Login successful'
    });
    
  } catch (error) {
    console.error('ERROR: Login failed:', error.message);
    console.error('Error stack:', error.stack);
    res.status(500).json({ 
      success: false,
      error: 'Internal Server Error', 
      message: error.message 
    });
  }
});

// Profile endpoint - Get authenticated user profile
app.get('/api/mobile/profile', async (req, res) => {
  try {
    const authHeader = req.headers.authorization;
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        success: false,
        message: 'Unauthorized. Token required.'
      });
    }

    const token = authHeader.substring(7); // Remove 'Bearer ' prefix
    
    // For now, we'll use the token to identify the user
    // In a real implementation, you'd validate the token and get user ID from it
    // For simplicity, we'll get the user ID from a query parameter or token payload
    // Since we're using UUID tokens, we'll need to store token->user mapping
    // For now, let's get user from request or use a simple approach
    
    // Get user ID from query parameter (temporary solution)
    const userId = req.query.user_id;
    
    if (!userId) {
      return res.status(400).json({
        success: false,
        message: 'User ID is required'
      });
    }

    const client = await pool.connect();
    
    // Query user from database
    const query = 'SELECT id, fullname, username, id_number, contact_number, address, profile_picture, created_at, updated_at FROM tomeco_enforcers WHERE id = $1';
    const result = await client.query(query, [userId]);

    if (result.rows.length === 0) {
      client.release();
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }

    const user = result.rows[0];
    client.release();

    // Return user data
    res.status(200).json({
      success: true,
      data: {
        id: user.id,
        fullname: user.fullname,
        username: user.username,
        id_number: user.id_number,
        contact_number: user.contact_number,
        address: user.address,
        profile_picture: user.profile_picture,
      }
    });
  } catch (error) {
    console.error('ERROR: Profile fetch failed:', error.message);
    res.status(500).json({
      success: false,
      message: 'Internal Server Error',
      error: error.message
    });
  }
});

// Test endpoint to verify Python OCR service is accessible
app.get('/api/test-python-ocr', async (req, res) => {
  try {
    const PYTHON_OCR_HOST = process.env.PYTHON_OCR_HOST || 'localhost';
    const PYTHON_OCR_PORT = process.env.PYTHON_OCR_PORT || '5000';
    const healthUrl = `http://${PYTHON_OCR_HOST}:${PYTHON_OCR_PORT}/health`;
    
    console.log('Testing Python OCR connection:', healthUrl);
    const response = await fetch(healthUrl);
    const data = await response.json();
    
    res.json({
      success: true,
      python_ocr_accessible: response.ok,
      python_ocr_url: healthUrl,
      python_ocr_response: data
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message,
      python_ocr_accessible: false
    });
  }
});

// OCR endpoint - Scan ID card using Python OCR service
app.post('/api/mobile/ocr/scan-id', upload.single('image'), async (req, res) => {
  let uploadedFilePath = null;
  
  try {
    // Validate authentication token
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        success: false,
        message: 'Unauthenticated. Token required.'
      });
    }

    const token = authHeader.substring(7); // Remove 'Bearer ' prefix
    
    // Validate token exists (UUID format)
    if (!token || token.length < 10) {
      return res.status(401).json({
        success: false,
        message: 'Invalid token format.'
      });
    }

    // Check if file was uploaded
    if (!req.file) {
      return res.status(400).json({
        success: false,
        message: 'Image file is required. Please upload an ID card image.'
      });
    }

    uploadedFilePath = req.file.path;
    console.log('Processing OCR for file:', uploadedFilePath);

    // Get Python OCR service URL
    // Use localhost since both services run on the same machine via PM2
    const PYTHON_OCR_HOST = process.env.PYTHON_OCR_HOST || 'localhost';
    const PYTHON_OCR_PORT = process.env.PYTHON_OCR_PORT || '5000';
    const pythonOCRUrl = `http://${PYTHON_OCR_HOST}:${PYTHON_OCR_PORT}/ocr/scan-id`;

    console.log('Sending image to Python OCR service:', pythonOCRUrl);
    console.log('Uploaded file path:', uploadedFilePath);
    console.log('File exists:', fs.existsSync(uploadedFilePath));

    // Send image to Python OCR service using form-data
    const formData = new FormData();
    formData.append('image', fs.createReadStream(uploadedFilePath), {
      filename: req.file.originalname || 'id_card.jpg',
      contentType: req.file.mimetype || 'image/jpeg'
    });

    // Use node-fetch (already imported at top of file)
    const fetchFunction = fetch; // node-fetch is already imported

    // Test Python OCR service connectivity first
    try {
      const healthUrl = `http://${PYTHON_OCR_HOST}:${PYTHON_OCR_PORT}/health`;
      console.log('Testing Python OCR service health:', healthUrl);
      const healthResponse = await fetchFunction(healthUrl);
      if (!healthResponse.ok) {
        const errorText = await healthResponse.text().catch(() => 'Unknown error');
        console.warn('Python OCR health check failed with status:', healthResponse.status, 'Response:', errorText.substring(0, 100));
      } else {
        // Check content-type before parsing JSON
        const contentType = healthResponse.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
          try {
            const healthData = await healthResponse.json();
            console.log('Python OCR service is healthy:', healthData);
          } catch (jsonError) {
            const responseText = await healthResponse.text().catch(() => 'Could not read response');
            console.warn('Python OCR health check returned invalid JSON:', responseText.substring(0, 200));
          }
        } else {
          const responseText = await healthResponse.text().catch(() => 'Could not read response');
          console.log('Python OCR service is reachable (non-JSON response):', responseText.substring(0, 100));
        }
      }
    } catch (healthError) {
      console.error('Python OCR service health check failed:', healthError.message);
      console.warn('Continuing with OCR request anyway...');
    }

    console.log('Sending image to Python OCR...');
    console.log('FormData headers:', formData.getHeaders());
    
    // Create a timeout promise
    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => reject(new Error('OCR request timeout after 60 seconds')), 60000);
    });
    
    // Race between fetch and timeout
    const pythonResponse = await Promise.race([
      fetchFunction(pythonOCRUrl, {
        method: 'POST',
        body: formData,
        headers: formData.getHeaders()
      }),
      timeoutPromise
    ]);

    console.log('Python OCR response status:', pythonResponse.status);

    if (!pythonResponse.ok) {
      const errorText = await pythonResponse.text();
      console.error('Python OCR service error:', errorText);
      console.error('Response status:', pythonResponse.status);
      throw new Error(`Python OCR service error: ${pythonResponse.status} - ${errorText}`);
    }

    // Check content-type before parsing JSON
    const contentType = pythonResponse.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      const responseText = await pythonResponse.text();
      console.error('Python OCR returned non-JSON response:', responseText.substring(0, 200));
      throw new Error(`Python OCR service returned non-JSON response: ${responseText.substring(0, 100)}`);
    }

    const pythonData = await pythonResponse.json();
    console.log('Python OCR response data keys:', Object.keys(pythonData));
    
    if (!pythonData.success) {
      console.error('Python OCR returned success=false:', pythonData);
      throw new Error(pythonData.error || 'Python OCR service failed');
    }

    const rawText = pythonData.raw_text || '';
    console.log('Python OCR completed. Extracted text length:', rawText.length);
    console.log('OCR text preview:', rawText.substring(0, 200));

    // Parse extracted text to extract ID card information
    const parsedData = parseIDCardText(rawText);

    // Clean up uploaded file
    if (uploadedFilePath && fs.existsSync(uploadedFilePath)) {
      fs.unlinkSync(uploadedFilePath);
    }

    // Build response
    const extractedFields = Object.values(parsedData).filter(v => v !== null && v !== '');
    const extractedCount = extractedFields.length;

    let message = 'ID card processed successfully';
    if (extractedCount > 0) {
      const fields = [];
      if (parsedData.lastname) fields.push('Last Name');
      if (parsedData.firstname) fields.push('First Name');
      if (parsedData.middlename) fields.push('Middle Name');
      if (parsedData.address) fields.push('Address');
      message += '. Extracted: ' + fields.join(', ');
    } else {
      message += '. No valid data could be extracted. Please enter manually.';
    }

    return res.json({
      success: true,
      message: message,
      data: parsedData,
      validation: {
        lastname_valid: !!parsedData.lastname,
        firstname_valid: !!parsedData.firstname,
        middlename_valid: !!parsedData.middlename,
        address_valid: !!parsedData.address,
        fields_extracted: extractedCount,
      },
      raw_text: rawText, // For debugging
    });

  } catch (error) {
    console.error('OCR processing error:', error);
    
    // Clean up uploaded file on error
    if (uploadedFilePath && fs.existsSync(uploadedFilePath)) {
      try {
        fs.unlinkSync(uploadedFilePath);
      } catch (unlinkError) {
        console.error('Error deleting uploaded file:', unlinkError);
      }
    }

    // Provide more detailed error information
    let errorMessage = 'Failed to process ID card. Please try again or use manual input.';
    if (error.message.includes('ECONNREFUSED') || error.message.includes('timeout')) {
      errorMessage = 'Cannot connect to OCR service. Please ensure Python OCR service is running on port 5000.';
    } else if (error.message.includes('Python OCR service error')) {
      errorMessage = `OCR service error: ${error.message}`;
    }
    
    console.error('Full error details:', {
      message: error.message,
      stack: error.stack,
      python_ocr_host: process.env.PYTHON_OCR_HOST || 'localhost',
      python_ocr_port: process.env.PYTHON_OCR_PORT || '5000'
    });
    
    res.status(500).json({
      success: false,
      message: errorMessage,
      error: error.message,
      debug: {
        python_ocr_host: process.env.PYTHON_OCR_HOST || 'localhost',
        python_ocr_port: process.env.PYTHON_OCR_PORT || '5000'
      }
    });
  }
});

// Helper function to parse ID card text
function parseIDCardText(text) {
  const data = {
    lastname: null,
    firstname: null,
    middlename: null,
    address: null,
  };

  const lines = text.split('\n').map(line => line.trim()).filter(line => line.length > 0);
  const upperText = text.toUpperCase();

  // Extract lastname
  const lastnamePatterns = [
    /(?:APELYIDO\s*\/\s*LAST\s*NAME|APELYIDO|LAST\s*NAME)\s*[#:]\s*(.+)/i,
    /(?:APELYIDO\s*\/\s*LAST\s*NAME|APELYIDO|LAST\s*NAME)\s+(.+)/i,
    /(?:SURNAME|FAMILY\s*NAME)\s*[#:]\s*(.+)/i,
  ];
  
  for (const pattern of lastnamePatterns) {
    const match = upperText.match(pattern);
    if (match && match[1]) {
      const value = cleanName(match[1].trim());
      if (validateName(value)) {
        data.lastname = value;
        break;
      }
    }
  }

  // Extract firstname
  const firstnamePatterns = [
    /(?:MGA\s*PANGALAN\s*\/\s*GIVEN\s*NAME|MGA\s*PANGALAN|GIVEN\s*NAME|FIRST\s*NAME)\s*[#:]\s*(.+)/i,
    /(?:MGA\s*PANGALAN\s*\/\s*GIVEN\s*NAME|MGA\s*PANGALAN|GIVEN\s*NAME|FIRST\s*NAME)\s+(.+)/i,
  ];
  
  for (const pattern of firstnamePatterns) {
    const match = upperText.match(pattern);
    if (match && match[1]) {
      const value = cleanName(match[1].trim());
      if (validateName(value)) {
        data.firstname = value;
        break;
      }
    }
  }

  // Extract middlename
  const middlenamePatterns = [
    /(?:GITNANG\s*APELYIDO\s*\/\s*MIDDLE\s*NAME|GITNANG\s*APELYIDO|MIDDLE\s*NAME)\s*[#:]\s*(.+)/i,
    /(?:GITNANG\s*APELYIDO\s*\/\s*MIDDLE\s*NAME|GITNANG\s*APELYIDO|MIDDLE\s*NAME)\s+(.+)/i,
  ];
  
  for (const pattern of middlenamePatterns) {
    const match = upperText.match(pattern);
    if (match && match[1]) {
      const value = cleanName(match[1].trim());
      if (validateName(value, true)) {
        data.middlename = value;
        break;
      }
    }
  }

  // Extract address
  const addressPatterns = [
    /(?:TIRAHAN\s*\/\s*ADDRESS|TIRAHAN|ADDRESS)\s*[#:]\s*(.+)/i,
    /(?:TIRAHAN\s*\/\s*ADDRESS|TIRAHAN|ADDRESS)\s+(.+)/i,
  ];
  
  for (const pattern of addressPatterns) {
    const match = upperText.match(pattern);
    if (match && match[1]) {
      const value = cleanAddress(match[1].trim());
      if (validateAddress(value)) {
        data.address = value;
        break;
      }
    }
  }

  return data;
}

// Helper functions for cleaning and validating
function cleanName(name) {
  if (!name) return '';
  name = name.trim();
  name = name.replace(/[^A-Za-z\s\-\']/g, '');
  name = name.replace(/\s+/g, ' ');
  const words = name.split(' ').filter(w => w.length > 1);
  name = words.join(' ');
  return name.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
}

function validateName(name, allowEmpty = false) {
  if (!name || name.length === 0) return allowEmpty;
  if (name.length < 2 || name.length > 50) return false;
  if (!/[A-Za-z]/.test(name)) return false;
  if (/\d/.test(name)) return false;
  return /^[A-Za-z\s\-\']+$/.test(name);
}

function cleanAddress(address) {
  if (!address) return '';
  address = address.trim();
  address = address.replace(/\s+/g, ' ');
  return address;
}

function validateAddress(address) {
  if (!address || address.length < 5 || address.length > 255) return false;
  if (!/[A-Za-z]/.test(address)) return false;
  return /^[A-Za-z0-9\s,.\-\/#]+$/.test(address);
}

// Health check endpoint for production monitoring
app.get('/health', async (req, res) => {
  try {
    const PYTHON_OCR_HOST = process.env.PYTHON_OCR_HOST || 'localhost';
    const PYTHON_OCR_PORT = process.env.PYTHON_OCR_PORT || '5000';
    const pythonHealthUrl = `http://${PYTHON_OCR_HOST}:${PYTHON_OCR_PORT}/health`;
    
    // Check Python OCR service
    let pythonStatus = 'unknown';
    try {
      const fetchFunction = require('node-fetch') || (typeof fetch === 'function' ? fetch : null);
      if (fetchFunction) {
        const pythonResponse = await fetchFunction(pythonHealthUrl, { timeout: 5000 });
        pythonStatus = pythonResponse.ok ? 'healthy' : 'unhealthy';
      }
    } catch (error) {
      pythonStatus = 'unreachable';
    }
    
    // Check database connection
    let dbStatus = 'unknown';
    try {
      const client = await pool.connect();
      await client.query('SELECT 1');
      client.release();
      dbStatus = 'healthy';
    } catch (error) {
      dbStatus = 'unhealthy';
    }
    
    const overallStatus = (pythonStatus === 'healthy' && dbStatus === 'healthy') ? 'healthy' : 'degraded';
    const statusCode = overallStatus === 'healthy' ? 200 : 503;
    
    res.status(statusCode).json({
      status: overallStatus,
      services: {
        nodejs: 'running',
        python_ocr: pythonStatus,
        database: dbStatus
      },
      timestamp: new Date().toISOString(),
      uptime: process.uptime()
    });
  } catch (error) {
    res.status(503).json({
      status: 'unhealthy',
      error: error.message,
      timestamp: new Date().toISOString()
    });
  }
});

// Mobile health alias for app precheck
app.get('/api/mobile/health', (req, res) => {
  res.json({ status: 'ok', service: 'node-api' });
});

// Diagnostic endpoint to check database connection and table
app.get('/api/diagnostics', async (req, res) => {
  try {
    const client = await pool.connect();
    
    // Check if table exists
    const tableCheck = await client.query(
      "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'tomeco_enforcers')"
    );
    
    const tableExists = tableCheck.rows[0].exists;
    
    if (!tableExists) {
      client.release();
      return res.json({
        status: 'error',
        message: 'Table tomeco_enforcers does not exist in database',
        tableExists: false
      });
    }
    
    // Get user count
    const userCount = await client.query('SELECT COUNT(*) as count FROM tomeco_enforcers');
    const count = userCount.rows[0].count;
    
    // Get list of usernames (for debugging, without passwords)
    const usernames = await client.query('SELECT id, username, fullname, SUBSTRING(password, 1, 7) as hash_format FROM tomeco_enforcers LIMIT 10');
    
    client.release();
    
    res.json({
      status: 'ok',
      database: 'capstone_gigs',
      tableExists: true,
      userCount: parseInt(count),
      sampleUsers: usernames.rows,
      note: 'Hash format shows first 7 characters of password hash. Should be $2y$10 or $2a$10'
    });
  } catch (error) {
    console.error('Diagnostics error:', error);
    res.status(500).json({
      status: 'error',
      message: error.message,
      stack: error.stack
    });
  }
});

// Start server - Listen on all interfaces (0.0.0.0) to allow mobile app connections
const server = app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server is running on port ${PORT}`);
  console.log(`Server accessible at:`);
  console.log(`  - http://localhost:${PORT}`);
  console.log(`  - http://127.0.0.1:${PORT}`);
  console.log(`  - Use your local network IP for mobile devices`);
  console.log(`\nAPI Endpoints:`);
  console.log(`  - GET  /health (Health check for production)`);
  console.log(`  - POST /api/mobile/login`);
  console.log(`  - GET  /api/mobile/profile`);
  console.log(`  - POST /api/mobile/ocr/scan-id`);
  console.log(`  - GET  /api/diagnostics`);
});

// Handle graceful shutdown
process.on('SIGTERM', () => {
  console.log('SIGTERM signal received: closing HTTP server');
  server.close(() => {
    console.log('HTTP server closed');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('SIGINT signal received: closing HTTP server');
  server.close(() => {
    console.log('HTTP server closed');
    process.exit(0);
  });
});

// Handle uncaught exceptions
process.on('uncaughtException', (error) => {
  console.error('Uncaught Exception:', error);
  // Don't exit, let PM2 handle the restart
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('Unhandled Rejection at:', promise, 'reason:', reason);
  // Don't exit, let PM2 handle the restart
});

