<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:30,1')->group(function () {
    Route::post('/chat', [ChatController::class, 'stream']);
    Route::get('/health', fn () => response()->json(['status' => 'ok']));
});