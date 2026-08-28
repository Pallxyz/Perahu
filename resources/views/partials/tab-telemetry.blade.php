{{--
    TAB 2: TELEMETRY
    Kartu Voltage & Capacity + Chart.js untuk histori tegangan baterai.
    Elemen id="batteryChart" di-init oleh setupChart() di JS induk,
    jadi id ini JANGAN diubah tanpa ikut ubah dashboard.blade.php.
--}}
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