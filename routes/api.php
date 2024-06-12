<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Merchant;
use App\Http\Middleware\ValidateSecret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Validasi Secret Header
Route::group(['middleware' => ValidateSecret::class], function () {

    // Authentication
    Route::group([
        'prefix' => 'auth',
        'middleware' => 'guest'
    ], function () {
        // Login
        Route::group(['prefix' => 'login'], function () {
            // Mhs
            Route::post('mhs', [Login::class, 'login_mhs']);
            // Merchant
            Route::post('merchant', [Login::class, 'login_merchant']);
        });
    });

    // Merchant
    Route::group([
        'prefix' => 'merchant',
        'middleware' => 'auth:sanctum'
    ], function () {
        // Get All
        Route::post('all', [Merchant::class, 'all']);
    });
});
