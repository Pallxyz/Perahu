<?php

namespace Database\Seeders;

use App\Models\ProductInfo;
use Illuminate\Database\Seeder;

class ProductInfoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['category' => 'Komponen', 'title' => 'Otak Sistem', 'content' => 'Perahu ini dikendalikan oleh mikrokontroler ESP32 DevKit V1, yang mengatur seluruh logika kendali motor, servo, dan pembacaan sensor baterai.'],
            ['category' => 'Komponen', 'title' => 'Motor Penggerak', 'content' => 'Motor DC utama digerakkan lewat driver L298N, dikendalikan dengan sinyal PWM dari ESP32 (GPIO25) untuk mengatur kecepatan, dan dua pin logika arah (GPIO26, GPIO27).'],
            ['category' => 'Komponen', 'title' => 'Kemudi', 'content' => 'Arah perahu (kemudi/rudder) digerakkan oleh motor servo SG90 atau MG996R yang terhubung ke GPIO18. Sudut servo berkisar 0-180 derajat, dengan posisi 90 derajat sebagai posisi lurus/netral.'],
            ['category' => 'Baterai', 'title' => 'Sumber Daya', 'content' => 'Perahu menggunakan baterai LiPo 3S dengan tegangan operasional 11.1V hingga 12.6V saat terisi penuh. Tegangan diturunkan menjadi 5V oleh regulator buck step-down untuk menyuplai ESP32 dan servo.'],
            ['category' => 'Baterai', 'title' => 'Sensor Tegangan Baterai', 'content' => 'Tegangan baterai dipantau lewat rangkaian voltage divider (R1=25kOhm, R2=7.5kOhm) yang terhubung ke pin ADC GPIO36, lalu dihaluskan menggunakan filter digital Exponential Moving Average dengan alpha 0.1.'],
            ['category' => 'Kontrol', 'title' => 'Cara Mengendalikan', 'content' => 'Perahu dikendalikan lewat halaman web yang di-hosting langsung oleh ESP32 sebagai WiFi Access Point bernama "Perahu_IoT". Pengguna menyambungkan HP ke WiFi tersebut, membuka browser ke alamat 192.168.4.1, lalu mengendalikan lewat joystick virtual (kemudi) dan slider (kecepatan).'],
            ['category' => 'Keamanan', 'title' => 'Sistem Proteksi Otomatis', 'content' => 'Jika tegangan baterai turun di bawah 11.0V, kecepatan motor otomatis dibatasi hingga 40%. Jika koneksi WiFi terputus, motor otomatis berhenti total dan kemudi kembali ke posisi lurus sebagai fail-safe.'],
            ['category' => 'Perawatan', 'title' => 'Tips Perawatan Dasar', 'content' => 'Pastikan seluruh sambungan ground (GND) antar komponen tersambung solid di satu titik (common ground). Hindari menyuplai motor servo langsung dari pin 3.3V ESP32.'],
        ];

        foreach ($data as $item) {
            ProductInfo::create($item);
        }
    }
}