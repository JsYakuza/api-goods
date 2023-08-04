<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use App\Http\Middleware\ValidateRegistrationParams;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(ProductController::class)->group(function () {
        Route::put('buy', 'buy');
        Route::put('rent', 'rent');
        Route::put('continue', 'continueRent');
        Route::put('check', 'checkStatus');
    });
    Route::get('history/{userId}', [UserController::class, 'history']);
});

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register'])->middleware(ValidateRegistrationParams::class);
    Route::post('logout', [AuthController::class, 'logout']);
});
