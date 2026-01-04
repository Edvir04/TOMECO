# Python OCR API Service

This is a Flask-based API service that wraps the ID Card OCR functionality for integration with Laravel.

## Features

- **Image Preprocessing**: Automatic perspective correction and rotation detection
- **Face Detection**: Orients ID card images based on face detection
- **OCR Support**: Uses both Tesseract and EasyOCR for text extraction
- **RESTful API**: Simple HTTP endpoints for easy integration

## Setup

### 1. Install Python Dependencies

```bash
# Create virtual environment
python -m venv card_id_ocr_venv

# Activate virtual environment
# Windows:
card_id_ocr_venv\Scripts\activate
# Linux/Mac:
source card_id_ocr_venv/bin/activate

# Install dependencies
pip install -r requirements_api.txt
```

### 2. Install System Dependencies

#### Tesseract OCR
- **Windows**: Download from [GitHub](https://github.com/UB-Mannheim/tesseract/wiki)
- **Linux**: `sudo apt-get install tesseract-ocr`
- **Mac**: `brew install tesseract`

#### EasyOCR (Optional but recommended)
EasyOCR will be installed via pip, but it requires additional system libraries.

### 3. Start the API Service

#### Windows:
```bash
start_ocr_api.bat
```

#### Linux/Mac:
```bash
python ocr_api.py
```

The service will start on `http://localhost:5000`

## API Endpoints

### Health Check
```
GET /health
```

Response:
```json
{
  "status": "healthy",
  "service": "ID Card OCR API"
}
```

### OCR Scan ID Card
```
POST /ocr/scan-id
Content-Type: multipart/form-data
```

**Request:**
- `image`: Image file (JPEG, PNG)

**Response:**
```json
{
  "success": true,
  "raw_text": "Extracted text from image...",
  "lines": ["Line 1", "Line 2", ...],
  "message": "OCR processing completed"
}
```

## Integration with Laravel

### 1. Update `.env` file

Add the Python OCR service URL:
```env
PYTHON_OCR_URL=http://localhost:5000
```

### 2. The Laravel service will automatically:
- Check if Python OCR service is available
- Use Python OCR if available (better accuracy)
- Fall back to PHP Tesseract if Python service is unavailable

### 3. Usage

The OCR functionality is already integrated in `OCRController`. No additional code changes needed.

## Troubleshooting

### Service not starting
- Check if Python is installed: `python --version`
- Check if port 5000 is available
- Check firewall settings

### OCR accuracy issues
- Ensure Tesseract is properly installed
- Try installing EasyOCR for better accuracy
- Check image quality (resolution, lighting, focus)

### Connection errors from Laravel
- Verify Python service is running: `curl http://localhost:5000/health`
- Check `PYTHON_OCR_URL` in Laravel `.env`
- Ensure firewall allows connections on port 5000

## Notes

- The service uses face detection to orient ID cards correctly
- Perspective correction helps with angled photos
- EasyOCR provides better accuracy but is slower than Tesseract
- The service automatically falls back to Tesseract if EasyOCR is unavailable

