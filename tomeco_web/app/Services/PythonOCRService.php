<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PythonOCRService
{
    private $apiUrl;
    
    public function __construct()
    {
        // Python OCR service URL
        $this->apiUrl = env('PYTHON_OCR_URL', 'http://localhost:5000');
    }
    
    /**
     * Process ID card image using Python OCR service
     * 
     * @param string $imagePath Path to the image file
     * @return array|null Returns extracted text data or null on failure
     */
    public function processImage($imagePath)
    {
        try {
            if (!file_exists($imagePath)) {
                Log::error("PythonOCRService: Image file not found: {$imagePath}");
                return null;
            }
            
            // Check if Python service is available
            $healthResponse = Http::timeout(5)->get("{$this->apiUrl}/health");
            
            if (!$healthResponse->successful()) {
                Log::warning("PythonOCRService: Python OCR service is not available");
                return null;
            }
            
            // Send image to Python OCR service
            $response = Http::timeout(30)
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->apiUrl}/ocr/scan-id");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success']) {
                    Log::info("PythonOCRService: OCR completed successfully");
                    return [
                        'raw_text' => $data['raw_text'] ?? '',
                        'lines' => $data['lines'] ?? [],
                    ];
                } else {
                    Log::error("PythonOCRService: OCR failed: " . ($data['error'] ?? 'Unknown error'));
                    return null;
                }
            } else {
                Log::error("PythonOCRService: HTTP error: " . $response->status());
                return null;
            }
            
        } catch (\Exception $e) {
            Log::error("PythonOCRService: Exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if Python OCR service is available
     * 
     * @return bool
     */
    public function isAvailable()
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}

