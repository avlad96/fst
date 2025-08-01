<?php

use App\Http\Middleware\ApiTokenAuth;
use Illuminate\Support\Facades\Route;

Route::middleware(ApiTokenAuth::class)->group(function() {
    //
});
