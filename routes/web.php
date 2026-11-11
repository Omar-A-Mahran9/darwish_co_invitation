<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// Public invitation + form
Route::get('/', [RegistrationController::class, 'show'])->name('invitation');
Route::post('/register', [RegistrationController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('register');

// Admin dashboard
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:5,1');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

        Route::redirect('/', '/admin/registrations');
        Route::get('registrations/export', [AdminRegistrationController::class, 'export'])->name('registrations.export');
        Route::delete('registrations', [AdminRegistrationController::class, 'bulkDestroy'])->name('registrations.bulk-destroy');
        Route::patch('registrations/{registration}/check-in', [AdminRegistrationController::class, 'toggleCheckIn'])->name('registrations.check-in');
        Route::resource('registrations', AdminRegistrationController::class)->except('show');
    });
});

