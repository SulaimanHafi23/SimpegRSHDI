<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// ========== HOME / WELCOME PAGE ==========
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// ========== PUBLIC AUTH ROUTES ==========
Route::middleware('guest')->group(function () {
    // Show login form
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    
    // Process login
    Route::post('login', [LoginController::class, 'login'])->name('login.post');
});

// ========== PROTECTED ROUTES (AUTHENTICATED USERS) ==========
Route::middleware('auth')->group(function () {
    
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard (default redirect after login)
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Admin Dashboard
    Route::middleware('role:Super Admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        
        Route::get('users', function () {
            return view('admin.users');
        })->name('users');
        
        Route::get('settings', function () {
            return view('admin.settings');
        })->name('settings');
    });
    
    // HR Dashboard
    Route::middleware('role:HR')->prefix('hr')->name('hr.')->group(function () {
        Route::get('dashboard', function () {
            return view('hr.dashboard');
        })->name('dashboard');
        
        Route::get('workers', function () {
            return view('hr.workers');
        })->name('workers');
        
        Route::get('attendance', function () {
            return view('hr.attendance');
        })->name('attendance');
    });
    
    // Manager Dashboard
    Route::middleware('role:Manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('dashboard', function () {
            return view('manager.dashboard');
        })->name('dashboard');
        
        Route::get('reports', function () {
            return view('manager.reports');
        })->name('reports');
    });
});
