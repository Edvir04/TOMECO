<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // Render the login view as the landing page
    return view('auth.login');
})->name('login')->middleware('guest');

Auth::routes();

Route::get('/payticket', function () {
    return view('auth.payticket');
})->name('payticket');

Route::get('/payticket', [App\Http\Controllers\TicketController::class, 'index'])->name('payticket');


// Employee accounts routes
Route::get('/employees-account', [App\Http\Controllers\EmployeeController::class, 'index'])->name('employees-account');
Route::get('/employees-account/create', [App\Http\Controllers\EmployeeController::class, 'create'])->name('employees-account.create');
Route::post('/employees-account', [App\Http\Controllers\EmployeeController::class, 'store'])->name('employees-account.store');
Route::get('/employees-account/{id}/edit', [App\Http\Controllers\EmployeeController::class, 'edit'])->name('employees-account.edit');
Route::put('/employees-account/{id}', [App\Http\Controllers\EmployeeController::class, 'update'])->name('employees-account.update');
Route::delete('/employees-account/{id}', [App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees-account.destroy');

// Ticket Issued routes
Route::get('/ticket-issued', [App\Http\Controllers\TicketIssuedController::class, 'index'])->name('ticket-issued');
Route::get('/ticket-issued/create', [App\Http\Controllers\TicketIssuedController::class, 'create'])->name('ticket-issued.create');
Route::post('/ticket-issued', [App\Http\Controllers\TicketIssuedController::class, 'store'])->name('ticket-issued.store');
Route::get('/ticket-issued/{id}/edit', [App\Http\Controllers\TicketIssuedController::class, 'edit'])->name('ticket-issued.edit');
Route::put('/ticket-issued/{id}', [App\Http\Controllers\TicketIssuedController::class, 'update'])->name('ticket-issued.update');
Route::delete('/ticket-issued/{id}', [App\Http\Controllers\TicketIssuedController::class, 'destroy'])->name('ticket-issued.destroy');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/add-event', [App\Http\Controllers\AddEventController::class, 'index'])->name('add-event');

    // Routes for adding events
    Route::post('/events', [App\Http\Controllers\AddEventController::class, 'store'])->name('event.store');

});
