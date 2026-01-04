# OCR Setup Guide

## Backend Setup (Laravel)

### 1. Install Tesseract OCR

**Windows:**
1. Download Tesseract installer from: https://github.com/UB-Mannheim/tesseract/wiki
   - Recommended: Download the latest Windows installer (e.g., `tesseract-ocr-w64-setup-5.x.x.exe`)
2. Run the installer and install to default location: `C:\Program Files\Tesseract-OCR`
3. **IMPORTANT:** During installation, check the option to "Add to PATH" or manually add:
   - Add `C:\Program Files\Tesseract-OCR` to your system PATH
   - Or add `C:\Program Files\Tesseract-OCR\tesseract.exe` to PATH
4. Restart your terminal/command prompt after installation
5. Verify installation by running: `tesseract --version`

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get update
sudo apt-get install tesseract-ocr
```

**macOS:**
```bash
brew install tesseract
```

### 2. Install PHP Package

```bash
cd tomeco_web
composer require thiagoalessio/tesseract_ocr
```

### 3. Verify Tesseract Installation

```bash
tesseract --version
```

### 4. Test Tesseract Installation

Test if Tesseract is properly installed:

```bash
cd tomeco_web
php test_ocr.php
```

Or test with an image:

```bash
php test_ocr.php path/to/test_image.jpg
```

You should see version information and extracted text if Tesseract is working.

### 5. Test OCR Endpoint

After starting your Laravel server, you can test the OCR endpoint using:

```bash
curl -X POST http://localhost:8000/api/mobile/ocr/scan-id \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@path/to/id_card.jpg"
```

## Mobile App Setup

### 1. Install Dependencies

```bash
cd tomeco_app
npm install
```

### 2. Required Permissions

The app will automatically request camera permissions when you try to use OCR.

## Usage

1. Click "Issue New Ticket" in the Ticket tab
2. Choose "Scan ID Card (OCR)" option
3. Position the ID card within the frame
4. Tap the capture button
5. Review the captured image
6. Tap "Process OCR" to extract information
7. Review and edit the auto-filled form fields
8. Complete the remaining fields and submit

## OCR Extraction

The OCR system extracts:
- Last Name
- First Name
- Middle Name
- Address
- Driver's License Number
- ID Number
- Contact/Phone Number
- Date of Birth

## Troubleshooting

### OCR Not Working
- Ensure Tesseract is installed and in PATH
- Check Laravel logs: `storage/logs/laravel.log`
- Verify image quality (should be clear and well-lit)
- Try different image formats (JPEG, PNG)

### Poor Extraction Results
- Ensure ID card is well-lit
- Keep ID card flat and in focus
- Avoid shadows and glare
- Use high-resolution images

### Permission Errors
- Grant camera permissions in device settings
- Restart the app after granting permissions

