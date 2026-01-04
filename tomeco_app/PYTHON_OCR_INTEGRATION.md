# Python OCR Service Integration

## Setup Instructions

### 1. Install Required Packages

Install `form-data` and `node-fetch` for making HTTP requests to Python OCR service:

```bash
cd tomeco_app
npm install form-data node-fetch@2
```

### 2. Start Python OCR Service

Make sure your Python OCR service is running:

```bash
cd Tc_ID_Card_OCR-main\Tc_ID_Card_OCR-main
card_id_ocr_venv\Scripts\activate
python ocr_api.py
```

The service should be running on:
- `http://127.0.0.1:5000`
- `http://192.168.1.10:5000` (your network IP)

### 3. Configure Python OCR URL (Optional)

You can set environment variables to configure the Python OCR service URL:

```bash
# Windows PowerShell
$env:PYTHON_OCR_HOST="192.168.1.10"
$env:PYTHON_OCR_PORT="5000"

# Or in your .env file (if using dotenv)
PYTHON_OCR_HOST=192.168.1.10
PYTHON_OCR_PORT=5000
```

Default values:
- Host: `192.168.1.10` (from your Python service output)
- Port: `5000`

### 4. Start Node.js Server

```bash
cd tomeco_app
node server.js
```

## How It Works

1. **Mobile App** sends image to Node.js server (`/api/mobile/ocr/scan-id`)
2. **Node.js Server**:
   - Validates authentication token (UUID)
   - Receives and saves image file temporarily
   - Forwards image to Python OCR service
   - Receives extracted text from Python
   - Parses text to extract ID card fields (lastname, firstname, middlename, address)
   - Returns formatted response to mobile app
   - Cleans up temporary file

## Testing

1. Make sure Python OCR service is running
2. Make sure Node.js server is running
3. Test from mobile app:
   - Open OCR scan screen
   - Capture or select ID card image
   - Process OCR
   - Should extract information automatically

## Troubleshooting

### Python OCR service not responding

- Check if Python service is running: `http://192.168.1.10:5000/health`
- Verify the IP address matches your network
- Check firewall settings

### Connection refused

- Make sure Python OCR service is running on `0.0.0.0` (all interfaces)
- Verify the IP address in `server.js` matches your Python service IP

### OCR not extracting data

- Check Python OCR service logs
- Verify image quality
- Check if ID card format is supported

## Benefits of Using Python OCR Service

✅ **Better Accuracy**: Uses face detection and perspective correction  
✅ **Advanced Processing**: Handles image orientation and preprocessing  
✅ **Multiple OCR Engines**: Can use both Tesseract and EasyOCR  
✅ **Optimized for ID Cards**: Specifically designed for Philippine ID cards

