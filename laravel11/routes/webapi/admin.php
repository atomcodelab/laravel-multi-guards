<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Admin routes
Route::prefix('web/api/admin')->name('web.api.admin')->middleware([
    'web'
])->group(function () {
    // Public
    # Route::post('/login', [AdminLoginController::class, 'index'])->name('login');
    Route::post('/login', function () {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::guard('admin')->attempt($credentials, request()->filled('remember'))) {
            request()->session()->regenerate();
            return response()->json([
                'message' => 'Admin logged',
                'guard' => 'admin',
            ]);
        } else {
            return response()->json([
                'message' => 'Admin invalid credentials',
                'guard' => 'admin',
            ], 422);
        }
    });

    // Private admin
    Route::middleware([
        'auth:admin',
    ])->group(function () {
        // Only logged admin
        Route::get('/logged', function () {
            return response()->json([
                'message' => 'Admin logged',
                'guard' => 'admin',
            ]);
        });
    });
});
