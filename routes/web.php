<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegistrationController;

Route::get('/register/{category}', [RegistrationController::class, 'show'])->name('registration.form');
Route::post('/register/{category}', [RegistrationController::class, 'store'])
    ->middleware('throttle:registration')
    ->name('registration.store');

Route::get('/payment/{registration:registration_number}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{registration:registration_number}', [PaymentController::class, 'store'])
    ->middleware('throttle:payment-upload')
    ->name('payment.store');
Route::get('/status/{registration:registration_number}', [PaymentController::class, 'status'])->name('payment.status');
Route::get('/ticket/{registration:registration_number}', [\App\Http\Controllers\TicketController::class, 'show'])->name('ticket.show');
Route::get('/ticket/{registration:registration_number}/download', [\App\Http\Controllers\TicketController::class, 'download'])->name('ticket.download');

Route::group(['prefix' => 'checkin'], function () {
    Route::get('/', [\App\Http\Controllers\Checkin\CheckinAuthController::class, 'login'])->name('checkin.login');
    Route::post('/authenticate', [\App\Http\Controllers\Checkin\CheckinAuthController::class, 'authenticate'])->name('checkin.authenticate');
    Route::post('/logout', [\App\Http\Controllers\Checkin\CheckinAuthController::class, 'logout'])->name('checkin.logout');

    Route::middleware([\App\Http\Middleware\CheckinAuth::class])->group(function () {
        Route::get('/scan', [\App\Http\Controllers\Checkin\CheckinController::class, 'index'])->name('checkin.scan');
        Route::post('/verify', [\App\Http\Controllers\Checkin\CheckinController::class, 'verify'])->name('checkin.verify');
    });
});
