<div class="flex flex-col gap-6">
    
    <!-- 1. Konfigurasi Jaringan & ESP32 -->
    <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">wifi</span>
            <span>Konfigurasi Jaringan & ESP32</span>
        </h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Alamat IP WebSocket ESP32</label>
                <div class="flex gap-2">
                    <input x-model="esp32Ip" type="text" 
                           class="text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#2d2d44] text-gray-800 dark:text-gray-100 px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <button @click="connectWS()" class="px-4 py-2 rounded-xl text-white font-semibold text-sm bg-indigo-600 hover:bg-indigo-700 transition whitespace-nowrap">
                        Simpan & Konek
                    </button>
                </div>
            </div>

            <!-- Status Indikator Jaringan -->
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Status Koneksi:</span>
                    <span class="font-semibold" :class="wsConnected ? 'text-emerald-500' : 'text-red-500'" x-text="wsConnected ? 'Connected (Aktif)' : 'Disconnected (Terputus)'"></span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Sinyal Wi-Fi (RSSI):</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">-68 dBm (Bagus)</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Latency / Ping:</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">14 ms</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Kontrol & Tindakan Sistem (System Actions) -->
    <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">build</span>
            <span>Tindakan Sistem</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <button type="button" @click="alert('Mengirim perintah Reboot ke ESP32...')" 
                    class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 font-semibold text-sm hover:bg-amber-100 dark:hover:bg-amber-900/40 transition">
                <span class="material-symbols-outlined text-lg">restart_alt</span>
                <span>Reboot ESP32</span>
            </button>

            <button type="button" @click="alert('Memulai Kalibrasi Gyro & Kompas...')" 
                    class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 font-semibold text-sm hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition">
                <span class="material-symbols-outlined text-lg">explore</span>
                <span>Kalibrasi Sensor</span>
            </button>

            <button type="button" @click="alert('Data log dibersihkan.')" 
                    class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-[#2d2d44] text-gray-700 dark:text-gray-300 font-semibold text-sm hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span class="material-symbols-outlined text-lg">delete_sweep</span>
                <span>Bersihkan Log</span>
            </button>
        </div>
    </div>

    <!-- 3. Informasi Perangkat Keras -->
    <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">developer_board</span>
            <span>Informasi Perangkat Keras</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-[#2d2d44] border border-gray-100 dark:border-gray-700/50">
                <span class="block text-xs text-gray-500 dark:text-gray-400">Microcontroller</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">ESP32 WROOM-32</span>
            </div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-[#2d2d44] border border-gray-100 dark:border-gray-700/50">
                <span class="block text-xs text-gray-500 dark:text-gray-400">Modul GPS</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">NEO-6M Module</span>
            </div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-[#2d2d44] border border-gray-100 dark:border-gray-700/50">
                <span class="block text-xs text-gray-500 dark:text-gray-400">Firmware Version</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">v1.0.4-beta</span>
            </div>
        </div>
    </div>

    <!-- 4. Terminal Log Sistem Real-time -->
    <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">terminal</span>
            <span>Konsol Log Perangkat</span>
        </h3>

        <div class="bg-gray-900 text-green-400 font-mono text-xs p-4 rounded-xl h-36 overflow-y-auto border border-gray-800 space-y-1">
            <p>[SYSTEM] WebSocket Server listening on 192.168.1.50:81</p>
            <p>[SYSTEM] GPS Module initialized. Lock acquired (8 Satellites).</p>
            <p>[SENSOR] IMU MPU6050 Online. Gyro calibrated successfully.</p>
            <p class="text-emerald-400">[CONNECT] Client Web Dashboard connected from 192.168.1.10</p>
        </div>
    </div>

</div>