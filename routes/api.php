<?php

use App\Http\Controllers\API\ChatController;
use Illuminate\Support\Facades\Route;

Route::post('/chat', [ChatController::class, 'send']);
Route::get('/chat/history', [ChatController::class, 'history']);
Route::get('/product-info', [ChatController::class, 'listProductInfo']);