<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ViolationController;

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/tickets', [DashboardController::class, 'getTickets'])->name('dashboard.tickets');
    Route::get('/dashboard/users', [DashboardController::class, 'getUsers'])->name('dashboard.users');
    Route::get('/dashboard/pending-tickets', [DashboardController::class, 'getPendingTickets'])->name('dashboard.pending-tickets');
    Route::get('/dashboard/period-reports', [DashboardController::class, 'getPeriodReports'])->name('dashboard.period-reports');
    Route::get('/dashboard/violations-statistics', [DashboardController::class, 'getViolationsStatistics'])->name('dashboard.violations-statistics');
    Route::get('/dashboard/enforcer-statistics', [DashboardController::class, 'getEnforcerStatistics'])->name('dashboard.enforcer-statistics');
    Route::get('/dashboard/violator-statistics', [DashboardController::class, 'getViolatorStatistics'])->name('dashboard.violator-statistics');
    
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
    Route::post('/tickets/{id}/update-court-action-status', [TicketController::class, 'updateCourtActionStatus'])->name('tickets.update-court-action-status');
    Route::post('/tickets/{id}/archive', [TicketController::class, 'archive'])->name('tickets.archive');
    Route::post('/tickets/{id}/unarchive', [TicketController::class, 'unarchive'])->name('tickets.unarchive');
    Route::post('/tickets/auto-archive', [TicketController::class, 'autoArchiveTickets'])->name('tickets.auto-archive');
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    
    // Violations Management
    Route::get('/violations', [ViolationController::class, 'index'])->name('violations');
    Route::post('/violations', [ViolationController::class, 'store'])->name('violations.store');
    Route::get('/violations/{id}', [ViolationController::class, 'show'])->name('violations.show');
    Route::put('/violations/{id}', [ViolationController::class, 'update'])->name('violations.update');
    Route::delete('/violations/{id}', [ViolationController::class, 'destroy'])->name('violations.destroy');
    
    // Penalty Recommendation (DSS)
    Route::get('/penalty', [TicketController::class, 'penaltyRecommendation'])->name('penalty');
});

// Admin Logout
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
Route::get('/admin/logout-auto', [LoginController::class, 'logout'])->name('admin.logout.auto');

