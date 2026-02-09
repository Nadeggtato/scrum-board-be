<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(GoogleAuthController::class)->prefix('auth/google')->group(function () {
    Route::get('redirect', 'redirect');
    Route::get('callback', 'callback');
});
