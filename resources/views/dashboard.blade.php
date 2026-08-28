<x-layouts.usv>
{{--
    ============================================================================
    KERANGKA DASHBOARD - file ini cuma berisi shell (header, connection bar,
    nav, bottom nav) + state Alpine.js. Isi tiap tab ada di file terpisah
    di folder partials/, di-include di bawah:
      - partials/tab-control.blade.php
      - partials/tab-telemetry.blade.php
      - partials/tab-map.blade.php
      - partials/tab-system.blade.php
    Semua partial itu WAJIB dibuka lewat @include di sini, karena mereka
    pakai variabel Alpine (activeTab, speed, steering, battery, dst) yang
    didefinisikan oleh x-data="usvDashboard()" di bawah - tidak jalan
    kalau dibuka berdiri sendiri.
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

    .v-slider-track { width: 8px; border-radius: 9999px; background: var(--c-surface-container-highest); overflow: hidden; position: relative; }
    .v-slider-fill { position: absolute; bottom: 0; left: 0; width: 100%; background: var(--c-primary); transition: height .06s linear; }
    .v-slider-input { -webkit-appearance: slider-vertical; appearance: slider-vertical; position: absolute; inset: 0; width: 100%; opacity: 0; cursor: pointer; }
    .v-slider-thumb { position: absolute; left: 50%; transform: translateX(-50%); width: 32px; height: 32px; border-radius: 9999px; background: #fff; border: 2px solid var(--c-primary); box-shadow: 0 2px 4px rgba(0,0,0,.2); display: flex; align-items: center; justify-content: center; pointer-events: none; transition: bottom .06s linear; }
</style>

    <header class="w-full sticky top-0 z-40 bg-[#f8f9fa] shadow-sm flex items-center justify-between px-4 md:px-8 h-16">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined" style="color:var(--c-primary); font-variation-settings:'FILL' 1;">directions_boat</span>
            <h1 class="text-2xl font-semibold" style="color:var(--c-primary);">BoatControl</h1>
        </div>

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

    {{-- ============ MAIN CONTENT - 4 TAB DI-INCLUDE DARI PARTIALS ============ --}}
    <main class="max-w-4xl mx-auto w-full px-4 md:px-8 py-6 pb-28 md:pb-10 flex flex-col gap-6">
        @include('partials.tab-control')
        @include('partials.tab-telemetry')
        @include('partials.tab-map')
        @include('partials.tab-system')
    </main>

    {{-- ============ BOTTOM NAV (MOBILE) ============ --}}
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
</x-layouts.usv>