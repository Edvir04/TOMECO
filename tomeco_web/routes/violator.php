<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Violator Portal Routes
|--------------------------------------------------------------------------
|
| These routes are for the violator portal (public-facing).
| They are prefixed with /violator
|
*/

// Violator Portal Routes (Public - no authentication required)
Route::prefix('violator')->name('violator.')->group(function () {
    // Violator Portal Main Page
    Route::get('/portal', [TicketController::class, 'violatorPortal'])->name('portal');
    
    // Search for ticket by citation number
    Route::get('/portal/search', [TicketController::class, 'searchByCitation'])->name('portal.search');
    
    // GCash Payment Routes (PayMongo)
    Route::post('/payment/initiate', [PaymentController::class, 'initiateGCashPayment'])->name('payment.initiate');
    Route::get('/payment/process', [PaymentController::class, 'processGCashPayment'])->name('payment.process');
    Route::get('/payment/success/{citation_number}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel/{citation_number}', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
    Route::post('/payment/callback', [PaymentController::class, 'gcashCallback'])->name('payment.callback');
});

// Legacy routes for backward compatibility (redirect to new routes)
Route::get('/violator-portal', function () {
    return redirect()->route('violator.portal');
})->name('violator.portal.legacy');

Route::get('/violator-portal/search', function () {
    return redirect()->route('violator.portal.search', request()->query());
})->name('violator.search.legacy');

