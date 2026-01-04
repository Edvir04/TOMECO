"""
Flask API service for ID Card OCR
This service provides OCR functionality for Philippine ID cards
"""
import cv2
import numpy as np
import base64
import io
from flask import Flask, request, jsonify
from flask_cors import CORS
import utlis
import extract_words
from extract_words import OcrFactory
import detect_face
import os
import tempfile

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Global variables for models (loaded once)
face_detector = None
ocr_engine = None

def initialize_models():
    """Initialize face detector and OCR engine once"""
    global face_detector, ocr_engine
    
    if face_detector is None:
        face_detector_factory = detect_face.face_factory(face_model="ssd")
        face_detector = face_detector_factory.get_face_detector()
    
    if ocr_engine is None:
        # Use TesseractOCR for better compatibility
        ocr_engine = OcrFactory().select_ocr_method(
            ocr_method="TesseractOcr",
            border_thresh=3,
            denoise=False
        )

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'ID Card OCR API'
    })

@app.route('/ocr/scan-id', methods=['POST'])
def scan_id():
    """
    Process ID card image and extract text
    Expects: multipart/form-data with 'image' field
    Returns: JSON with extracted text and raw OCR output
    """
    try:
        # Check if image is in request
        if 'image' not in request.files:
            return jsonify({
                'success': False,
                'error': 'No image file provided'
            }), 400
        
        image_file = request.files['image']
        
        if image_file.filename == '':
            return jsonify({
                'success': False,
                'error': 'Empty image file'
            }), 400
        
        # Initialize models if not already done
        initialize_models()
        
        # Read image
        image_bytes = image_file.read()
        # Convert bytes to numpy array
        nparr = np.frombuffer(image_bytes, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if img is None:
            return jsonify({
                'success': False,
                'error': 'Invalid image format'
            }), 400
        
        # Convert BGR to RGB
        img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        
        # Step 1: Face detection and orientation correction
        # Try to find face and correct orientation
        try:
            final_img = face_detector.changeOrientationUntilFaceFound(img_rgb, 60)
            if final_img is None:
                # If no face found, use original image
                final_img = img_rgb
        except Exception as e:
            print(f"Face detection error: {e}")
            final_img = img_rgb
        
        # Step 2: Perspective correction
        try:
            final_img = utlis.correctPerspective(final_img)
        except Exception as e:
            print(f"Perspective correction error: {e}")
            # Continue with uncorrected image
        
        # Step 3: Perform OCR on the entire image
        # Convert back to BGR for OpenCV operations
        img_bgr = cv2.cvtColor(final_img, cv2.COLOR_RGB2BGR)
        
        # Use Tesseract to extract all text
        import pytesseract
        raw_text = pytesseract.image_to_string(img_bgr, lang='eng')
        
        # Also try EasyOCR if available (for better accuracy)
        try:
            import easyocr
            reader = easyocr.Reader(['en'], gpu=False)
            easyocr_results = reader.readtext(img_bgr)
            # Combine EasyOCR results
            easyocr_text = ' '.join([result[1] for result in easyocr_results])
            
            # Use EasyOCR if it found more text
            if len(easyocr_text.strip()) > len(raw_text.strip()):
                raw_text = easyocr_text
        except Exception as e:
            print(f"EasyOCR not available or error: {e}")
            # Continue with Tesseract
        
        # Clean up the text
        lines = [line.strip() for line in raw_text.split('\n') if line.strip()]
        cleaned_text = '\n'.join(lines)
        
        return jsonify({
            'success': True,
            'raw_text': cleaned_text,
            'lines': lines,
            'message': 'OCR processing completed'
        })
        
    except Exception as e:
        import traceback
        error_trace = traceback.format_exc()
        print(f"OCR Error: {error_trace}")
        return jsonify({
            'success': False,
            'error': str(e),
            'trace': error_trace
        }), 500

if __name__ == '__main__':
    # Run on all interfaces so Laravel can access it
    # Use PORT environment variable for Render deployment
    import os
    port = int(os.environ.get('PORT', 5000))
    app.run(host='0.0.0.0', port=port, debug=False)

