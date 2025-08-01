<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingSlotController;
use App\Http\Middleware\ApiTokenAuth;
use Illuminate\Support\Facades\Route;

Route::middleware(ApiTokenAuth::class)->group(function() {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

    Route::patch('/bookings/{booking}/slots/{slot}', [BookingSlotController::class, 'update']);
    Route::post('/bookings/{booking}/slots', [BookingSlotController::class, 'store']);
});
