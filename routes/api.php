<?php

use App\Http\Controllers\API\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BatteryLogController;

Route::post('/chat', [ChatController::class, 'send']);
Route::get('/chat/history', [ChatController::class, 'history']);
Route::get('/product-info', [ChatController::class, 'listProductInfo']);

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/battery-logs', [BatteryLogController::class, 'store']);
    Route::get('/battery-logs', [BatteryLogController::class, 'history']);
});