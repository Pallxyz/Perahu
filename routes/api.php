<?php

use App\Http\Controllers\BatteryLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Endpoint ini dipanggil oleh JS di browser (bukan ESP32 langsung),
| setiap kali browser menerima broadcast telemetri dari WebSocket ESP32.
| Kalau route ini pakai middleware 'auth:sanctum' default Laravel API,
| fetch dari Blade akan gagal 401 karena beda guard dari session web.
| Makanya di sini sengaja pakai middleware 'web' agar tetap ikut sesi
| login Breeze yang sedang aktif di browser.
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/battery-logs', [BatteryLogController::class, 'store']);
    Route::get('/battery-logs', [BatteryLogController::class, 'history']);
});