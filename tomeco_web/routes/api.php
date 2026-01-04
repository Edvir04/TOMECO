<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnforcerAuthController;

/*
|--------------------------------------------------------------------------
| API Routes for Mobile App
|--------------------------------------------------------------------------
|
| These routes are for the mobile app (tomeco_app) to connect with the backend.
| They use API authentication (tokens) instead of session-based auth.
|
*/

// Public API routes (no authentication required)
Route::prefix('mobile')->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is accessible',
            'timestamp' => now()->toDateTimeString(),
        ]);
    });
    
    // Enforcer authentication
    Route::post('/login', [EnforcerAuthController::class, 'login']);
    Route::post('/logout', [EnforcerAuthController::class, 'logout'])->middleware('auth:sanctum');
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [EnforcerAuthController::class, 'profile']);
        
        // Ticket routes
        Route::get('/tickets', [\App\Http\Controllers\Api\TicketController::class, 'index']);
        Route::post('/tickets', [\App\Http\Controllers\Api\TicketController::class, 'store']);
        Route::get('/tickets/{id}', [\App\Http\Controllers\Api\TicketController::class, 'show']);
        
        // OCR routes
        Route::post('/ocr/scan-id', [\App\Http\Controllers\Api\OCRController::class, 'scanIdCard']);
    });
});

