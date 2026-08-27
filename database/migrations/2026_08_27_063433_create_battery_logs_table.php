<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan histori telemetri baterai USV yang dikirim
     * langsung dari browser (JS) setiap kali menerima data dari ESP32
     * lewat WebSocket. Laravel di sini murni jadi data logger,
     * bukan perantara kontrol (kontrol tetap direct WS ke ESP32).
     */
    public function up(): void
    {
        Schema::create('battery_logs', function (Blueprint $table) {
            $table->id();
            $table->float('voltage');           // Tegangan hasil filter EMA (Volt)
            $table->unsignedTinyInteger('percentage'); // 0-100
            $table->string('status')->nullable();      // NORMAL / LOW BATTERY / dst
            $table->integer('rssi')->nullable();       // Kekuatan sinyal WiFi ESP32 (dBm)
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battery_logs');
    }
};