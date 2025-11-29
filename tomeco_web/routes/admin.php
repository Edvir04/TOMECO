<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Admin Portal Routes
|--------------------------------------------------------------------------
|
| These routes are for the admin portal and require authentication.
| They are prefixed with /admin
|
*/

// Admin Login Routes (outside prefix for flexibility)
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.post');

// Admin Protected Routes (require authentication)
Route::middleware(['admin.auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::view('/dashboard', 'layout.dashboard')->name('dashboard');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/settings/personal-info', [SettingsController::class, 'getPersonalInfo'])->name('settings.personal-info');
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
    
    // Accounts Management
    Route::get('/accounts', [AccountsController::class, 'index'])->name('accounts');
    Route::post('/accounts', [AccountsController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{role}/{id}', [AccountsController::class, 'show'])->name('accounts.show');
    Route::put('/accounts/{role}/{id}', [AccountsController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{role}/{id}', [AccountsController::class, 'destroy'])->name('accounts.destroy');
    Route::get('/accounts/{role}/{id}/edit', [AccountsController::class, 'edit'])->name('accounts.edit');
    
    // Ticket Management (Admin)
    Route::get('/ticket-issuance', [TicketController::class, 'index'])->name('ticket-issuance');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{id}/print', [TicketController::class, 'print'])->name('tickets.print');
    Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
});

// Admin Logout
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

