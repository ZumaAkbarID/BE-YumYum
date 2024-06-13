<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Category;
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
            Route::post('/', [Login::class, 'login_gate']);
            // Merchant
            // Route::post('merchant', [Login::class, 'login_merchant']);
        });
    });

    Route::group(['middleware' => 'auth:sanctum'], function () {
        // Merchant
        Route::group([
            'prefix' => 'merchant',
        ], function () {
            // Get All
            Route::post('all', [Merchant::class, 'all']);
            // Get Detail
            Route::post('detail', [Merchant::class, 'detail']);
        });

        // Category
        Route::group([
            'prefix' => 'category',
        ], function () {
            // Get All
            Route::post('all', [Category::class, 'all']);
            // Get Detail
            Route::post('detail', [Category::class, 'detail']);
        });
    });
});