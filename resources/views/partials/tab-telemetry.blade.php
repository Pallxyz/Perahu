<div class="flex flex-col gap-6">
    
    <!-- 1. Ringkasan Parameter Telemetri Utama -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Tegangan Baterai -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm transition-colors duration-300">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tegangan Baterai</span>
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1" 
                 x-text="(telemetry?.voltage ?? '0.0') + ' V'"></div>
        </div>

        <!-- Kapasitas Sisa -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm transition-colors duration-300">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kapasitas Sisa</span>
            <div class="text-2xl font-bold text-emerald-500 mt-1" 
                 x-text="(telemetry?.batteryPercentage ?? 0) + ' %'"></div>
        </div>

        <!-- Kecepatan Kapal (SOG) -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm transition-colors duration-300">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kecepatan (Speed)</span>
            <div class="text-2xl font-bold text-sky-500 mt-1" 
                 x-text="(telemetry?.speed ?? '0.0') + ' Knot'"></div>
        </div>

        <!-- Sinyal Wi-Fi (RSSI) -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm transition-colors duration-300">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Sinyal Wi-Fi (RSSI)</span>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1" 
                 x-text="wsConnected ? (telemetry?.rssi ?? 0) + ' dBm' : 'Offline'"></div>
        </div>
    </div>

    <!-- 2. Detail Sensor IMU (Kemudi & Navigasi) & GPS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Sensor Orientasi (IMU / MPU6050) -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm transition-colors duration-300">
            <h4 class="font-bold text-gray-800 dark:text-gray-100 text-sm mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500 text-base">explore</span>
                <span>Orientasi & Kompas (IMU)</span>
            </h4>
            <div class="grid grid-cols-3 gap-2 text-center text-sm">
                <div class="p-2 bg-gray-50 dark:bg-[#121222] rounded-xl border border-gray-100 dark:border-gray-800">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400">Heading</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200" x-text="(telemetry?.heading ?? 0) + '°'"></span>
                </div>
                <div class="p-2 bg-gray-50 dark:bg-[#121222] rounded-xl border border-gray-100 dark:border-gray-800">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400">Pitch</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200" x-text="(telemetry?.pitch ?? 0) + '°'"></span>
                </div>
                <div class="p-2 bg-gray-50 dark:bg-[#121222] rounded-xl border border-gray-100 dark:border-gray-800">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400">Roll</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200" x-text="(telemetry?.roll ?? 0) + '°'"></span>
                </div>
            </div>
        </div>

        <!-- Sensor GPS (NEO-6M) - Sinkron dengan Map di Tab Control -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm transition-colors duration-300">
            <h4 class="font-bold text-gray-800 dark:text-gray-100 text-sm mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500 text-base">my_location</span>
                <span>Sinyal & Lokasi GPS</span>
            </h4>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="p-2 bg-gray-50 dark:bg-[#121222] rounded-xl border border-gray-100 dark:border-gray-800">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400">Jumlah Satelit</span>
                    <span class="font-bold"
                          :class="wsConnected && (telemetry?.satellites ?? 0) > 0 ? 'text-emerald-500' : 'text-red-500'"
                          x-text="wsConnected && (telemetry?.satellites ?? 0) > 0 ? telemetry.satellites + ' Sat (Locked)' : '0 Sat (No Fix)'"></span>
                </div>
                <div class="p-2 bg-gray-50 dark:bg-[#121222] rounded-xl border border-gray-100 dark:border-gray-800">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400">Koordinat (Lat, Lng)</span>
                    <span class="font-mono text-xs font-semibold text-gray-800 dark:text-gray-200"
                          x-text="(telemetry?.lat || '-6.7320') + ', ' + (telemetry?.lng || '108.5523')"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Grafik Tegangan Baterai Real-time -->
    <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-base flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500">show_chart</span>
                <span>Grafik Tegangan Baterai (Real-time)</span>
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="'Update terakhir: ' + (lastSync || '-')"></span>
        </div>
        <div class="w-full h-64">
            <canvas id="batteryChart"></canvas>
        </div>
    </div>

</div>