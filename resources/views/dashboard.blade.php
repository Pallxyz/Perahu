<x-app-layout>
    <div x-data="{
        activeTab: 'control',
        sidebarOpen: false,
        themeDropdownOpen: false,
        profileDropdownOpen: false,
        currentTheme: localStorage.getItem('theme') || 'auto',
    
        // --- WEBSOCKET & ESP32 IP MANAGEMENT ---
        esp32Ip: localStorage.getItem('esp32_ip') || '192.168.1.50',
        wsConnected: false,
        ws: null,
        lastSync: '-',
    
        // Object penampung data telemetri real-time dari ESP32
        telemetry: {
            voltage: '0.0',
            batteryPercentage: 0,
            speed: '0.0',
            rssi: 0,
            heading: 0,
            pitch: 0,
            roll: 0,
            satellites: 0,
            lat: '-6.2088',
            lng: '106.8456'
        },
    
        batteryChart: null,
    
        connectWS() {
            if (this.ws) {
                this.ws.close();
            }
    
            localStorage.setItem('esp32_ip', this.esp32Ip);
    
            try {
                this.ws = new WebSocket(`ws://${this.esp32Ip}:81`);
    
                this.ws.onopen = () => {
                    this.wsConnected = true;
                    console.log('Terhubung ke ESP32!');
                };
    
                this.ws.onclose = () => {
                    this.wsConnected = false;
                    console.log('Koneksi ESP32 Terputus');
                };
    
                this.ws.onerror = (err) => {
                    this.wsConnected = false;
                    console.error('Error WebSocket:', err);
                };
    
                this.ws.onmessage = (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        this.telemetry = { ...this.telemetry, ...data };
    
                        const now = new Date();
                        this.lastSync = now.toLocaleTimeString();
    
                        if (data.voltage && this.batteryChart) {
                            this.updateChart(this.lastSync, data.voltage);
                        }
                    } catch (e) {
                        console.log('Pesan dari ESP32 (Non-JSON):', event.data);
                    }
                };
            } catch (e) {
                this.wsConnected = false;
                alert('Gagal menghubungkan ke IP tersebut!');
            }
        },
    
        sendCommand(command) {
            if (this.ws && this.wsConnected) {
                this.ws.send(JSON.stringify(command));
            } else {
                alert('ESP32 Belum Terhubung!');
            }
        },
    
        initChart() {
            const ctx = document.getElementById('batteryChart')?.getContext('2d');
            if (!ctx) return;
    
            this.batteryChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Tegangan Baterai (V)',
                        data: [],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: false, suggestedMin: 10, suggestedMax: 13 }
                    }
                }
            });
        },
    
        updateChart(timeLabel, voltageVal) {
            if (!this.batteryChart) return;
    
            if (this.batteryChart.data.labels.length >= 10) {
                this.batteryChart.data.labels.shift();
                this.batteryChart.data.datasets[0].data.shift();
            }
    
            this.batteryChart.data.labels.push(timeLabel);
            this.batteryChart.data.datasets[0].data.push(voltageVal);
            this.batteryChart.update();
        },
    
        setTheme(theme) {
            this.currentTheme = theme;
            localStorage.setItem('theme', theme);
            this.applyTheme(theme);
            this.themeDropdownOpen = false;
        },
        applyTheme(theme) {
            const html = document.documentElement;
            html.classList.remove('light', 'dark');
            if (theme === 'light') {
                html.classList.add('light');
            } else if (theme === 'dark') {
                html.classList.add('dark');
            } else if (theme === 'auto') {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    html.classList.add('dark');
                } else {
                    html.classList.add('light');
                }
            }
        }
    }" x-init="applyTheme(currentTheme);
    connectWS();
    setTimeout(() => initChart(), 500);"
        class="min-h-screen bg-slate-100 dark:bg-[#0f0f1a] text-gray-800 dark:text-gray-100 transition-colors duration-300">

        <div class="flex flex-col md:flex-row min-h-screen relative">

            <!-- SIDEBAR NAVIGASI DI SEBELAH KIRI -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
                class="fixed md:static top-0 left-0 h-full md:h-auto w-64 bg-white dark:bg-[#1a1a2e] border-r border-gray-200 dark:border-gray-800 p-5 flex flex-col justify-between shrink-0 transition-transform duration-300 z-50">
                <div>
                    <!-- Tombol Close (khusus mobile) -->
                    <div class="flex justify-end mb-2 md:hidden">
                        <button @click="sidebarOpen = false"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Brand / Logo -->
                    <div class="flex items-center gap-3 px-2 mb-8">
                        <span
                            class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-3xl">directions_boat</span>
                        <span class="font-bold text-gray-800 dark:text-white text-xl tracking-wide">NauTech</span>
                    </div>

                    <!-- Navigation Links Vertikal -->
                    <nav class="space-y-2">
                        <button @click="activeTab = 'control'; sidebarOpen = false"
                            :class="activeTab === 'control' ?
                                'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' :
                                'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-indigo-600 dark:hover:text-indigo-400'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200">
                            <i class="bi bi-sliders text-lg"></i>
                            <span>Control</span>
                        </button>

                        <button @click="activeTab = 'telemetry'; sidebarOpen = false"
                            :class="activeTab === 'telemetry' ?
                                'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' :
                                'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-indigo-600 dark:hover:text-indigo-400'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200">
                            <i class="bi bi-activity text-lg"></i>
                            <span>Telemetry</span>
                        </button>

                        <button @click="activeTab = 'map'; sidebarOpen = false"
                            :class="activeTab === 'map' ?
                                'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' :
                                'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-indigo-600 dark:hover:text-indigo-400'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200">
                            <i class="bi bi-geo-alt text-lg"></i>
                            <span>Map</span>
                        </button>

                        <button @click="activeTab = 'system'; sidebarOpen = false"
                            :class="activeTab === 'system' ?
                                'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20' :
                                'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-indigo-600 dark:hover:text-indigo-400'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200">
                            <i class="bi bi-cpu text-lg"></i>
                            <span>System</span>
                        </button>
                    </nav>
                </div>

                <!-- Footer Sidebar (Theme Switcher & User Profile) -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-800 mt-6 space-y-3">

                    <!-- Theme Switcher Dropdown -->
                    <div class="relative" @click.outside="themeDropdownOpen = false">
                        <button @click="themeDropdownOpen = !themeDropdownOpen"
                            class="w-full flex items-center justify-between px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#2d2d44] text-gray-700 dark:text-gray-200 hover:border-indigo-500 transition text-sm">
                            <span class="flex items-center gap-2">
                                <i class="bi"
                                    :class="{
                                        'bi-sun-fill': currentTheme === 'light',
                                        'bi-moon-fill': currentTheme === 'dark',
                                        'bi-circle-half': currentTheme === 'auto'
                                    }"></i>
                                <span class="capitalize" x-text="currentTheme + ' Mode'"></span>
                            </span>
                            <i class="bi bi-chevron-expand"></i>
                        </button>

                        <div x-show="themeDropdownOpen" x-cloak x-transition
                            class="absolute bottom-12 left-0 w-full bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden z-50 text-sm">
                            <button @click="setTheme('light')"
                                :class="currentTheme === 'light' ?
                                    'text-indigo-600 dark:text-indigo-400 font-semibold bg-gray-50 dark:bg-gray-800/50' :
                                    'text-gray-700 dark:text-gray-300'"
                                class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <span class="flex items-center gap-2"><i class="bi bi-sun-fill"></i> Light</span>
                                <i x-show="currentTheme === 'light'" class="bi bi-check-lg"></i>
                            </button>
                            <button @click="setTheme('dark')"
                                :class="currentTheme === 'dark' ?
                                    'text-indigo-600 dark:text-indigo-400 font-semibold bg-gray-50 dark:bg-gray-800/50' :
                                    'text-gray-700 dark:text-gray-300'"
                                class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <span class="flex items-center gap-2"><i class="bi bi-moon-fill"></i> Dark</span>
                                <i x-show="currentTheme === 'dark'" class="bi bi-check-lg"></i>
                            </button>
                            <button @click="setTheme('auto')"
                                :class="currentTheme === 'auto' ?
                                    'text-indigo-600 dark:text-indigo-400 font-semibold bg-gray-50 dark:bg-gray-800/50' :
                                    'text-gray-700 dark:text-gray-300'"
                                class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <span class="flex items-center gap-2"><i class="bi bi-circle-half"></i> Auto</span>
                                <i x-show="currentTheme === 'auto'" class="bi bi-check-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- User Profile Dropdown (Pengganti Navbar Atas) -->
                    <div class="relative" @click.outside="profileDropdownOpen = false">
                        <button @click="profileDropdownOpen = !profileDropdownOpen"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-gray-100 dark:bg-[#252542] text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-[#2d2d50] transition text-sm">
                            <span class="flex items-center gap-2.5 truncate">
                                <i class="bi bi-person-circle text-lg text-indigo-500"></i>
                                <span class="font-medium truncate">{{ Auth::user()->name }}</span>
                            </span>
                            <i class="bi bi-chevron-up text-xs"></i>
                        </button>

                        <div x-show="profileDropdownOpen" x-cloak x-transition
                            class="absolute bottom-14 left-0 w-full bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden z-50 text-sm py-1">

                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <i class="bi bi-person"></i>
                                <span>Profile</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition text-left">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Log Out</span>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </aside>

            <!-- Overlay saat sidebar terbuka di mobile -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 z-40 md:hidden">
            </div>

            <!-- MAIN CONTENT AREA DIBAGIAN KANAN -->
            <main class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-6xl mx-auto">

                    <!-- Mobile Header dengan Tombol Hamburger -->
                    <div class="flex items-center justify-between mb-4 md:hidden">
                        <div class="flex items-center gap-2">
                            <span
                                class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-2xl">directions_boat</span>
                            <span class="font-bold text-gray-800 dark:text-white text-lg">NauTech</span>
                        </div>
                        <button @click="sidebarOpen = true"
                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-200">
                            <i class="bi bi-list text-xl"></i>
                        </button>
                    </div>

                    <!-- Connection Bar -->
                    <div
                        class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 transition-colors duration-300">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full"
                                :class="wsConnected ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300"
                                x-text="wsConnected ? 'Terhubung ke ESP32' : 'Terputus dari ESP32'"></span>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <input x-model="esp32Ip" type="text" placeholder="IP ESP32"
                                class="text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#2d2d44] text-gray-800 dark:text-gray-100 px-3 py-1.5 w-full sm:w-56 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            <button type="button" @click="connectWS()"
                                class="text-sm px-4 py-1.5 rounded-xl text-white font-semibold bg-indigo-600 hover:bg-indigo-700 transition whitespace-nowrap">Connect
                                & Simpan</button>
                        </div>
                    </div>

                    <!-- Area Partials (Tampil Sesuai Tab Aktif) -->
                    <div x-show="activeTab === 'control'" x-cloak>
                        @include('partials.tab-control')
                    </div>

                    <div x-show="activeTab === 'telemetry'" x-cloak>
                        @include('partials.tab-telemetry')
                    </div>

                    <div x-show="activeTab === 'map'" x-cloak>
                        @include('partials.tab-map')
                    </div>

                    <div x-show="activeTab === 'system'" x-cloak>
                        @include('partials.tab-system')
                    </div>

                </div>
            </main>

        </div>
    </div>
</x-app-layout>
