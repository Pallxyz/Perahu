<div x-data="{ 
    controlMode: 'wheel', // 'wheel' (Mode Putar) | 'dpad' (Mode D-Pad)
    leftHanded: false, // false = roda/D-Pad di KANAN (default) | true = roda/D-Pad di KIRI
    throttle: 0, 
    maxThrottle: 100,
    steering: 90, 
    wheelAngle: 0, // Sudut visual roda kemudi (-90° kiri penuh s/d +90° kanan penuh)
    isDraggingWheel: false,
    startDragAngle: 0,
    initialWheelAngle: 0,
    gear: 'NEUTRAL',
    engineOn: false,
    cruiseControl: false, // Kunci kecepatan otomatis
    navLightOn: false,    // Status lampu navigasi kapal
    hornActive: false,    // Status klakson/buzzer
    gasInterval: null,
    autoCenterInterval: null,
    lastSentThrottle: -1,
    lastSentSteering: -1,
    mapInitialized: false,
    trailCoords: [],

    // --- EFEK GETAR HALUS (HP) ---
    vibrate(ms = 20) {
        if (navigator.vibrate) navigator.vibrate(ms);
    },

    // --- INISIALISASI PETA LEAFLET + JEJAK LINTASAN ---
    initMap() {
        if (this.mapInitialized) return;
        const mapEl = document.getElementById('map');
        if (!mapEl) return;
        
        const lat = parseFloat(this.telemetry?.lat || -6.7320);
        const lng = parseFloat(this.telemetry?.lng || 108.5523);

        window.leafletMap = L.map('map', {
            zoomControl: false
        }).setView([lat, lng], 16);

        L.control.zoom({ position: 'bottomright' }).addTo(window.leafletMap);

        const lightTile = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OSM'
        });

        const darkTile = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            attribution: '&copy; OSM &copy; CARTO'
        });

        const updateMapTheme = () => {
            if (document.documentElement.classList.contains('dark')) {
                if (window.leafletMap.hasLayer(lightTile)) window.leafletMap.removeLayer(lightTile);
                if (!window.leafletMap.hasLayer(darkTile)) darkTile.addTo(window.leafletMap);
            } else {
                if (window.leafletMap.hasLayer(darkTile)) window.leafletMap.removeLayer(darkTile);
                if (!window.leafletMap.hasLayer(lightTile)) lightTile.addTo(window.leafletMap);
            }
        };

        updateMapTheme();

        // Marker USV
        window.usvMarker = L.marker([lat, lng]).addTo(window.leafletMap)
            .bindPopup('<b>USV NauTech</b><br>Status: Ready');

        // Garis Jejak Lintasan (Polyline)
        this.trailCoords = [[lat, lng]];
        window.usvTrail = L.polyline(this.trailCoords, {
            color: '#6366f1',
            weight: 4,
            opacity: 0.8,
            dashArray: '6, 6'
        }).addTo(window.leafletMap);

        const observer = new MutationObserver(updateMapTheme);
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        window.addEventListener('resize', () => {
            if (window.leafletMap) window.leafletMap.invalidateSize();
        });

        this.mapInitialized = true;
    },

    centerMapOnBoat() {
        if (!window.leafletMap || !window.usvMarker) return;
        const pos = window.usvMarker.getLatLng();
        window.leafletMap.flyTo(pos, 17, { duration: 0.6 });
    },

    clearMapTrail() {
        if (!window.usvTrail || !window.usvMarker) return;
        const pos = window.usvMarker.getLatLng();
        this.trailCoords = [[pos.lat, pos.lng]];
        window.usvTrail.setLatLngs(this.trailCoords);
    },

    // Helper aman: hanya kirim perintah ke ESP32 jika WebSocket sudah terhubung
    dispatchCommand(payload) {
        if (typeof wsConnected !== 'undefined' && wsConnected && typeof sendCommand === 'function') {
            sendCommand(payload);
        }
    },

    // Sinkronisasi perintah Throttle ke kapal
    syncThrottle() {
        const val = Math.round(this.throttle);
        if (val !== this.lastSentThrottle) {
            this.lastSentThrottle = val;
            this.dispatchCommand({ type: 'SET_THROTTLE', value: val, gear: this.gear });
        }
    },

    // Sinkronisasi perintah Steering ke kapal
    syncSteering() {
        const prevSteering = this.steering;
        this.steering = Math.round(90 + this.wheelAngle);
        if (prevSteering !== 90 && this.steering === 90) {
            this.vibrate(15);
        }
        if (this.steering !== this.lastSentSteering) {
            this.lastSentSteering = this.steering;
            this.dispatchCommand({ type: 'SET_STEERING', value: this.steering });
        }
    },

    // --- TOMBOL START / STOP ---
    toggleEngine() {
        this.vibrate(35);
        if (!this.engineOn) {
            this.engineOn = true;
            this.dispatchCommand({ type: 'ENGINE', action: 'START' });
        } else {
            this.emergencyStop();
        }
    },

    // --- CRUISE CONTROL (KUNCI KECEPATAN) ---
    toggleCruise() {
        if (!this.engineOn) return;
        this.vibrate(25);
        if (!this.cruiseControl && this.gear === 'FORWARD' && this.throttle > 0) {
            this.cruiseControl = true;
            clearInterval(this.gasInterval);
        } else {
            this.cruiseControl = false;
            this.stopDrive();
        }
    },

    // --- LAMPU NAVIGASI & KLAKSON ---
    toggleNavLight() {
        this.vibrate(20);
        this.navLightOn = !this.navLightOn;
        this.dispatchCommand({ type: 'SET_LIGHT', state: this.navLightOn ? 'ON' : 'OFF' });
    },

    triggerHorn(active) {
        if (active && !this.hornActive) this.vibrate(40);
        this.hornActive = active;
        this.dispatchCommand({ type: 'HORN', state: active ? 'ON' : 'OFF' });
    },

    // --- LOGIKA RODA KEMUDI (STEERING WHEEL) ---
    getPointerAngle(e, el) {
        const rect = el.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        return Math.atan2(e.clientY - centerY, e.clientX - centerX) * (180 / Math.PI);
    },

    startWheelDrag(e) {
        clearInterval(this.autoCenterInterval);
        this.isDraggingWheel = true;
        e.currentTarget.setPointerCapture(e.pointerId);
        this.startDragAngle = this.getPointerAngle(e, this.$refs.steeringWheel);
        this.initialWheelAngle = this.wheelAngle;
    },

    onWheelDrag(e) {
        if (!this.isDraggingWheel) return;
        const currentAngle = this.getPointerAngle(e, this.$refs.steeringWheel);
        let delta = currentAngle - this.startDragAngle;
        
        if (delta > 180) delta -= 360;
        if (delta < -180) delta += 360;

        this.wheelAngle = Math.max(-90, Math.min(90, this.initialWheelAngle + delta));
        this.syncSteering();
    },

    endWheelDrag() {
        this.isDraggingWheel = false;
        clearInterval(this.autoCenterInterval);
        if (this.wheelAngle === 0) return;

        this.autoCenterInterval = setInterval(() => {
            if (Math.abs(this.wheelAngle) <= 4) {
                this.wheelAngle = 0;
                this.syncSteering();
                clearInterval(this.autoCenterInterval);
            } else {
                this.wheelAngle += this.wheelAngle > 0 ? -5 : 5;
                this.syncSteering();
            }
        }, 30);
    },

    // --- LOGIKA BELOK VIA D-PAD / KEYBOARD ---
    startDpadSteer(direction) {
        if (!this.engineOn) return;
        clearInterval(this.autoCenterInterval);
        const targetAngle = direction === 'LEFT' ? -90 : 90;
        
        this.autoCenterInterval = setInterval(() => {
            if (Math.abs(targetAngle - this.wheelAngle) <= 8) {
                this.wheelAngle = targetAngle;
                this.syncSteering();
                clearInterval(this.autoCenterInterval);
            } else {
                this.wheelAngle += targetAngle > this.wheelAngle ? 8 : -8;
                this.syncSteering();
            }
        }, 25);
    },

    stopDpadSteer() {
        this.endWheelDrag();
    },

    // --- LOGIKA GAS & MUNDUR ---
    startDrive(targetGear) {
        if (!this.engineOn) return;
        if (targetGear === 'REVERSE') this.cruiseControl = false;
        
        clearInterval(this.gasInterval);
        this.gear = targetGear;
        
        this.gasInterval = setInterval(() => {
            if (this.throttle < this.maxThrottle) {
                this.throttle = Math.min(this.maxThrottle, this.throttle + 5);
                this.syncThrottle();
            }
        }, 50);
    },

    stopDrive() {
        if (this.cruiseControl && this.gear === 'FORWARD') return;
        clearInterval(this.gasInterval);
        this.gasInterval = setInterval(() => {
            if (this.throttle > 0) {
                this.throttle = Math.max(0, this.throttle - 10);
                this.syncThrottle();
            } else {
                this.gear = 'NEUTRAL';
                this.syncThrottle();
                clearInterval(this.gasInterval);
            }
        }, 50);
    },

    emergencyStop() {
        clearInterval(this.gasInterval);
        clearInterval(this.autoCenterInterval);
        this.engineOn = false;
        this.cruiseControl = false;
        this.throttle = 0;
        this.wheelAngle = 0;
        this.steering = 90;
        this.gear = 'NEUTRAL';
        this.syncThrottle();
        this.syncSteering();
        this.dispatchCommand({ type: 'EMERGENCY_STOP' });
    }
}" 
@keydown.window="
    if (['INPUT', 'TEXTAREA'].includes($event.target.tagName) || $event.repeat) return;
    const k = $event.key.toLowerCase();
    if (k === 'w' || k === 'arrowup') { $event.preventDefault(); startDrive('FORWARD'); }
    if (k === 's' || k === 'arrowdown') { $event.preventDefault(); startDrive('REVERSE'); }
    if (k === 'a' || k === 'arrowleft') { $event.preventDefault(); startDpadSteer('LEFT'); }
    if (k === 'd' || k === 'arrowright') { $event.preventDefault(); startDpadSteer('RIGHT'); }
    if (k === 'h') { triggerHorn(true); }
