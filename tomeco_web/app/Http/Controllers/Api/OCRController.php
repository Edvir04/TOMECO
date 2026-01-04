<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PythonOCRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OCRController extends Controller
{
    /**
     * Scan ID card and extract information using OCR
     */
    public function scanIdCard(Request $request)
    {
        try {
            // Debug authentication
            $authHeader = $request->header('Authorization');
            $user = $request->user();
            
            Log::info('OCR Request Debug', [
                'has_auth_header' => !empty($authHeader),
                'auth_header_preview' => $authHeader ? substr($authHeader, 0, 30) . '...' : 'none',
                'user_id' => $user ? $user->id : 'null',
                'user_username' => $user ? $user->username : 'null',
            ]);
            
            if (!$user) {
                $tokenPart = $authHeader ? str_replace('Bearer ', '', $authHeader) : '';
                Log::warning('OCR Request - Unauthenticated user', [
                    'auth_header' => $authHeader ? 'present' : 'missing',
                    'token_length' => strlen($tokenPart),
                    'token_preview' => substr($tokenPart, 0, 20) . '...',
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login again.',
                    'debug' => [
                        'has_auth_header' => !empty($authHeader),
                        'token_length' => strlen($tokenPart),
                    ],
                ], 401);
            }
            
            // Validate request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB max
            ]);

            // Store uploaded image
            $image = $request->file('image');
            $imagePath = $image->store('ocr-temp', 'public');
            $fullImagePath = storage_path('app/public/' . $imagePath);

            // Try Python OCR service first (if available)
            $pythonOCRService = new PythonOCRService();
            $extractedText = null;
            
            if ($pythonOCRService->isAvailable()) {
                Log::info('Using Python OCR service for better accuracy');
                $pythonResult = $pythonOCRService->processImage($fullImagePath);
                
                if ($pythonResult && !empty($pythonResult['raw_text'])) {
                    $extractedText = $pythonResult['raw_text'];
                    Log::info('Python OCR completed successfully', [
                        'text_length' => strlen($extractedText),
                        'lines_count' => count($pythonResult['lines'] ?? [])
                    ]);
                } else {
                    Log::warning('Python OCR service returned empty result, falling back to PHP Tesseract');
                }
            }
            
            // Fallback to PHP Tesseract if Python OCR is not available or failed
            if (empty($extractedText)) {
                Log::info('Using PHP Tesseract OCR');
                
                // Preprocess image to improve OCR accuracy
                $processedImagePath = $this->preprocessImage($fullImagePath);

                // Perform OCR on preprocessed image
                $extractedText = $this->performOCR($processedImagePath);
                
                // Clean up processed image if different from original
                if ($processedImagePath !== $fullImagePath && file_exists($processedImagePath)) {
                    @unlink($processedImagePath);
                }
            }

            // Parse extracted text to extract relevant information
            try {
                $parsedData = $this->parseIDCardText($extractedText);
            } catch (\Exception $parseError) {
                Log::error('Error parsing OCR text', [
                    'error' => $parseError->getMessage(),
                    'trace' => $parseError->getTraceAsString(),
                    'text_preview' => substr($extractedText, 0, 200),
                ]);
                // Return empty data instead of failing completely
                $parsedData = [
                    'lastname' => null,
                    'firstname' => null,
                    'middlename' => null,
                    'address' => null,
                ];
            }

            // Clean up temporary file
            Storage::disk('public')->delete($imagePath);

            // Log extracted data for debugging
            $extractedFields = array_filter($parsedData, function($value) {
                return $value !== null && $value !== '';
            });
            
            Log::info('OCR Results', [
                'extracted_fields' => $extractedFields,
                'raw_text_length' => strlen($extractedText),
                'validation_status' => [
                    'lastname' => !empty($parsedData['lastname']),
                    'firstname' => !empty($parsedData['firstname']),
                    'middlename' => !empty($parsedData['middlename']),
                    'address' => !empty($parsedData['address']),
                ],
            ]);

            // Build success message based on what was extracted
            $extractedCount = count($extractedFields);
            $message = 'ID card processed successfully';
            if ($extractedCount > 0) {
                $fields = [];
                if (!empty($parsedData['lastname'])) $fields[] = 'Last Name';
                if (!empty($parsedData['firstname'])) $fields[] = 'First Name';
                if (!empty($parsedData['middlename'])) $fields[] = 'Middle Name';
                if (!empty($parsedData['address'])) $fields[] = 'Address';
                $message .= '. Extracted: ' . implode(', ', $fields);
            } else {
                $message .= '. No valid data could be extracted. Please enter manually.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $parsedData,
                'validation' => [
                    'lastname_valid' => !empty($parsedData['lastname']),
                    'firstname_valid' => !empty($parsedData['firstname']),
                    'middlename_valid' => !empty($parsedData['middlename']),
                    'address_valid' => !empty($parsedData['address']),
                    'fields_extracted' => $extractedCount,
                ],
                'raw_text' => $extractedText, // For debugging - can be removed in production
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('OCR processing error: ' . $e->getMessage());
            
            // Clean up file if exists
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to process ID card. Please try again or use manual input.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preprocess image to improve OCR accuracy
     * - Convert to grayscale
     * - Enhance contrast
     * - Resize if needed
     */
    private function preprocessImage($imagePath)
    {
        try {
            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                Log::warning('GD extension not available, skipping image preprocessing');
                return $imagePath;
            }

            // Get image info
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) {
                Log::warning('Could not read image info, skipping preprocessing');
                return $imagePath;
            }

            $mimeType = $imageInfo['mime'];
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Create image resource based on type
            switch ($mimeType) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($imagePath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($imagePath);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($imagePath);
                    break;
                default:
                    Log::warning('Unsupported image type: ' . $mimeType);
                    return $imagePath;
            }

            if (!$source) {
                Log::warning('Could not create image resource, skipping preprocessing');
                return $imagePath;
            }

            // Resize if image is too large (max 2000px width/height for better OCR)
            $maxDimension = 2000;
            if ($width > $maxDimension || $height > $maxDimension) {
                $ratio = min($maxDimension / $width, $maxDimension / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
                
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $resized;
                $width = $newWidth;
                $height = $newHeight;
            }

            // Create grayscale image
            $grayscale = imagecreatetruecolor($width, $height);
            
            // Convert to grayscale
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($source, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    
                    // Calculate grayscale value (luminance formula)
                    $gray = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);
                    $grayColor = imagecolorallocate($grayscale, $gray, $gray, $gray);
                    imagesetpixel($grayscale, $x, $y, $grayColor);
                }
            }

            // Enhance contrast (simple contrast enhancement)
            imagefilter($grayscale, IMG_FILTER_CONTRAST, -20);
            imagefilter($grayscale, IMG_FILTER_BRIGHTNESS, 10);

            // Save processed image
            $processedPath = storage_path('app/public/ocr-temp/processed_' . basename($imagePath));
            $dir = dirname($processedPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            imagejpeg($grayscale, $processedPath, 95);
            imagedestroy($source);
            imagedestroy($grayscale);

            Log::info('Image preprocessed successfully', [
                'original' => basename($imagePath),
                'processed' => basename($processedPath),
                'size' => $width . 'x' . $height,
            ]);

            return $processedPath;
        } catch (\Exception $e) {
            Log::error('Image preprocessing error: ' . $e->getMessage());
            // Return original image if preprocessing fails
            return $imagePath;
        }
    }

    /**
     * Perform OCR on image using Tesseract with optimized settings
     */
    private function performOCR($imagePath)
    {
        try {
            $ocr = new TesseractOCR($imagePath);
            
            // Set language
            $ocr->lang('eng');
            
            // Try different PSM modes for better accuracy
            // PSM 6: Assume a single uniform block of text (good for ID cards)
            // PSM 11: Sparse text (try if 6 doesn't work well)
            // PSM 3: Fully automatic page segmentation (default)
            $ocr->psm(6);
            
            // Set OCR Engine Mode (OEM)
            // 3 = Default, based on what is available
            $ocr->oem(3);
            
            // Add whitelist for common characters (letters, numbers, spaces, commas, hyphens, apostrophes)
            // This helps reduce false character recognition
            // Note: whitelist format may vary by TesseractOCR version
            try {
                $whitelistChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz ,-\'.';
                $ocr->whitelist($whitelistChars);
            } catch (\Exception $e) {
                // Whitelist not supported in this version, continue without it
                Log::debug('Whitelist not available: ' . $e->getMessage());
            }
            
            // Set DPI (dots per inch) - higher DPI can improve accuracy
            $ocr->dpi(300);
            
            // Run OCR
            $text = $ocr->run();
            
            Log::info('OCR completed', [
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 100),
            ]);
            
            return $text;
        } catch (\Exception $e) {
            Log::error('Tesseract OCR error: ' . $e->getMessage());
            throw new \Exception('OCR processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Parse extracted text to identify ID card information
     * Only extracts: lastname, firstname, middlename, and address
     * Strategy: First detect field labels, then extract values
     */
    private function parseIDCardText($text)
    {
        $data = [
            'lastname' => null,
            'firstname' => null,
            'middlename' => null,
            'address' => null,
        ];

        // Normalize text - preserve case for names, uppercase for matching
        $originalText = $text;
        $upperText = strtoupper($text);
        $lines = explode("\n", $originalText);
        $lines = array_filter(array_map('trim', $lines));
        $upperLines = array_map('strtoupper', $lines);

        // STEP 1: Detect field labels and extract values
        // This is more accurate than pattern matching alone
        foreach ($lines as $index => $line) {
            $upperLine = $upperLines[$index];
            $line = trim($line);
            
            // Skip empty lines or very short lines
            if (strlen($line) < 3) {
                continue;
            }

            // Detect LAST NAME field
            // Supports: Apelyido/Last Name (Tagalog/English format)
            if (empty($data['lastname'])) {
                $lastnamePatterns = [
                    // Tagalog/English format: "APELYIDO/LAST NAME" or "APELYIDO / LAST NAME"
                    '/(?:APELYIDO\s*\/\s*LAST\s*NAME|APELYIDO|LAST\s*NAME)\s*[#:]\s*(.+)/i',
                    '/(?:APELYIDO\s*\/\s*LAST\s*NAME|APELYIDO|LAST\s*NAME)\s+(.+)/i',
                    // Also support other variations
                    '/(?:SURNAME|FAMILY\s*NAME)\s*[#:]\s*(.+)/i',
                    '/(?:SURNAME|FAMILY\s*NAME)\s+(.+)/i',
                ];
                
                foreach ($lastnamePatterns as $pattern) {
                    if (preg_match($pattern, $upperLine, $matches)) {
                        if (isset($matches[1])) {
                            $value = trim($matches[1]);
                            // Remove any remaining label text that might have been captured
                            $value = preg_replace('/^(APELYIDO|LAST\s*NAME|SURNAME)[#:\s]*/i', '', $value);
                            $value = trim($value);
                            
                            // Clean and validate
                            $cleaned = $this->cleanName($value);
                            if ($this->validateName($cleaned)) {
                                $data['lastname'] = $cleaned;
                                break;
                            }
                        }
                    }
                }
            }

            // Detect FIRST NAME / GIVEN NAME field
            // Supports: Mga Pangalan/Given Name (Tagalog/English format)
            if (empty($data['firstname'])) {
                $firstnamePatterns = [
                    // Tagalog/English format: "MGA PANGALAN/GIVEN NAME" or "MGA PANGALAN / GIVEN NAME"
                    '/(?:MGA\s*PANGALAN\s*\/\s*GIVEN\s*NAME|MGA\s*PANGALAN|GIVEN\s*NAME|FIRST\s*NAME)\s*[#:]\s*(.+)/i',
                    '/(?:MGA\s*PANGALAN\s*\/\s*GIVEN\s*NAME|MGA\s*PANGALAN|GIVEN\s*NAME|FIRST\s*NAME)\s+(.+)/i',
                    // Also support other variations
                    '/(?:FORENAME|NOMBRE|PRIMER\s*NOMBRE)\s*[#:]\s*(.+)/i',
                    '/(?:FORENAME|NOMBRE|PRIMER\s*NOMBRE)\s+(.+)/i',
                ];
                
                foreach ($firstnamePatterns as $pattern) {
                    if (preg_match($pattern, $upperLine, $matches)) {
                        if (isset($matches[1])) {
                            $value = trim($matches[1]);
                            // Remove any remaining label text that might have been captured
                            $value = preg_replace('/^(MGA\s*PANGALAN|GIVEN\s*NAME|FIRST\s*NAME)[#:\s]*/i', '', $value);
                            $value = trim($value);
                            
                            // Clean and validate
                            $cleaned = $this->cleanName($value);
                            if ($this->validateName($cleaned)) {
                                $data['firstname'] = $cleaned;
                                break;
                            }
                        }
                    }
                }
            }

            // Detect MIDDLE NAME field
            // Supports: Gitnang Apelyido/Middle Name (Tagalog/English format)
            if (empty($data['middlename'])) {
                $middlenamePatterns = [
                    // Tagalog/English format: "GITNANG APELYIDO/MIDDLE NAME" or "GITNANG APELYIDO / MIDDLE NAME"
                    '/(?:GITNANG\s*APELYIDO\s*\/\s*MIDDLE\s*NAME|GITNANG\s*APELYIDO|MIDDLE\s*NAME)\s*[#:]\s*(.+)/i',
                    '/(?:GITNANG\s*APELYIDO\s*\/\s*MIDDLE\s*NAME|GITNANG\s*APELYIDO|MIDDLE\s*NAME)\s+(.+)/i',
                    // Also support other variations
                    '/(?:MIDDLE\s*INITIAL|SEGUNDO\s*NOMBRE)\s*[#:]\s*(.+)/i',
                    '/(?:MIDDLE\s*INITIAL|SEGUNDO\s*NOMBRE)\s+(.+)/i',
                ];
                
                foreach ($middlenamePatterns as $pattern) {
                    if (preg_match($pattern, $upperLine, $matches)) {
                        if (isset($matches[1])) {
                            $value = trim($matches[1]);
                            // Remove any remaining label text that might have been captured
                            $value = preg_replace('/^(GITNANG\s*APELYIDO|MIDDLE\s*NAME|MIDDLE\s*INITIAL)[#:\s]*/i', '', $value);
                            $value = trim($value);
                            
                            // Clean and validate (allow empty for middle name)
                            $cleaned = $this->cleanName($value);
                            if ($this->validateName($cleaned, true)) {
                                $data['middlename'] = $cleaned;
                                break;
                            }
                        }
                    }
                }
            }

            // Detect ADDRESS field
            // Supports: Tirahan/Address (Tagalog/English format)
            if (empty($data['address'])) {
                $addressPatterns = [
                    // Tagalog/English format: "TIRAHAN/ADDRESS" or "TIRAHAN / ADDRESS"
                    '/(?:TIRAHAN\s*\/\s*ADDRESS|TIRAHAN|ADDRESS)\s*[#:]\s*(.+)/i',
                    '/(?:TIRAHAN\s*\/\s*ADDRESS|TIRAHAN|ADDRESS)\s+(.+)/i',
                    // Also support other variations
                    '/(?:ADDR|RESIDENCE|RES\.|RESIDENCIAL|DIRECCION|DOMICILIO)\s*[#:]\s*(.+)/i',
                    '/(?:ADDR|RESIDENCE|RES\.|RESIDENCIAL|DIRECCION|DOMICILIO)\s+(.+)/i',
                ];
                
                foreach ($addressPatterns as $pattern) {
                    if (preg_match($pattern, $upperLine, $matches)) {
                        if (isset($matches[1])) {
                            $addressLine = trim($matches[1]);
                            // Remove any remaining label text that might have been captured
                            $addressLine = preg_replace('/^(TIRAHAN|ADDRESS|ADDR|RESIDENCE)[#:\s]*/i', '', $addressLine);
                            $addressLine = trim($addressLine);
                            
                            // Get the full address (may span multiple lines)
                            $addressParts = [$addressLine];
                            
                            // Check next few lines for continuation (up to 4 lines)
                            for ($i = $index + 1; $i < min($index + 4, count($lines)); $i++) {
                                $nextLine = trim($lines[$i] ?? '');
                                if (empty($nextLine)) {
                                    break;
                                }
                                
                                $nextUpper = strtoupper($nextLine);
                                
                                // Stop if we hit another label or field (including Tagalog labels)
                                if (preg_match('/^(?:[A-Z\s]+[#:])|^(?:NAME|ID|BIRTH|DOB|PHONE|TEL|CONTACT|LICENSE|DL|LAST|FIRST|MIDDLE|ADDRESS|APELYIDO|PANGALAN|GITNANG|TIRAHAN)/', $nextUpper)) {
                                    break;
                                }
                                
                                // If next line doesn't look like a label or single number, add to address
                                if (strlen($nextLine) > 3 && !preg_match('/^\d{1,2}$/', $nextLine)) {
                                    $addressParts[] = $nextLine;
                                } else {
                                    break;
                                }
                            }
                            
                            $fullAddress = implode(', ', array_filter($addressParts));
                            $cleaned = $this->cleanAddress($fullAddress);
                            if ($this->validateAddress($cleaned)) {
                                $data['address'] = $cleaned;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // STEP 2: Fallback - If field labels not found, try pattern-based extraction
        // This handles ID cards without clear field labels
        if (empty($data['lastname']) && !empty($lines)) {
            // Try using dedicated extraction function
            $extractedName = $this->extractName($text);
            if ($extractedName) {
                $data['lastname'] = $extractedName['lastname'];
                $data['firstname'] = $extractedName['firstname'];
                $data['middlename'] = $extractedName['middlename'];
            } else {
                // Last resort: try to extract from first substantial line
                foreach ($lines as $line) {
                    $line = trim($line);
                    // Skip lines that are too short or look like labels
                    if (strlen($line) < 5 || preg_match('/^[A-Z\s]+[#:]/', strtoupper($line))) {
                        continue;
                    }
                    
                    // Try to split by comma first (LASTNAME, FIRSTNAME format)
                    if (strpos($line, ',') !== false) {
                        $parts = explode(',', $line, 2);
                        if (count($parts) === 2) {
                            $lastname = $this->cleanName(trim($parts[0]));
                            $firstnamePart = trim($parts[1]);
                            $nameParts = preg_split('/\s+/', $firstnamePart);
                            $firstname = $this->cleanName(trim($nameParts[0] ?? ''));
                            $middlename = isset($nameParts[1]) ? $this->cleanName(trim($nameParts[1])) : null;
                            
                            // Validate before assigning
                            if ($this->validateName($lastname) && $this->validateName($firstname)) {
                                $data['lastname'] = $lastname;
                                $data['firstname'] = $firstname;
                                $data['middlename'] = ($middlename && $this->validateName($middlename, true)) ? $middlename : null;
                                break;
                            }
                        }
                    } else {
                        // Try splitting by spaces (FIRSTNAME MIDDLENAME LASTNAME format)
                        $nameParts = preg_split('/\s+/', $line);
                        if (count($nameParts) >= 2) {
                            $firstname = $this->cleanName(trim($nameParts[0] ?? ''));
                            $middlename = (count($nameParts) >= 3) ? $this->cleanName(trim($nameParts[1] ?? '')) : null;
                            $lastname = $this->cleanName(trim(end($nameParts)));
                            
                            // Validate before assigning
                            if ($this->validateName($firstname) && $this->validateName($lastname)) {
                                $data['firstname'] = $firstname;
                                $data['middlename'] = ($middlename && $this->validateName($middlename, true)) ? $middlename : null;
                                $data['lastname'] = $lastname;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Fallback: Extract address from lines that look like addresses (contain common address words)
        if (empty($data['address'])) {
            // Try using dedicated extraction function
            $extractedAddress = $this->extractAddress($text);
            if ($extractedAddress) {
                $data['address'] = $extractedAddress;
            } else {
                // Last resort: look for address-like patterns
                $addressKeywords = ['STREET', 'ST', 'AVENUE', 'AVE', 'ROAD', 'RD', 'BARANGAY', 'BRGY', 'CITY', 'PROVINCE', 'REGION', 'VILLAGE', 'VILL', 'POBLACION', 'POBL'];
                $addressParts = [];
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strlen($line) < 5) {
                        continue;
                    }
                    
                    $upperLine = strtoupper($line);
                    $hasAddressKeyword = false;
                    foreach ($addressKeywords as $keyword) {
                        if (strpos($upperLine, $keyword) !== false) {
                            $hasAddressKeyword = true;
                            break;
                        }
                    }
                    
                    // If line contains address keywords or looks like an address (has numbers and letters)
                    if ($hasAddressKeyword || (preg_match('/\d+/', $line) && preg_match('/[A-Z]/i', $line) && strlen($line) > 10)) {
                        $cleanedAddress = $this->cleanAddress($line);
                        if ($this->validateAddress($cleanedAddress)) {
                            $addressParts[] = $cleanedAddress;
                        }
                    }
                }
                
                if (!empty($addressParts)) {
                    $data['address'] = implode(', ', $addressParts);
                }
            }
        }

        // Clean up and validate extracted data
        $data = $this->cleanAndValidateData($data);

        return $data;
    }

    /**
     * Clean and validate extracted OCR data
     */
    private function cleanAndValidateData($data)
    {
        // Clean and validate lastname
        if (!empty($data['lastname'])) {
            $data['lastname'] = $this->cleanName($data['lastname']);
            if (!$this->validateName($data['lastname'])) {
                Log::warning('Invalid lastname format', ['value' => $data['lastname']]);
                $data['lastname'] = null;
            }
        }

        // Clean and validate firstname
        if (!empty($data['firstname'])) {
            $data['firstname'] = $this->cleanName($data['firstname']);
            if (!$this->validateName($data['firstname'])) {
                Log::warning('Invalid firstname format', ['value' => $data['firstname']]);
                $data['firstname'] = null;
            }
        }

        // Clean and validate middlename
        if (!empty($data['middlename'])) {
            $data['middlename'] = $this->cleanName($data['middlename']);
            if (!$this->validateName($data['middlename'], true)) {
                Log::warning('Invalid middlename format', ['value' => $data['middlename']]);
                $data['middlename'] = null;
            }
        }

        // Clean and validate address
        if (!empty($data['address'])) {
            $data['address'] = $this->cleanAddress($data['address']);
            if (!$this->validateAddress($data['address'])) {
                Log::warning('Invalid address format', ['value' => $data['address']]);
                $data['address'] = null;
            }
        }

        return $data;
    }

    /**
     * Clean name field - remove invalid characters and normalize
     */
    private function cleanName($name)
    {
        // Remove leading/trailing whitespace
        $name = trim($name);
        
        // Remove common OCR errors (numbers, special characters except hyphens and apostrophes)
        $name = preg_replace('/[^A-Za-z\s\-\']/', '', $name);
        
        // Remove multiple spaces
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Remove single character words (likely OCR errors)
        $words = explode(' ', $name);
        $words = array_filter($words, function($word) {
            return strlen(trim($word)) > 1;
        });
        $name = implode(' ', $words);
        
        // Capitalize properly (Title Case)
        $name = ucwords(strtolower($name));
        
        // Handle special cases (Mc, Mac, O', etc.)
        $name = preg_replace_callback('/\b(Mc|Mac|O\')([a-z])/', function($matches) {
            if (isset($matches[1]) && isset($matches[2])) {
                return $matches[1] . strtoupper($matches[2]);
            }
            return $matches[0] ?? '';
        }, $name);
        
        return trim($name);
    }

    /**
     * Validate name format
     * - Should contain only letters, spaces, hyphens, and apostrophes
     * - Should be at least 2 characters long
     * - Should not be all uppercase or all lowercase (mixed case preferred)
     * - Should not contain numbers
     */
    private function validateName($name, $allowEmpty = false)
    {
        if (empty($name)) {
            return $allowEmpty;
        }

        // Minimum length check
        if (strlen($name) < 2) {
            return false;
        }

        // Maximum length check (reasonable name length)
        if (strlen($name) > 50) {
            return false;
        }

        // Should contain at least one letter
        if (!preg_match('/[A-Za-z]/', $name)) {
            return false;
        }

        // Should not contain numbers
        if (preg_match('/\d/', $name)) {
            return false;
        }

        // Should not be all uppercase (likely OCR error)
        if (strtoupper($name) === $name && strlen($name) > 3) {
            return false;
        }

        // Should not contain only special characters
        if (!preg_match('/[A-Za-z]{2,}/', $name)) {
            return false;
        }

        // Should match valid name pattern (letters, spaces, hyphens, apostrophes)
        if (!preg_match('/^[A-Za-z\s\-\']+$/', $name)) {
            return false;
        }

        // Check for common OCR errors (repeated characters, invalid patterns)
        if (preg_match('/(.)\1{3,}/', $name)) { // 4+ repeated characters
            return false;
        }

        return true;
    }

    /**
     * Clean address field - remove invalid characters and normalize
     */
    private function cleanAddress($address)
    {
        // Remove leading/trailing whitespace
        $address = trim($address);
        
        // Remove excessive whitespace
        $address = preg_replace('/\s+/', ' ', $address);
        
        // Remove leading/trailing commas and periods
        $address = trim($address, ',. ');
        
        // Capitalize first letter of each word (but preserve common abbreviations)
        $address = ucwords(strtolower($address));
        
        // Fix common address abbreviations
        $abbreviations = [
            'St' => 'St.',
            'Ave' => 'Ave.',
            'Rd' => 'Rd.',
            'Blvd' => 'Blvd.',
            'Dr' => 'Dr.',
            'Brgy' => 'Brgy.',
            'Brgy.' => 'Brgy.',
        ];
        
        foreach ($abbreviations as $abbr => $full) {
            $address = preg_replace('/\b' . preg_quote($abbr, '/') . '\b/i', $full, $address);
        }
        
        return trim($address);
    }

    /**
     * Validate address format
     * - Should contain letters and may contain numbers
     * - Should be at least 5 characters long
     * - Should contain at least one letter
     * - Should not be all numbers
     */
    private function validateAddress($address)
    {
        if (empty($address)) {
            return false;
        }

        // Minimum length check
        if (strlen($address) < 5) {
            return false;
        }

        // Maximum length check (reasonable address length)
        if (strlen($address) > 255) {
            return false;
        }

        // Should contain at least one letter
        if (!preg_match('/[A-Za-z]/', $address)) {
            return false;
        }

        // Should not be all numbers
        if (preg_match('/^\d+$/', $address)) {
            return false;
        }

        // Should contain valid address characters (letters, numbers, spaces, commas, periods, hyphens, #, /)
        if (!preg_match('/^[A-Za-z0-9\s,.\-\/#]+$/', $address)) {
            return false;
        }

        // Should have at least 3 words (e.g., "123 Main St" or "Barangay Name, City")
        $wordCount = str_word_count($address);
        if ($wordCount < 2) {
            return false;
        }

        // Check for common OCR errors (too many repeated characters)
        if (preg_match('/(.)\1{5,}/', $address)) { // 6+ repeated characters
            return false;
        }

        return true;
    }

    /**
     * Extract and validate name from text using multiple strategies
     */
    private function extractName($text)
    {
        $name = null;
        
        // Strategy 1: Look for "LASTNAME, FIRSTNAME" pattern
        if (preg_match('/\b([A-Z][A-Za-z]{2,}),\s*([A-Z][A-Za-z]{1,}(?:\s+[A-Z][A-Za-z]{1,})?)\b/', $text, $matches)) {
            // Check if matches array has required indices
            if (isset($matches[1]) && isset($matches[2])) {
                $lastname = $this->cleanName($matches[1]);
                $firstname = $this->cleanName($matches[2]);
                
                if ($this->validateName($lastname) && $this->validateName($firstname)) {
                    return [
                        'lastname' => $lastname,
                        'firstname' => $firstname,
                        'middlename' => null,
                    ];
                }
            }
        }
        
        // Strategy 2: Look for "FIRSTNAME MIDDLENAME LASTNAME" pattern
        if (preg_match('/\b([A-Z][A-Za-z]{2,})\s+([A-Z][A-Za-z]{1,})\s+([A-Z][A-Za-z]{2,})\b/', $text, $matches)) {
            // Check if matches array has required indices
            if (isset($matches[1]) && isset($matches[2]) && isset($matches[3])) {
                $firstname = $this->cleanName($matches[1]);
                $middlename = $this->cleanName($matches[2]);
                $lastname = $this->cleanName($matches[3]);
                
                if ($this->validateName($firstname) && $this->validateName($lastname)) {
                    return [
                        'lastname' => $lastname,
                        'firstname' => $firstname,
                        'middlename' => $this->validateName($middlename, true) ? $middlename : null,
                    ];
                }
            }
        }
        
        return null;
    }

    /**
     * Extract and validate address from text
     */
    private function extractAddress($text)
    {
        // Look for address patterns
        $patterns = [
            // Pattern: "ADDRESS: ..." or "ADDR: ..."
            '/(?:ADDRESS|ADDR|RESIDENCE|RES\.?)\s*[#:]?\s*([A-Za-z0-9\s,.\-\/#]{5,255})/i',
            // Pattern: Lines that look like addresses (contain numbers and letters)
            '/\b\d+[A-Za-z\s,.\-\/#]+[A-Za-z]{3,}/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                // Check if matches array has index 1 (captured group)
                if (isset($matches[1])) {
                    $address = $this->cleanAddress($matches[1]);
                    if ($this->validateAddress($address)) {
                        return $address;
                    }
                } else {
                    // For patterns without capture groups, use the full match
                    if (isset($matches[0])) {
                        $address = $this->cleanAddress($matches[0]);
                        if ($this->validateAddress($address)) {
                            return $address;
                        }
                    }
                }
            }
        }
        
        return null;
    }
}

