<x-app-layout>
    <div x-data="{ 
        themeDropdownOpen: false,
        profileDropdownOpen: false,
        currentTheme: localStorage.getItem('theme') || 'auto',

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
    }" 
    x-init="applyTheme(currentTheme)"
    class="min-h-screen bg-slate-100 dark:bg-[#0f0f1a] text-gray-800 dark:text-gray-100 transition-colors duration-300">

        <div class="flex flex-col md:flex-row min-h-screen">

            <!-- SIDEBAR -->
            <aside class="w-full md:w-64 bg-white dark:bg-[#1a1a2e] border-r border-gray-200 dark:border-gray-800 p-5 flex flex-col justify-between shrink-0 transition-colors duration-300">
                <div>
                    <div class="flex items-center gap-3 px-2 mb-8">
                        <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-3xl">directions_boat</span>
                        <span class="font-bold text-gray-800 dark:text-white text-xl tracking-wide">NauTech</span>
                    </div>

                    <nav class="space-y-2">
                        <a href="{{ route('dashboard') }}" 
                           class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-indigo-600 dark:hover:text-indigo-400">
                            <i class="bi bi-arrow-left-circle text-lg"></i>
                            <span>Kembali ke Dashboard</span>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200 bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-500/20">
                            <i class="bi bi-person-gear text-lg"></i>
                            <span>Profile</span>
                        </a>
                    </nav>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-800 mt-6 space-y-3">

                    <div class="relative" @click.outside="themeDropdownOpen = false">
                        <button @click="themeDropdownOpen = !themeDropdownOpen" 
                                class="w-full flex items-center justify-between px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#2d2d44] text-gray-700 dark:text-gray-200 hover:border-indigo-500 transition text-sm">
                            <span class="flex items-center gap-2">
                                <i class="bi" :class="{
                                    'bi-sun-fill': currentTheme === 'light',
                                    'bi-moon-fill': currentTheme === 'dark',
                                    'bi-circle-half': currentTheme === 'auto'
                                }"></i>
                                <span class="capitalize" x-text="currentTheme + ' Mode'"></span>
                            </span>
                            <i class="bi bi-chevron-expand"></i>
                        </button>

                        <div x-show="themeDropdownOpen" 
                             x-cloak 
                             x-transition
                             class="absolute bottom-12 left-0 w-full bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden z-50 text-sm">
                            <button @click="setTheme('light')" 
                                    :class="currentTheme === 'light' ? 'text-indigo-600 dark:text-indigo-400 font-semibold bg-gray-50 dark:bg-gray-800/50' : 'text-gray-700 dark:text-gray-300'"
                                    class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <span class="flex items-center gap-2"><i class="bi bi-sun-fill"></i> Light</span>
                                <i x-show="currentTheme === 'light'" class="bi bi-check-lg"></i>
                            </button>
                            <button @click="setTheme('dark')" 
                                    :class="currentTheme === 'dark' ? 'text-indigo-600 dark:text-indigo-400 font-semibold bg-gray-50 dark:bg-gray-800/50' : 'text-gray-700 dark:text-gray-300'"
                                    class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <span class="flex items-center gap-2"><i class="bi bi-moon-fill"></i> Dark</span>
                                <i x-show="currentTheme === 'dark'" class="bi bi-check-lg"></i>
                            </button>
                            <button @click="setTheme('auto')" 
                                    :class="currentTheme === 'auto' ? 'text-indigo-600 dark:text-indigo-400 font-semibold bg-gray-50 dark:bg-gray-800/50' : 'text-gray-700 dark:text-gray-300'"
                                    class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <span class="flex items-center gap-2"><i class="bi bi-circle-half"></i> Auto</span>
                                <i x-show="currentTheme === 'auto'" class="bi bi-check-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="relative" @click.outside="profileDropdownOpen = false">
                        <button @click="profileDropdownOpen = !profileDropdownOpen" 
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-gray-100 dark:bg-[#252542] text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-[#2d2d50] transition text-sm">
                            <span class="flex items-center gap-2.5 truncate">
                                <i class="bi bi-person-circle text-lg text-indigo-500"></i>
                                <span class="font-medium truncate">{{ Auth::user()->name }}</span>
                            </span>
                            <i class="bi bi-chevron-up text-xs"></i>
                        </button>

                        <div x-show="profileDropdownOpen" 
                             x-cloak 
                             x-transition
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

            <!-- MAIN CONTENT -->
            <main class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-3xl mx-auto space-y-6">

                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Profile</h1>

                    <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        @include('profile.partials.delete-user-form')
                    </div>

                </div>
            </main>

        </div>
    </div>
</x-app-layout>