<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// User routes
Route::prefix('web/api')->name('web.api')->middleware([
    'web'
])->group(function () {
    // Public
    # Route::post('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', function () {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::guard('web')->attempt($credentials, request()->filled('remember'))) {
            request()->session()->regenerate();
            return response()->json([
                'message' => 'User logged',
                'guard' => 'web',
            ]);
        } else {
            return response()->json([
                'message' => 'User invalid credentials',
                'guard' => 'web',
            ], 422);
        }
    });

    // Private user
    Route::middleware([
        'auth:web',
    ])->group(function () {
        // Only logged user
        Route::get('/logged', function () {
            return response()->json([
                'message' => 'User logged',
                'guard' => 'web',
            ]);
        });
    });
});
