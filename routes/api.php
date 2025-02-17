<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\PublicAuthController;
use App\Http\Controllers\Public\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'public'], function () {
    // ---------------------------------------------------- Auth ----------------------------------------------------------
    Route::group(['prefix' => 'auth', 'middleware' => ['sanitizer', 'set_timezone:users']], function () {
        Route::post('/login', [PublicAuthController::class, 'login'])->middleware(['guest_or_user', 'cart_verify', 'wishlist_verify']);
        Route::post('signup', [PublicAuthController::class, 'signup'])->middleware(['guest_or_user', 'cart_verify', 'wishlist_verify']);
        Route::get('/logout', [PublicAuthController::class, 'logout']);
        Route::post('/verify-2fa-code', [PublicAuthController::class, 'Verify2FACode']);
        Route::get('/refresh-token', [PublicAuthController::class, 'refreshToken']);
        Route::post('/send-password-reset-token', [PublicAuthController::class, 'SendResetPasswordEmail']);
        Route::post('/check-password-reset-token', [PublicAuthController::class, 'CheckResetPasswordToken']);
        Route::patch('/reset-password', [PublicAuthController::class, 'ResetPassword']);
        Route::get('/me', [PublicAuthController::class, 'me'])->middleware('user_authentication');
        Route::get('/is-authenticated', [PublicAuthController::class, 'isAuthenticated'])->middleware('user_authentication');
    });

    // ---------------------------------------------------- User ----------------------------------------------------------
    Route::group(['prefix' => 'user', 'middleware' => ['user_authentication']], function () {
        // ----------------------- Account ------------------------------
        Route::group(['prefix' => 'account'], function () {
            Route::get('/get-user-info', [UserController::class, 'getUserInfo']);
            Route::patch('/update-user-info', [UserController::class, 'updateInfo']);
            Route::patch('/change-password', [UserController::class, 'changePassword']);
            Route::delete('/delete-me', [UserController::class, 'deleteMe']);
            // Route::get('/get-timezones', [UserController::class, 'getTimezones']);
        });

        // ----------------------- Addresses ------------------------------
        // Route::group(['prefix' => 'addresses'], function () {
        //     Route::get('/', [AddressController::class, 'getAddresses']);
        //     Route::patch('/update-info', [AddressController::class, 'updateAddress']);
        // });
    });


});
