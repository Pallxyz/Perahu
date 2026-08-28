<x-app-layout>
<<<<<<< HEAD
{{--
    ============================================================================
    CATATAN UNTUK DEVELOPER LAIN YANG BUKA FILE INI:
    ============================================================================
    UI ini dibuat mengikuti 4 mockup Stitch (control, telemetry_updated, map,
    system_dashboard_updated) - token warna, radius, dan komponen visual
    (Material Symbols, custom vertical slider, bottom nav) diambil PERSIS dari
    tailwind.config yang ada di file-file mockup tersebut.

    PERBEDAAN SENGAJA dari mockup asli (karena spek fungsional lu beda):
    1. Mockup Control cuma punya tombol "Turn Left/Turn Right" (nudge button),
       BUKAN slider steering. Spek lu minta steering SLIDER 0-180 derajat,
       jadi steering di sini dibuat slider vertical custom senada dengan
       speed slider - bukan tombol seperti mockup.
    2. Mockup Map pakai citra satelit (URL Google) + GPS Fix 3D + koordinat.
       Hardware USV lu TIDAK punya modul GPS, jadi bagian itu diganti jadi
       grid pattern dummy + radar ping (gaya visualnya tetap dari mockup,
       datanya jujur ditandai "belum ada GPS").
    3. Arsitektur data: mockup murni statis/dummy. Di sini semua angka
       (voltage, capacity, status, rssi, chart) diisi live dari WebSocket
       ESP32, dan histori disimpan/diambil dari Laravel (/api/battery-logs).
    ============================================================================
--}}

