{{--
    TAB 3: MAP
    Dummy grid map + radar ping penanda posisi USV.
    Sengaja BUKAN citra satelit asli (beda dari mockup Stitch) karena
    hardware USV di spek ini tidak punya modul GPS - jujur ditandai
    "No Module" daripada nampilin koordinat palsu.
--}}
<section x-show="activeTab==='map'" class="flex flex-col gap-3">
    <div class="bg-white rounded-xl ambient-shadow overflow-hidden border" style="border-color:var(--c-surface-container);">
        <div class="relative w-full h-[420px] bg-map-pattern">
            {{-- Overlay info --}}
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

            {{-- USV marker + radar ping --}}
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20 flex items-center justify-center pointer-events-none">
                <div class="absolute w-12 h-12 rounded-full animate-radar" style="background:rgba(53,37,205,0.2);"></div>
                <div class="relative w-4 h-4 rounded-full border-2 border-white ambient-shadow z-10" style="background:var(--c-primary);"></div>
            </div>

            {{-- Zoom controls (dekoratif) --}}
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