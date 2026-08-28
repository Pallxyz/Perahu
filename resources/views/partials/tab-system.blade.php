{{--
    TAB 4: SYSTEM
    Status operasional & sinyal WiFi - keduanya diambil langsung dari
    payload JSON terakhir yang dikirim ESP32 lewat WebSocket (variabel
    'battery.status' dan 'battery.rssi' di state Alpine induk).
--}}
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