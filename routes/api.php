<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Middleware\ValidateSecret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Validasi Secret Header
Route::group(['middleware' => ValidateSecret::class], function () {

    // Authentication
    Route::group(['prefix' => 'auth'], function () {
        // Login
        Route::post('login', [Login::class, 'login_mhs']);
    });
});
