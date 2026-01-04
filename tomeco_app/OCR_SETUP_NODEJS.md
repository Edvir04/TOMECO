# OCR Setup for Node.js Server

## Installation

You need to install `multer` for file upload handling:

```bash
cd tomeco_app
npm install multer
```

## What's Implemented

✅ **OCR Endpoint**: `/api/mobile/ocr/scan-id`
- Accepts image uploads via multipart/form-data
- Uses Tesseract.js for OCR processing
- Validates UUID tokens (from Node.js authentication)
- Extracts: lastname, firstname, middlename, address
- Returns parsed data in the same format as Laravel OCR

## How It Works

1. **Mobile app** sends image file to Node.js server
2. **Node.js server**:
   - Validates authentication token (UUID format)
   - Saves uploaded image temporarily
   - Processes image with Tesseract.js OCR
   - Parses extracted text to find ID card fields
   - Cleans up temporary file
   - Returns extracted data

## Response Format

**Success:**
```json
{
  "success": true,
  "message": "ID card processed successfully. Extracted: Last Name, First Name, Address",
  "data": {
    "lastname": "Rosete",
    "firstname": "Charles",
    "middlename": null,
    "address": "Bgry. Calogcog, Tanauan, Leyte"
  },
  "validation": {
    "lastname_valid": true,
    "firstname_valid": true,
    "middlename_valid": false,
    "address_valid": true,
    "fields_extracted": 3
  },
  "raw_text": "..."
}
```

## Testing

1. Make sure Node.js server is running:
   ```bash
   cd tomeco_app
   node server.js
   ```

2. Test from mobile app:
   - Open OCR scan screen
   - Capture or select ID card image
   - Process OCR
   - Should extract information automatically

## Notes

- OCR processing may take a few seconds
- First run will download Tesseract.js language data
- Image files are automatically cleaned up after processing
- Supports common ID card formats (Philippine ID cards)

