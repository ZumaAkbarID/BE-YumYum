<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\Auth\VerifyToken;
use App\Http\Controllers\Cart;
use App\Http\Controllers\Category;
use App\Http\Controllers\Favorite;
use App\Http\Controllers\Merchant;
use App\Http\Controllers\Merchant\Order;
use App\Http\Controllers\Product;
use App\Http\Controllers\Profile;
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
        // Register
        Route::group(['prefix' => 'register'], function () {
            // Mhs
            Route::post('/', [Register::class, 'register_gate']);
            // Merchant
            // Route::post('merchant', [Login::class, 'login_merchant']);
        });
    });

    Route::group(['middleware' => 'auth:sanctum'], function () {

        Route::group([
            'prefix' => 'auth'
        ], function () {
            // Verify
            Route::post('verify-token', [VerifyToken::class, 'verify']);
            // Logout
            Route::post('logout', [Logout::class, 'logout']);
        });

        // Merchant for Customer
        Route::group([
            'prefix' => 'merchant',
        ], function () {
            // Get All
            Route::post('/', [Merchant::class, 'all']);
            // Get Detail
            Route::post('detail', [Merchant::class, 'detail']);
        });

        // Category
        Route::group([
            'prefix' => 'category',
        ], function () {
            // Get All
            Route::post('/', [Category::class, 'all']);
            // Get Detail
            Route::post('detail', [Category::class, 'detail']);
        });

        // Product
        Route::group([
            'prefix' => 'product',
        ], function () {
            // Get All
            Route::post('/', [Product::class, 'all']);
            // Get Detail
            Route::post('detail', [Product::class, 'detail']);
            // List Favorite
            Route::post('favorite', [Favorite::class, 'list_fav']);
            // Action
            Route::group([
                'prefix' => 'action'
            ], function () {
                // Toogle
                Route::post('toggle', [Favorite::class, 'toggle_fav']);
                // Fav
                Route::post('favorite', [Favorite::class, 'add_fav']);
                // Unfav
                Route::post('unfavorite', [Favorite::class, 'del_fav']);
            });
        });

        // Cart
        Route::group([
            'prefix' => 'cart',
        ], function () {
            // Get Product with id
            Route::post('fetch-product', [Cart::class, 'fetch_by_id']);
            // Checkout
            Route::post('checkout', [Cart::class, 'checkout']);
        });

        // Profile
        Route::group([
            'prefix' => 'profile',
        ], function () {
            // Detail Profile
            Route::post('/', [Profile::class, 'full']);
        });

        // Merchant for Merchant ofc :v
        Route::group([
            'prefix' => 'merchant',
        ], function () {
            // Orders
            Route::group([
                'prefix' => 'order'
            ], function () {
                // Order Masuk
                Route::post('incoming', [Order::class, 'incoming']);
                // Order Diterima
                Route::post('accepted', [Order::class, 'accepted']);
                // Order Siap
                Route::post('ready', [Order::class, 'ready']);
                // Order Completed
                Route::post('completed', [Order::class, 'completed']);
            });
        });
    });
});