"
@keyup.window="
    const k = $event.key.toLowerCase();
    if (['w', 'arrowup', 's', 'arrowdown'].includes(k)) stopDrive();
    if (['a', 'arrowleft', 'd', 'arrowright'].includes(k)) stopDpadSteer();
    if (k === 'h') { triggerHorn(false); }
"
x-init="
    try { leftHanded = localStorage.getItem('nautech_left_handed') === '1'; } catch (e) {}
    $watch('leftHanded', v => { try { localStorage.setItem('nautech_left_handed', v ? '1' : '0'); } catch (e) {} });

    if (typeof activeTab === 'undefined' || activeTab === 'control') {
        setTimeout(() => {
            initMap();
            if (window.leafletMap) window.leafletMap.invalidateSize();
        }, 100);
    }

    $watch('activeTab', value => {
        if (value === 'control') {
            setTimeout(() => {
                initMap();
                if (window.leafletMap) {
                    window.leafletMap.invalidateSize();
                }
            }, 50);
        }
    });
"
class="select-none">

    <!-- KOKPIT KONTROL TERPADU -->
    <div class="bg-white dark:bg-[#1a1a2e] text-gray-800 dark:text-white border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm transition-colors duration-300 overflow-hidden">
        
        <!-- Header Kokpit: Switcher Mode, Posisi Kiri/Kanan, Lampu, Klakson & Status Mesin -->
        <div class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-50/80 dark:bg-[#141425] border-b border-gray-200 dark:border-gray-800 flex flex-wrap items-center justify-between gap-2">
            
            <!-- Switcher 2 Mode (Mode Putar vs Mode D-Pad) -->
            <div class="flex items-center gap-1 bg-white dark:bg-[#1a1a2e] p-1 rounded-xl border border-gray-200 dark:border-gray-700">
                <button type="button"
                        @click="controlMode = 'wheel'"
                        :class="controlMode === 'wheel' 
                            ? 'bg-indigo-600 text-white shadow-sm' 
                            : 'text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white'"
                        class="flex items-center gap-1 py-1 px-2.5 rounded-lg text-[11px] sm:text-xs font-bold tracking-wide transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-sm sm:text-base">trip_origin</span>
                    <span>Putar</span>
                </button>

                <button type="button"
                        @click="controlMode = 'dpad'"
                        :class="controlMode === 'dpad' 
                            ? 'bg-indigo-600 text-white shadow-sm' 
                            : 'text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white'"
                        class="flex items-center gap-1 py-1 px-2.5 rounded-lg text-[11px] sm:text-xs font-bold tracking-wide transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-sm sm:text-base">gamepad</span>
                    <span>D-Pad</span>
                </button>
            </div>

            <!-- Tombol Cepat: Switch Kiri/Kanan, Lampu Navigasi, Klakson & Status Mesin -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                <!-- Switch Slide: Posisi Roda / D-Pad Kiri atau Kanan -->
                <button type="button"
                        role="switch"
                        :aria-checked="leftHanded.toString()"
                        @click="leftHanded = !leftHanded"
                        :class="leftHanded
                            ? 'bg-indigo-600/15 border-indigo-500/40 text-indigo-600 dark:text-indigo-300'
                            : 'bg-white dark:bg-[#1a1a2e] border-gray-200 dark:border-gray-700 text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800'"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border text-[11px] sm:text-xs font-bold transition-all cursor-pointer"
                        title="Posisi roda kemudi / D-Pad: geser untuk pindah kiri atau kanan">
                    <span class="material-symbols-outlined text-sm sm:text-base" x-text="leftHanded ? 'swipe_left' : 'swipe_right'"></span>
                    <span class="hidden sm:inline" x-text="leftHanded ? 'Kiri' : 'Kanan'"></span>
                    <span class="relative inline-block w-8 h-4 rounded-full transition-colors duration-200"
                          :class="leftHanded ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                        <span class="absolute top-0.5 left-0.5 w-3 h-3 rounded-full bg-white shadow transition-transform duration-200"
                              :class="leftHanded ? 'translate-x-0' : 'translate-x-4'"></span>
                    </span>
                </button>

                <!-- Tombol Lampu Kapal -->
                <button type="button"
                        @click="toggleNavLight()"
                        :class="navLightOn 
                            ? 'bg-amber-500/15 border-amber-500/40 text-amber-600 dark:text-amber-400' 
                            : 'bg-white dark:bg-[#1a1a2e] border-gray-200 dark:border-gray-700 text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800'"
                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-xl border text-[11px] sm:text-xs font-bold transition-all cursor-pointer"
                        title="Lampu Navigasi Kapal">
                    <span class="material-symbols-outlined text-sm sm:text-base" x-text="navLightOn ? 'lightbulb' : 'light_off'"></span>
                    <span class="hidden sm:inline" x-text="navLightOn ? 'Lampu ON' : 'Lampu OFF'"></span>
                </button>

                <!-- Tombol Klakson / Buzzer -->
                <button type="button"
                        @pointerdown.prevent="triggerHorn(true)"
                        @pointerup.prevent="triggerHorn(false)"
                        @pointerleave.prevent="triggerHorn(false)"
                        @pointercancel.prevent="triggerHorn(false)"
                        :class="hornActive 
                            ? 'bg-indigo-600 border-indigo-500 text-white scale-95 shadow-md' 
                            : 'bg-white dark:bg-[#1a1a2e] border-gray-200 dark:border-gray-700 text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800'"
                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-xl border text-[11px] sm:text-xs font-bold transition-all touch-none cursor-pointer"
                        title="Klakson Kapal (Tombol H)">
                    <span class="material-symbols-outlined text-sm sm:text-base">campaign</span>
                    <span class="hidden sm:inline">Klakson</span>
                </button>

                <!-- Indikator Status Mesin -->
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border text-[11px] sm:text-xs font-bold transition-colors"
                     :class="engineOn 
                        ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400' 
                        : 'bg-white dark:bg-[#1a1a2e] border-gray-200 dark:border-gray-700 text-slate-500'">
                    <span class="w-2 h-2 rounded-full" :class="engineOn ? 'bg-emerald-500 animate-ping' : 'bg-slate-400'"></span>
                    <span x-text="engineOn ? 'AKTIF' : 'MATI'"></span>
                </div>
            </div>
        </div>

        <!-- Body Kokpit:
             Mobile: Peta Mini (atas) -> Instrumen & Pedal Gas/Mundur (tengah, saling tukar jika leftHanded) -> Roda Kemudi / D-Pad (bawah)
             Desktop (md+): Kolom Kiri (4 col) & Kolom Kanan (4 col) bertukar posisi saat leftHanded aktif, Tengah (4 col): Peta Mini + Instrumen -->
        <div class="p-3 sm:p-4 grid grid-cols-2 md:grid-cols-12 gap-3 sm:gap-4 items-stretch">
            
            <!-- 1. PETA NAVIGASI KAPAL -->
            <div class="col-span-2 md:col-span-4 md:col-start-5 order-1 md:order-2 bg-slate-50/70 dark:bg-[#121222]/70 border border-gray-200 dark:border-gray-800 rounded-xl p-2 flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1 mb-1.5 px-0.5">
                    <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-sm">location_on</span>
                        <span class="font-bold text-gray-700 dark:text-gray-200 text-[11px]">Peta</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button"
                                @click="centerMapOnBoat()"
                                class="p-0.5 rounded bg-white dark:bg-[#1a1a2e] hover:text-indigo-600 dark:hover:text-indigo-400 text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-gray-700 transition-colors cursor-pointer"
                                title="Fokus ke Kapal">
                            <span class="material-symbols-outlined text-[13px] block">my_location</span>
                        </button>
                        <button type="button"
                                @click="clearMapTrail()"
                                class="p-0.5 rounded bg-white dark:bg-[#1a1a2e] hover:text-rose-600 dark:hover:text-rose-400 text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-gray-700 transition-colors cursor-pointer"
                                title="Reset Jejak Lintasan">
                            <span class="material-symbols-outlined text-[13px] block">route</span>
                        </button>
                        <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                              :class="typeof wsConnected !== 'undefined' && wsConnected && telemetry?.satellites > 0 
                                  ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400' 
                                  : 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400'"
                              x-text="typeof wsConnected !== 'undefined' && wsConnected && telemetry?.satellites > 0 ? telemetry.satellites + ' Sat' : 'No GPS'">
                        </span>
                    </div>
                </div>
                <!-- Container Peta Mini -->
                <div id="map" class="w-full h-[105px] sm:h-[115px] rounded-lg border border-gray-200 dark:border-gray-700 z-10"></div>
            </div>

            <!-- 2. INSTRUMEN KAPAL / NETRAL -->
            <div :class="leftHanded ? 'order-3 md:order-3' : 'order-2 md:order-3'"
                 class="col-span-1 md:col-span-4 md:col-start-5 flex flex-col justify-between bg-slate-50 dark:bg-[#121222] border border-gray-200 dark:border-gray-800 rounded-xl p-2.5 transition-colors">
                
                <!-- Baris Atas: Badge Netral/Gear & Angka Kecepatan Sejajar -->
                <div class="w-full flex items-center justify-between gap-1 mb-1">
                    <span class="px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-black tracking-wider"
                          :class="{
                              'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30': gear === 'FORWARD',
                              'bg-gray-200/80 dark:bg-slate-800 text-gray-600 dark:text-slate-300': gear === 'NEUTRAL',
                              'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30': gear === 'REVERSE'
                          }"
                          x-text="gear === 'FORWARD' ? 'MAJU (D)' : (gear === 'REVERSE' ? 'MUNDUR (R)' : 'NETRAL (N)')">
                    </span>

                    <div class="text-right leading-none">
                        <span class="text-xl sm:text-2xl font-black tracking-tight text-gray-900 dark:text-white" x-text="throttle"></span>
                        <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">%</span>
                    </div>
                </div>

                <!-- Bar Kecepatan Tipis -->
                <div class="w-full h-1.5 bg-gray-200 dark:bg-slate-800 rounded-full overflow-hidden my-1">
                    <div class="h-full transition-all duration-75 rounded-full"
                         :class="gear === 'REVERSE' ? 'bg-amber-500' : 'bg-emerald-500'"
                         :style="`width: ${throttle}%`"></div>
                </div>

                <!-- Visual Rudder Bar Ringkas -->
                <div class="w-full mt-1.5 pt-1.5 border-t border-gray-200 dark:border-gray-800">
                    <div class="flex justify-between items-center text-[10px] text-gray-500 dark:text-slate-400 mb-1">
                        <span>Kemudi</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400" 
                              x-text="steering === 90 ? '90° (Lurus)' : (steering < 90 ? steering + '° (Kiri)' : steering + '° (Kanan)')"></span>
                    </div>
                    <div class="relative w-full h-1.5 bg-gray-200 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="absolute left-1/2 top-0 bottom-0 w-0.5 bg-gray-400 dark:bg-slate-600 -translate-x-1/2 z-10"></div>
                        <div class="absolute top-0 bottom-0 w-2.5 bg-indigo-600 dark:bg-indigo-400 rounded-full transition-all duration-75 -translate-x-1/2"
                             :style="`left: ${(steering / 180) * 100}%`"></div>
                    </div>
                </div>

                <!-- Max Power Limiter Ringkas -->
                <div class="w-full mt-1.5 pt-1.5 border-t border-gray-200 dark:border-gray-800">
                    <div class="flex justify-between text-[10px] text-gray-500 dark:text-slate-400 mb-0.5">
                        <span>Max Power</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="maxThrottle + '%'"></span>
                    </div>
                    <input type="range" min="20" max="100" step="10" x-model.number="maxThrottle"
                           class="w-full h-1 bg-gray-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-indigo-600 dark:accent-indigo-500">
                </div>
            </div>

            <!-- 3. PEDAL GAS & MUNDUR / KONTROL MESIN -->
            <div :class="leftHanded ? 'md:col-start-9 order-2 md:order-4' : 'md:col-start-1 order-3 md:order-1'"
                 class="col-span-1 md:col-span-4 md:row-start-1 md:row-span-2 flex flex-col items-center justify-center bg-slate-50/70 dark:bg-[#121222]/70 border border-gray-100 dark:border-gray-800 rounded-2xl p-2.5 sm:p-4">
                
                <!-- OPSI A: PEDAL GAS, MUNDUR & CRUISE LOCK (Mode Putar) -->
                <div x-show="controlMode === 'wheel'" class="w-full flex flex-col items-center">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-400 mb-2 sm:mb-3 text-center"
                          x-text="engineOn ? 'Pedal (Tahan)' : 'Tekan START'"></span>
                    
                    <div class="flex items-end justify-center gap-2 sm:gap-3 w-full" :class="!engineOn ? 'opacity-60' : ''">
                        <!-- Pedal Mundur -->
                        <button type="button"
                                :disabled="!engineOn"
                                @pointerdown.prevent="startDrive('REVERSE')"
                                @pointerup.prevent="stopDrive()"
                                @pointerleave.prevent="stopDrive()"
                                @pointercancel.prevent="stopDrive()"
                                :class="gear === 'REVERSE' && throttle > 0 
                                    ? 'bg-amber-500 text-white border-amber-400 scale-95 shadow-lg shadow-amber-500/30' 
                                    : 'bg-white dark:bg-[#1a1a2e] text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                class="flex-1 max-w-[96px] h-20 sm:h-28 rounded-2xl border-2 flex flex-col items-center justify-center transition-all duration-75 touch-none cursor-pointer disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-2xl sm:text-3xl">keyboard_double_arrow_down</span>
                            <span class="text-[10px] sm:text-xs font-bold tracking-wider mt-1">MUNDUR</span>
                        </button>

                        <!-- Pedal Gas -->
                        <button type="button"
                                :disabled="!engineOn"
                                @pointerdown.prevent="startDrive('FORWARD')"
                                @pointerup.prevent="stopDrive()"
                                @pointerleave.prevent="stopDrive()"
                                @pointercancel.prevent="stopDrive()"
                                :class="gear === 'FORWARD' && throttle > 0 
                                    ? 'bg-emerald-600 text-white border-emerald-400 scale-95 shadow-lg shadow-emerald-500/30' 
                                    : 'bg-white dark:bg-[#1a1a2e] text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                class="flex-1 max-w-[112px] h-28 sm:h-36 rounded-2xl border-2 flex flex-col items-center justify-center transition-all duration-75 touch-none cursor-pointer disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-3xl sm:text-4xl">keyboard_double_arrow_up</span>
                            <span class="text-xs sm:text-sm font-black tracking-wider mt-1">GAS</span>
                        </button>
                    </div>

                    <!-- Tombol Cruise Control (Tahan Gas Otomatis) -->
                    <button type="button"
                            :disabled="!engineOn || (gear !== 'FORWARD' && !cruiseControl)"
                            @click="toggleCruise()"
                            :class="cruiseControl 
                                ? 'bg-indigo-600 text-white border-indigo-400 shadow-md shadow-indigo-500/30' 
                                : 'bg-white dark:bg-[#1a1a2e] text-gray-600 dark:text-slate-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                            class="mt-2 sm:mt-3.5 w-full py-1.5 sm:py-2 px-2 rounded-xl border text-[10px] sm:text-xs font-bold flex items-center justify-center gap-1 transition-all cursor-pointer disabled:opacity-45 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-sm sm:text-base" x-text="cruiseControl ? 'lock' : 'lock_open'"></span>
                        <span x-text="cruiseControl ? 'Cruise (' + throttle + '%)' : 'Cruise Lock'"></span>
                    </button>
                </div>

                <!-- OPSI B: TOMBOL START/STOP & PRESET POWER (Mode D-Pad) -->
                <div x-show="controlMode === 'dpad'" style="display: none;" class="w-full flex flex-col items-center justify-between h-full gap-2">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-400">Mesin</span>
                    
                    <!-- Tombol Start / Stop Besar -->
                    <button type="button"
                            @click="toggleEngine()"
                            :class="engineOn 
                                ? 'bg-red-600 hover:bg-red-500 border-red-400 shadow-lg shadow-red-500/30' 
                                : 'bg-emerald-600 hover:bg-emerald-500 border-emerald-400 shadow-lg shadow-emerald-500/30'"
                            class="w-full py-2.5 sm:py-3.5 px-2.5 rounded-2xl border-2 flex items-center justify-center gap-1.5 text-white font-black tracking-wider text-xs sm:text-sm transition-all active:scale-95 cursor-pointer">
                        <span class="material-symbols-outlined text-xl sm:text-2xl" x-text="engineOn ? 'stop_circle' : 'power_settings_new'"></span>
                        <span x-text="engineOn ? 'STOP' : 'START'"></span>
                    </button>

                    <!-- Tombol Cruise Control di Mode D-Pad -->
                    <button type="button"
                            :disabled="!engineOn || (gear !== 'FORWARD' && !cruiseControl)"
                            @click="toggleCruise()"
                            :class="cruiseControl 
                                ? 'bg-indigo-600 text-white border-indigo-400 shadow-md' 
                                : 'bg-white dark:bg-[#1a1a2e] text-gray-600 dark:text-slate-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                            class="w-full py-1.5 sm:py-2 px-2 rounded-xl border text-[10px] sm:text-xs font-bold flex items-center justify-center gap-1 transition-all cursor-pointer disabled:opacity-45 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-sm sm:text-base" x-text="cruiseControl ? 'lock' : 'lock_open'"></span>
                        <span x-text="cruiseControl ? 'Cruise (' + throttle + '%)' : 'Cruise Lock'"></span>
                    </button>

                    <!-- Preset Batas Kecepatan -->
                    <div class="w-full pt-1.5 border-t border-gray-200 dark:border-gray-800">
                        <span class="block text-center text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-400 mb-1">Power</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1">
                            <template x-for="preset in [25, 50, 75, 100]" :key="preset">
                                <button type="button"
                                        @click="maxThrottle = preset"
                                        :class="maxThrottle === preset 
                                            ? 'bg-indigo-600 text-white border-indigo-500' 
                                            : 'bg-white dark:bg-[#1a1a2e] text-gray-600 dark:text-slate-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                        class="py-1 rounded-lg border text-[10px] sm:text-xs font-bold transition-colors cursor-pointer"
                                        x-text="preset + '%'">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

            <!-- 4. RODA KEMUDI / D-PAD -->
            <div :class="leftHanded ? 'md:col-start-1 md:order-1' : 'md:col-start-9 md:order-4'"
                 class="col-span-2 md:col-span-4 md:row-start-1 md:row-span-2 order-4 flex flex-col items-center justify-center bg-slate-50/70 dark:bg-[#121222]/70 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 transition-colors">
                
                <!-- TAMPILAN 1: RODA KEMUDI KAPAL (Mode Putar) -->
                <div x-show="controlMode === 'wheel'" class="flex flex-col items-center justify-center">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-400 mb-3">Putar Kemudi</span>
                    
                    <div x-ref="steeringWheel"
                         @pointerdown="startWheelDrag($event)"
                         @pointermove="onWheelDrag($event)"
                         @pointerup="endWheelDrag()"
                         @pointercancel="endWheelDrag()"
                         class="relative w-48 h-48 sm:w-52 sm:h-52 flex items-center justify-center cursor-grab active:cursor-grabbing touch-none">
                        
                        <!-- Ring Penanda Sudut Luar -->
                        <div class="absolute inset-0 rounded-full border-2 border-dashed border-gray-300 dark:border-slate-700/80 pointer-events-none"></div>
                        <div class="absolute -top-2 w-1.5 h-3 bg-indigo-600 dark:bg-indigo-500 rounded-full pointer-events-none"></div>

                        <!-- Visual Roda Kemudi Kapal (SVG) yang Berputar -->
                        <svg viewBox="0 0 200 200" 
                             :style="`transform: rotate(${wheelAngle}deg)`"
                             class="w-44 h-44 sm:w-48 sm:h-48 drop-shadow-[0_8px_12px_rgba(79,70,229,0.18)] dark:drop-shadow-[0_10px_15px_rgba(0,0,0,0.6)] pointer-events-none transition-transform duration-75 ease-out">
                            
                            <!-- Pegangan Luar Roda Kemudi (8 Spokes) -->
                            <g class="stroke-slate-400 dark:stroke-slate-600" stroke-width="8" stroke-linecap="round">
                                <line x1="100" y1="8" x2="100" y2="192" />
                                <line x1="8" y1="100" x2="192" y2="100" />
                                <line x1="35" y1="35" x2="165" y2="165" />
                                <line x1="165" y1="35" x2="35" y2="165" />
                            </g>

                            <!-- Lingkaran Utama Setir -->
                            <circle cx="100" cy="100" r="72" fill="none" class="stroke-slate-700 dark:stroke-slate-800" stroke-width="18" />
                            <circle cx="100" cy="100" r="72" fill="none" stroke="#6366f1" stroke-width="3" stroke-opacity="0.75" />

                            <!-- Penanda Atas Roda Kemudi -->
                            <rect x="94" y="16" width="12" height="22" rx="4" fill="#f43f5e" />

                            <!-- Dudukan Poros Tengah -->
                            <circle cx="100" cy="100" r="32" class="fill-white dark:fill-slate-900 stroke-gray-300 dark:stroke-slate-700" stroke-width="3" />
                        </svg>

                        <!-- TOMBOL START / STOP DI TENGAH RODA -->
                        <button type="button"
                                @pointerdown.stop
                                @click="toggleEngine()"
                                :class="engineOn 
                                    ? 'bg-red-600 hover:bg-red-500 border-red-300 dark:border-red-400 shadow-[0_0_20px_rgba(239,68,68,0.5)]' 
                                    : 'bg-emerald-600 hover:bg-emerald-500 border-emerald-300 dark:border-emerald-400 shadow-[0_0_20px_rgba(16,185,129,0.45)]'"
                                class="absolute z-10 w-16 h-16 sm:w-[72px] sm:h-[72px] rounded-full border-2 flex flex-col items-center justify-center text-white transition-all active:scale-90 cursor-pointer">
                            <span class="material-symbols-outlined text-xl leading-none" 
                                  x-text="engineOn ? 'stop_circle' : 'power_settings_new'"></span>
                            <span class="text-[10px] font-black tracking-wider mt-0.5 uppercase" 
                                  x-text="engineOn ? 'STOP' : 'START'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAMPILAN 2: D-PAD NAVIGASI BESAR (Mode D-Pad) -->
                <div x-show="controlMode === 'dpad'" style="display: none;" class="flex flex-col items-center justify-center">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-400 mb-3"
                          x-text="engineOn ? 'Navigasi (Tahan)' : 'Nyalakan Mesin'"></span>
                    
                    <div class="grid grid-cols-3 gap-2 w-48 h-48 sm:w-52 sm:h-52" :class="!engineOn ? 'opacity-55' : ''">
                        <div></div>
                        <!-- D-Pad Atas (Maju) -->
                        <button type="button"
                                :disabled="!engineOn"
                                @pointerdown.prevent="startDrive('FORWARD')"
                                @pointerup.prevent="stopDrive()"
                                @pointerleave.prevent="stopDrive()"
                                @pointercancel.prevent="stopDrive()"
                                :class="gear === 'FORWARD' && throttle > 0 
                                    ? 'bg-emerald-600 text-white border-emerald-400 scale-95 shadow-lg shadow-emerald-500/30' 
                                    : 'bg-white dark:bg-[#1a1a2e] text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                class="rounded-2xl border-2 flex flex-col items-center justify-center transition-all duration-75 touch-none cursor-pointer disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-3xl">keyboard_arrow_up</span>
                            <span class="text-[10px] font-black tracking-wider">MAJU</span>
                        </button>
                        <div></div>

                        <!-- D-Pad Kiri (Belok Kiri) -->
                        <button type="button"
                                :disabled="!engineOn"
                                @pointerdown.prevent="startDpadSteer('LEFT')"
                                @pointerup.prevent="stopDpadSteer()"
                                @pointerleave.prevent="stopDpadSteer()"
                                @pointercancel.prevent="stopDpadSteer()"
                                :class="wheelAngle < -10 
                                    ? 'bg-indigo-600 text-white border-indigo-400 scale-95 shadow-lg shadow-indigo-500/30' 
                                    : 'bg-white dark:bg-[#1a1a2e] text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                class="rounded-2xl border-2 flex flex-col items-center justify-center transition-all duration-75 touch-none cursor-pointer disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-3xl">keyboard_arrow_left</span>
                            <span class="text-[10px] font-black tracking-wider">KIRI</span>
                        </button>

                        <!-- Pusat D-Pad -->
                        <div class="rounded-2xl bg-gray-100/80 dark:bg-slate-800/70 border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-indigo-500/70 text-xl">navigation</span>
                        </div>

                        <!-- D-Pad Kanan (Belok Kanan) -->
                        <button type="button"
                                :disabled="!engineOn"
                                @pointerdown.prevent="startDpadSteer('RIGHT')"
                                @pointerup.prevent="stopDpadSteer()"
                                @pointerleave.prevent="stopDpadSteer()"
                                @pointercancel.prevent="stopDpadSteer()"
                                :class="wheelAngle > 10 
                                    ? 'bg-indigo-600 text-white border-indigo-400 scale-95 shadow-lg shadow-indigo-500/30' 
                                    : 'bg-white dark:bg-[#1a1a2e] text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                class="rounded-2xl border-2 flex flex-col items-center justify-center transition-all duration-75 touch-none cursor-pointer disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-3xl">keyboard_arrow_right</span>
                            <span class="text-[10px] font-black tracking-wider">KANAN</span>
                        </button>

                        <div></div>
                        <!-- D-Pad Bawah (Mundur) -->
                        <button type="button"
                                :disabled="!engineOn"
                                @pointerdown.prevent="startDrive('REVERSE')"
                                @pointerup.prevent="stopDrive()"
                                @pointerleave.prevent="stopDrive()"
                                @pointercancel.prevent="stopDrive()"
                                :class="gear === 'REVERSE' && throttle > 0 
                                    ? 'bg-amber-500 text-white border-amber-400 scale-95 shadow-lg shadow-amber-500/30' 
                                    : 'bg-white dark:bg-[#1a1a2e] text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                class="rounded-2xl border-2 flex flex-col items-center justify-center transition-all duration-75 touch-none cursor-pointer disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-3xl">keyboard_arrow_down</span>
                            <span class="text-[10px] font-black tracking-wider">MUNDUR</span>
                        </button>
                        <div></div>
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>