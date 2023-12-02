<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/readers', [StripeController::class, 'listReaders']);

Route::post('/readers/process-payment', [StripeController::class, 'processPayment']);

Route::post('/readers/simulate-payment', [StripeController::class, 'simulatePayment']);

Route::post('/payments/capture', [StripeController::class, 'capturePayment']);

Route::post('/payments/cancel-payment', [StripeController::class, 'cancelPayment']);