<div x-data="usvDashboard()" x-init="init()" class="min-h-screen bg-[#f8f9fa] font-[Inter] antialiased" style="--c-primary:#3525cd; --c-primary-container:#4f46e5; --c-on-primary-container:#dad7ff; --c-secondary:#006c49; --c-secondary-container:#6cf8bb; --c-on-secondary-container:#00714d; --c-error:#ba1a1a; --c-outline:#777587; --c-outline-variant:#c7c4d8; --c-surface-container-lowest:#ffffff; --c-surface-container-low:#f3f4f5; --c-surface-container:#edeeef; --c-surface-container-high:#e7e8e9; --c-surface-container-highest:#e1e3e4; --c-on-surface:#191c1d; --c-on-surface-variant:#464555;">

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .ambient-shadow { box-shadow: 0px 1px 3px rgba(0,0,0,0.1), 0px 1px 2px rgba(0,0,0,0.06); }
    .bg-map-pattern {
        background-color: #f0f1f2;
        background-image: linear-gradient(#e1e3e4 1px, transparent 1px), linear-gradient(90deg, #e1e3e4 1px, transparent 1px);
        background-size: 20px 20px;
    }
    @keyframes radar-ping { 0% { transform: scale(1); opacity: .8; } 100% { transform: scale(3); opacity: 0; } }
    .animate-radar { animation: radar-ping 2s cubic-bezier(0,0,0.2,1) infinite; }

    /* Custom vertical slider (dipakai untuk Speed & Steering, gaya sama seperti mockup Control) */
    .v-slider-track { width: 8px; border-radius: 9999px; background: var(--c-surface-container-highest); overflow: hidden; position: relative; }
    .v-slider-fill { position: absolute; bottom: 0; left: 0; width: 100%; background: var(--c-primary); transition: height .06s linear; }
    .v-slider-input { -webkit-appearance: slider-vertical; appearance: slider-vertical; position: absolute; inset: 0; width: 100%; opacity: 0; cursor: pointer; }
    .v-slider-thumb { position: absolute; left: 50%; transform: translateX(-50%); width: 32px; height: 32px; border-radius: 9999px; background: #fff; border: 2px solid var(--c-primary); box-shadow: 0 2px 4px rgba(0,0,0,.2); display: flex; align-items: center; justify-content: center; pointer-events: none; transition: bottom .06s linear; }
</style>

    {{-- ============ TOP APP BAR ============ --}}
    <header class="w-full sticky top-0 z-40 bg-[#f8f9fa] shadow-sm flex items-center justify-between px-4 md:px-8 h-16">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined" style="color:var(--c-primary); font-variation-settings:'FILL' 1;">directions_boat</span>
            <h1 class="text-2xl font-semibold" style="color:var(--c-primary);">BoatControl</h1>
        </div>

        {{-- Desktop nav --}}
        <nav class="hidden md:flex items-center gap-2">
            <button @click="activeTab='control'" class="px-3 py-2 rounded-lg text-sm font-semibold transition"
                    :style="activeTab==='control' ? 'color:var(--c-primary); background:var(--c-secondary-container);' : 'color:var(--c-on-surface-variant);'">Control</button>
            <button @click="activeTab='telemetry'" class="px-3 py-2 rounded-lg text-sm font-semibold transition"
                    :style="activeTab==='telemetry' ? 'color:var(--c-primary); background:var(--c-secondary-container);' : 'color:var(--c-on-surface-variant);'">Telemetry</button>
            <button @click="activeTab='map'" class="px-3 py-2 rounded-lg text-sm font-semibold transition"
                    :style="activeTab==='map' ? 'color:var(--c-primary); background:var(--c-secondary-container);' : 'color:var(--c-on-surface-variant);'">Map</button>
            <button @click="activeTab='system'" class="px-3 py-2 rounded-lg text-sm font-semibold transition"
                    :style="activeTab==='system' ? 'color:var(--c-primary); background:var(--c-secondary-container);' : 'color:var(--c-on-surface-variant);'">System</button>
        </nav>
    </header>

    {{-- ============ ESP32 CONNECTION BAR ============ --}}
    <div class="max-w-4xl mx-auto px-4 md:px-8 pt-4">
        <div class="bg-white rounded-xl ambient-shadow p-4 flex flex-col sm:flex-row sm:items-center gap-3 justify-between border" style="border-color:var(--c-surface-container-highest);">
            <div class="flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span x-show="wsConnected" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background:var(--c-secondary-container);"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3" :style="wsConnected ? 'background:var(--c-secondary)' : 'background:var(--c-error)'"></span>
                </span>
                <span class="text-sm font-medium" style="color:var(--c-on-surface-variant);" x-text="wsConnected ? 'Terhubung ke ' + esp32Ip : 'Tidak terhubung ke ESP32'"></span>
            </div>
            <div class="flex items-center gap-2">
                <input x-model="esp32Ip" type="text" placeholder="IP ESP32, mis. 192.168.1.50"
                       class="text-sm rounded-lg border focus:outline-none px-3 py-2 w-56" style="border-color:var(--c-outline-variant);">
                <button @click="connectWS()" class="text-sm px-4 py-2 rounded-lg text-white font-semibold" style="background:var(--c-primary);">Connect</button>
            </div>
        </div>
    </div>

    {{-- ============ MAIN CONTENT ============ --}}
    <main class="max-w-4xl mx-auto w-full px-4 md:px-8 py-6 pb-28 md:pb-10 flex flex-col gap-6">

    {{-- ===================== TAB 1: CONTROL ===================== --}}
    <section x-show="activeTab==='control'" class="flex flex-col gap-4">

        <div class="grid grid-cols-2 gap-4">
            <button @click="sendStart()" class="rounded-xl p-4 flex flex-col items-center justify-center gap-1 ambient-shadow h-24 text-white active:scale-95 transition"
                    style="background:var(--c-primary-container);">
                <span class="material-symbols-outlined text-[32px]">power_settings_new</span>
                <span class="text-base font-semibold">Start System</span>
            </button>
            <button @click="sendStop()" class="rounded-xl p-4 flex flex-col items-center justify-center gap-1 ambient-shadow h-24 bg-white border-2 active:scale-95 transition"
                    style="border-color:var(--c-error); color:var(--c-error);">
                <span class="material-symbols-outlined text-[32px]">stop_circle</span>
                <span class="text-base font-semibold">Emergency Stop</span>
            </button>
        </div>

        {{-- Dua slider vertikal berdampingan: Speed (kiri) & Steering (kanan) --}}
        <div class="bg-white rounded-xl ambient-shadow p-4 flex flex-col items-center gap-4">
            <div class="grid grid-cols-2 gap-4 w-full">

                {{-- SPEED SLIDER --}}
                <div class="flex flex-col items-center gap-2">
                    <h2 class="text-base font-semibold" style="color:var(--c-on-surface);">Forward Speed</h2>
                    <span class="text-sm" style="color:var(--c-outline);" x-text="speed"></span>
                    <div class="relative w-16 h-64 flex items-center justify-center">
                        <div class="v-slider-track h-64 absolute inset-y-0">
                            <div class="v-slider-fill" :style="'height:' + (speed/255*100) + '%'"></div>
                        </div>
                        <input type="range" min="0" max="255" x-model.number="speed"
                               @input="sendSpeed()" @change="sendSpeed()"
                               class="v-slider-input">
                        <div class="v-slider-thumb" :style="'bottom: calc(' + (speed/255*100) + '% - 16px)'">
                            <span class="material-symbols-outlined text-[16px] leading-none" style="color:var(--c-primary);">unfold_more</span>
                        </div>
                    </div>
                    <div class="flex justify-between w-full text-xs" style="color:var(--c-outline);">
                        <span>0</span><span>255</span>
                    </div>
                </div>

                {{-- STEERING SLIDER (custom, auto-center saat dilepas) --}}
                <div class="flex flex-col items-center gap-2">
                    <h2 class="text-base font-semibold" style="color:var(--c-on-surface);">Steering</h2>
                    <span class="text-sm" style="color:var(--c-outline);" x-text="steering + '\u00b0'"></span>
                    <div class="relative w-16 h-64 flex items-center justify-center">
                        <div class="v-slider-track h-64 absolute inset-y-0">
                            <div class="v-slider-fill" :style="'height:' + (steering/180*100) + '%'"></div>
                        </div>
                        <input type="range" min="0" max="180" x-model.number="steering"
                               @input="sendSteering()"
                               @mouseup="centerSteering()" @touchend="centerSteering()"
                               class="v-slider-input">
                        <div class="v-slider-thumb" :style="'bottom: calc(' + (steering/180*100) + '% - 16px)'">
                            <span class="material-symbols-outlined text-[16px] leading-none" style="color:var(--c-primary);">swap_vert</span>
                        </div>
                    </div>
                    <div class="flex justify-between w-full text-xs" style="color:var(--c-outline);">
                        <span>0&deg;</span><span>180&deg;</span>
                    </div>
                </div>
            </div>
            <p class="text-xs text-center" style="color:var(--c-outline);">Steering otomatis kembali ke 90&deg; saat dilepas.</p>
        </div>
    </section>

    {{-- ===================== TAB 2: TELEMETRY ===================== --}}
    <section x-show="activeTab==='telemetry'" class="flex flex-col gap-6">
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold" style="color:var(--c-on-surface);">Telemetry Overview</h2>
                <p class="text-sm mt-1" style="color:var(--c-on-surface-variant);">Live metrics dari USV.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex h-3 w-3 relative">
                    <span x-show="wsConnected" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background:var(--c-secondary-container);"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3" :style="wsConnected ? 'background:var(--c-secondary)' : 'background:var(--c-error)'"></span>
                </span>
                <span class="text-xs" style="color:var(--c-on-surface-variant);">Live Connection</span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl ambient-shadow p-6 h-40 flex flex-col justify-between border" style="border-color:var(--c-surface-container);">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider" style="color:var(--c-outline);">Battery Voltage</span>
                    <span class="material-symbols-outlined" style="color:var(--c-outline);">battery_charging_full</span>
                </div>
                <div>
                    <div class="text-3xl font-bold" style="color:var(--c-on-surface);" x-text="battery.voltage.toFixed(2) + ' V'"></div>
                    <div class="text-xs mt-1" style="color:var(--c-secondary);" x-text="battery.voltage >= 11.1 ? 'Nominal' : 'Rendah'"></div>
                </div>
            </div>
            <div class="bg-white rounded-xl ambient-shadow p-6 h-40 flex flex-col justify-between border" style="border-color:var(--c-surface-container);">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider" style="color:var(--c-outline);">Capacity</span>
                    <span class="material-symbols-outlined" style="color:var(--c-primary);">bolt</span>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2" style="color:var(--c-on-surface);" x-text="battery.percentage + '%'"></div>
                    <div class="w-full rounded-full h-1.5 overflow-hidden" style="background:var(--c-surface-container-high);">
                        <div class="h-1.5 rounded-full transition-all" :style="'width:' + battery.percentage + '%; background:var(--c-primary-container);'"></div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold mb-3" style="color:var(--c-on-surface);">Battery History</h3>
            <div class="bg-white rounded-xl ambient-shadow p-4 md:p-6 border" style="border-color:var(--c-surface-container);">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs" style="color:var(--c-on-surface-variant);">Voltage vs Time</span>
                    <span class="text-xs px-2 py-1 rounded-md" style="color:var(--c-primary); background:rgba(53,37,205,0.1);">Live</span>
                </div>
                <canvas id="batteryChart" height="90"></canvas>
            </div>
        </div>
    </section>

    {{-- ===================== TAB 3: MAP ===================== --}}
    <section x-show="activeTab==='map'" class="flex flex-col gap-3">
        <div class="bg-white rounded-xl ambient-shadow overflow-hidden border" style="border-color:var(--c-surface-container);">
            <div class="relative w-full h-[420px] bg-map-pattern">
                {{-- Overlay info (gaya sama seperti mockup, isi jujur: belum ada GPS) --}}
                <div class="absolute top-4 left-4 z-20">
                    <div class="bg-white rounded-xl ambient-shadow p-4 flex flex-col gap-2 border min-w-[220px]" style="border-color:var(--c-surface-container-highest);">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase tracking-wider" style="color:var(--c-on-surface-variant);">GPS Status</span>
                            <div class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full" style="background:var(--c-outline);"></span>
                                <span class="text-sm font-semibold" style="color:var(--c-outline);">No Module</span>
                            </div>
                        </div>
                        <div class="w-full h-px" style="background:var(--c-surface-container-highest);"></div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wider" style="color:var(--c-on-surface-variant);">Catatan</span>
                            <span class="text-sm" style="color:var(--c-on-surface);">Posisi statis - hardware belum pasang GPS</span>
                        </div>
                    </div>
                </div>

                {{-- USV marker + radar ping, style dari mockup --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20 flex items-center justify-center pointer-events-none">
                    <div class="absolute w-12 h-12 rounded-full animate-radar" style="background:rgba(53,37,205,0.2);"></div>
                    <div class="relative w-4 h-4 rounded-full border-2 border-white ambient-shadow z-10" style="background:var(--c-primary);"></div>
                </div>

                {{-- Zoom controls (dekoratif, sesuai mockup) --}}
                <div class="absolute right-4 bottom-4 z-20 flex flex-col gap-2">
                    <button class="w-10 h-10 bg-white rounded-xl ambient-shadow flex items-center justify-center border" style="border-color:var(--c-surface-container-highest); color:var(--c-on-surface-variant);">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                    </button>
                    <button class="w-10 h-10 bg-white rounded-xl ambient-shadow flex items-center justify-center border" style="border-color:var(--c-surface-container-highest); color:var(--c-on-surface-variant);">
                        <span class="material-symbols-outlined text-[20px]">remove</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== TAB 4: SYSTEM ===================== --}}
    <section x-show="activeTab==='system'" class="flex flex-col gap-6">
        <header>
            <h2 class="text-2xl font-bold" style="color:var(--c-on-surface);">System Diagnostics</h2>
            <p class="text-sm mt-1" style="color:var(--c-on-surface-variant);">Status hardware &amp; konektivitas real-time.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl ambient-shadow p-4 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs" style="color:var(--c-on-surface-variant);">Operational Status</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold"
                          :style="battery.status === 'NORMAL' ? 'background:var(--c-secondary); color:#fff;' : 'background:var(--c-error); color:#fff;'"
                          x-text="battery.status || 'UNKNOWN'"></span>
                </div>
                <p class="text-sm" style="color:var(--c-on-surface-variant);">Dikirim langsung oleh firmware ESP32 lewat WebSocket.</p>
            </div>

            <div class="bg-white rounded-xl ambient-shadow p-4 flex flex-col justify-between">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-xs" style="color:var(--c-on-surface-variant);">WiFi Network</h3>
                    <span class="material-symbols-outlined" style="color:var(--c-outline);">wifi</span>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-lg font-medium" style="color:var(--c-on-surface);" x-text="battery.rssi + ' dBm'"></span>
                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-full" style="background:rgba(0,108,73,0.1);">
                        <div class="w-2 h-2 rounded-full" :style="wsConnected ? 'background:var(--c-secondary)' : 'background:var(--c-error)'"></div>
                        <span class="text-xs font-bold" :style="wsConnected ? 'color:var(--c-secondary)' : 'color:var(--c-error)'" x-text="wsConnected ? 'Connected' : 'Disconnected'"></span>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-xs" style="color:var(--c-outline);" x-text="'Last Sync: ' + lastSync"></p>
    </section>

    </main>

    {{-- ============ BOTTOM NAV (MOBILE) - sesuai mockup persis ============ --}}
    <nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] border-t" style="border-color:var(--c-surface-container-high);">
        <button @click="activeTab='control'" class="flex flex-col items-center justify-center rounded-xl px-4 py-1 transition"
                :style="activeTab==='control' ? 'background:var(--c-secondary-container); color:var(--c-on-secondary-container);' : 'color:var(--c-on-surface-variant);'">
            <span class="material-symbols-outlined">settings_remote</span>
            <span class="text-xs mt-1">Control</span>
        </button>
        <button @click="activeTab='telemetry'" class="flex flex-col items-center justify-center rounded-xl px-4 py-1 transition"
                :style="activeTab==='telemetry' ? 'background:var(--c-secondary-container); color:var(--c-on-secondary-container);' : 'color:var(--c-on-surface-variant);'">
            <span class="material-symbols-outlined">analytics</span>
            <span class="text-xs mt-1">Telemetry</span>
        </button>
        <button @click="activeTab='map'" class="flex flex-col items-center justify-center rounded-xl px-4 py-1 transition"
                :style="activeTab==='map' ? 'background:var(--c-secondary-container); color:var(--c-on-secondary-container);' : 'color:var(--c-on-surface-variant);'">
            <span class="material-symbols-outlined">map</span>
            <span class="text-xs mt-1">Map</span>
        </button>
        <button @click="activeTab='system'" class="flex flex-col items-center justify-center rounded-xl px-4 py-1 transition"
                :style="activeTab==='system' ? 'background:var(--c-secondary-container); color:var(--c-on-secondary-container);' : 'color:var(--c-on-surface-variant);'">
            <span class="material-symbols-outlined">settings_input_component</span>
            <span class="text-xs mt-1">System</span>
        </button>
    </nav>
</div>

<script>
function usvDashboard() {
    return {
        activeTab: 'control',
        esp32Ip: localStorage.getItem('esp32_ip') || '192.168.1.50',
        ws: null,
        wsConnected: false,
        speed: 0,
        steering: 90,
        battery: { voltage: 0, percentage: 0, status: '', rssi: 0 },
        chart: null,
        lastSync: 'Belum ada data',

        init() {
            this.setupChart();
            this.loadHistory();
            if (this.esp32Ip) this.connectWS();
        },

        connectWS() {
            if (this.ws) { this.ws.close(); }
            localStorage.setItem('esp32_ip', this.esp32Ip);
            this.ws = new WebSocket(`ws://${this.esp32Ip}:81/`);

            this.ws.onopen  = () => { this.wsConnected = true; };
            this.ws.onclose = () => { this.wsConnected = false; };
            this.ws.onerror = () => { this.wsConnected = false; };

            this.ws.onmessage = (event) => {
                let data;
                try { data = JSON.parse(event.data); } catch (e) { return; }
                if (data.v === undefined) return;

                this.battery.voltage = data.v;
                this.battery.percentage = data.p;
                this.battery.status = data.status;
                this.battery.rssi = data.rssi;
                this.lastSync = new Date().toLocaleTimeString();

                this.pushChartPoint(data.v);
                this.logBatteryToServer(data);
            };
        },

        sendCmd(obj) {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                this.ws.send(JSON.stringify(obj));
            }
        },

        sendStart()    { this.sendCmd({ cmd: 'start' }); },
        sendStop()     { this.sendCmd({ cmd: 'stop' }); },
        sendSpeed()    { this.sendCmd({ cmd: 'speed', value: this.speed }); },
        sendSteering() { this.sendCmd({ cmd: 'steer', value: this.steering }); },
        centerSteering() {
            this.steering = 90;
            this.sendSteering();
        },

        async logBatteryToServer(data) {
            try {
                await fetch('/api/battery-logs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        voltage: data.v,
                        percentage: data.p,
                        status: data.status,
                        rssi: data.rssi,
                    }),
                });
            } catch (e) {
                console.warn('Gagal kirim log baterai ke server:', e);
            }
        },

        async loadHistory() {
            try {
                const res = await fetch('/api/battery-logs?limit=50', { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (json.success) {
                    json.data.forEach(log => this.pushChartPoint(log.voltage, log.created_at));
                }
            } catch (e) {
                console.warn('Gagal memuat histori baterai:', e);
            }
        },

        setupChart() {
            const ctx = document.getElementById('batteryChart');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Voltage (V)',
                        data: [],
                        borderColor: '#3525cd',
                        backgroundColor: 'rgba(53,37,205,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0,
                        borderWidth: 2.5,
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { suggestedMin: 9, suggestedMax: 13 } },
                    plugins: { legend: { display: false } }
                }
            });
        },

        pushChartPoint(voltage, label) {
            if (!this.chart) return;
            const ds = this.chart.data;
            ds.labels.push(label ? new Date(label).toLocaleTimeString() : new Date().toLocaleTimeString());
            ds.datasets[0].data.push(voltage);
            if (ds.labels.length > 50) {
                ds.labels.shift();
                ds.datasets[0].data.shift();
            }
            this.chart.update('none');
        },
    };
}
</script>
</x-app-layout>
=======
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
>>>>>>> 26bce98a8f8cefe8587581fa40afc965acb43053
