<?php

/**
 * Quick test script to verify Tesseract OCR installation
 * Run: php test_ocr.php
 */

require __DIR__ . '/vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

echo "Testing Tesseract OCR Installation...\n\n";

try {
    // Test if Tesseract is accessible by checking version
    $version = shell_exec('tesseract --version 2>&1');
    
    // Check if the output contains version info or error message
    if ($version && strpos($version, 'tesseract') !== false && strpos($version, 'not recognized') === false) {
        echo "✓ Tesseract OCR is installed!\n";
        echo "Version info:\n";
        echo $version . "\n";
    } else {
        echo "✗ Tesseract OCR not found in PATH\n";
        echo "\nError: " . ($version ?: "Command not found") . "\n";
        echo "\nPlease install Tesseract OCR:\n";
        echo "1. Download from: https://github.com/UB-Mannheim/tesseract/wiki\n";
        echo "2. Install to: C:\\Program Files\\Tesseract-OCR\n";
        echo "3. Add to PATH: C:\\Program Files\\Tesseract-OCR\n";
        echo "4. Restart your terminal/command prompt\n";
        echo "5. Verify with: tesseract --version\n";
        exit(1);
    }
    
    // Test with a sample image if provided
    if (isset($argv[1]) && file_exists($argv[1])) {
        echo "\nTesting OCR on image: " . $argv[1] . "\n";
        $text = (new TesseractOCR($argv[1]))
            ->lang('eng')
            ->run();
        
        echo "\nExtracted text:\n";
        echo "---\n";
        echo $text . "\n";
        echo "---\n";
    } else {
        echo "\nTo test OCR on an image, run:\n";
        echo "php test_ocr.php path/to/image.jpg\n";
    }
    
    echo "\n✓ Setup is correct! OCR should work.\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure Tesseract OCR is installed\n";
    echo "2. Add Tesseract to your system PATH\n";
    echo "3. On Windows, default path is: C:\\Program Files\\Tesseract-OCR\n";
    echo "4. Restart your terminal/command prompt after installation\n";
    exit(1);
}

