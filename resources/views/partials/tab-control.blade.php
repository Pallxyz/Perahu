<div x-data="{ 
    throttle: 0, 
    steering: 90, 
    gear: 'FORWARD',
    mode: 'MANUAL',
    throttleTimer: null,

    // Fungsi pengirim throttle yang smooth tanpa konfirmasi
    updateThrottle(val) {
        this.throttle = val;
        clearTimeout(this.throttleTimer);
        this.throttleTimer = setTimeout(() => {
            if (typeof sendCommand === 'function') {
                sendCommand({ type: 'SET_THROTTLE', value: parseInt(this.throttle), gear: this.gear });
            }
        }, 50); // Delay 50ms agar pengiriman smooth
    }
}" class="space-y-6">

    <!-- Mode Operasi & Kontrol Utama -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Status Engine & Mode -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-4">Mode Operasi</h3>
            <div class="flex gap-2">
                <button type="button" 
                        @click="mode = 'MANUAL'; sendCommand({ type: 'SET_MODE', value: 'MANUAL' })"
                        :class="mode === 'MANUAL' ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 dark:bg-[#2d2d44] text-gray-700 dark:text-gray-300'"
                        class="flex-1 py-2.5 px-3 rounded-xl text-sm transition text-center">
                    Manual
                </button>
                <button type="button" 
                        @click="mode = 'AUTONOMOUS'; sendCommand({ type: 'SET_MODE', value: 'AUTO' })"
                        :class="mode === 'AUTONOMOUS' ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 dark:bg-[#2d2d44] text-gray-700 dark:text-gray-300'"
                        class="flex-1 py-2.5 px-3 rounded-xl text-sm transition text-center">
                    Autonomous
                </button>
            </div>
        </div>

        <!-- Tombol Aksi Cepat / Mesin Utama -->
        <div class="md:col-span-2 bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-4">Kontrol Mesin Utama</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button type="button" 
                        @click="sendCommand({ type: 'ENGINE', action: 'START' })"
                        class="flex items-center justify-center gap-2 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-semibold rounded-xl transition shadow-md shadow-emerald-600/20">
                    <span class="material-symbols-outlined text-xl">play_arrow</span>
                    <span>Start Engine</span>
                </button>
                
                <button type="button" 
                        @click="updateThrottle(0); sendCommand({ type: 'EMERGENCY_STOP' })"
                        class="flex items-center justify-center gap-2 py-3 px-4 bg-red-600 hover:bg-red-700 active:scale-95 text-white font-semibold rounded-xl transition shadow-md shadow-red-600/20">
                    <span class="material-symbols-outlined text-xl">stop</span>
                    <span>Emergency Stop</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Sliders Section (Throttle & Steering) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Throttle Card -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800 dark:text-gray-100">Throttle (Kecepatan)</h3>
                
                <!-- Transmission Switch -->
                <div class="flex gap-1 bg-gray-100 dark:bg-[#2d2d44] p-1 rounded-xl text-xs">
                    <button type="button" @click="gear = 'FORWARD'; updateThrottle(throttle)"
                            :class="gear === 'FORWARD' ? 'bg-indigo-600 text-white font-bold' : 'text-gray-600 dark:text-gray-400'"
                            class="px-2.5 py-1 rounded-lg transition">Maju</button>
                    <button type="button" @click="gear = 'NEUTRAL'; updateThrottle(0)"
                            :class="gear === 'NEUTRAL' ? 'bg-amber-600 text-white font-bold' : 'text-gray-600 dark:text-gray-400'"
                            class="px-2.5 py-1 rounded-lg transition">N</button>
                    <button type="button" @click="gear = 'REVERSE'; updateThrottle(throttle)"
                            :class="gear === 'REVERSE' ? 'bg-indigo-600 text-white font-bold' : 'text-gray-600 dark:text-gray-400'"
                            class="px-2.5 py-1 rounded-lg transition">Mundur</button>
                </div>
            </div>
            
            <div class="flex flex-col items-center justify-center h-48">
                <!-- Slider langsung responsif tanpa konfirmasi -->
                <input type="range" 
                       min="0" 
                       max="100" 
                       :value="throttle"
                       @input="updateThrottle($event.target.value)"
                       class="w-2 h-36 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-indigo-600 dark:accent-indigo-400 [writing-mode:vertical-lr] [direction:rtl]">
                <span class="mt-4 font-bold text-indigo-600 dark:text-indigo-400 text-xl" x-text="throttle + '%'"></span>
            </div>
        </div>

        <!-- Steering Card -->
        <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between transition-colors duration-300">
            <div>
                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-center mb-6">Kemudi Servo (Steering)</h3>
                <div class="px-4">
                    <input type="range" 
                           min="0" 
                           max="180" 
                           x-model="steering"
                           @input="sendCommand({ type: 'SET_STEERING', value: parseInt(steering) })"
                           class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-indigo-600 dark:accent-indigo-400">
                    
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-3">
                        <span>0° (Kiri)</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-base" x-text="steering + '°'"></span>
                        <span>180° (Kanan)</span>
                    </div>
                </div>
            </div>

            <!-- Quick Buttons Steering -->
            <div class="flex justify-center gap-2 mt-6">
                <button type="button" 
                        @click="steering = 45; sendCommand({ type: 'SET_STEERING', value: 45 })"
                        class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-[#2d2d44] hover:border-indigo-500 transition">
                    45° Kiri
                </button>
                <button type="button" 
                        @click="steering = 90; sendCommand({ type: 'SET_STEERING', value: 90 })"
                        class="flex items-center gap-1.5 px-4 py-1.5 border border-indigo-500/30 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-semibold hover:bg-indigo-600 hover:text-white transition">
                    <span class="material-symbols-outlined text-base">settings</span>
                    <span>Center (90°)</span>
                </button>
                <button type="button" 
                        @click="steering = 135; sendCommand({ type: 'SET_STEERING', value: 135 })"
                        class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-[#2d2d44] hover:border-indigo-500 transition">
                    135° Kanan
                </button>
            </div>
        </div>

    </div>
</div>