{{--
    TAB 1: CONTROL
    Start/Stop system + dua slider vertikal (Speed & Steering).
    Semua @click / x-model di sini masih mengacu ke state Alpine
    'usvDashboard()' yang didefinisikan di dashboard.blade.php induk -
    jadi partial ini TIDAK bisa dibuka berdiri sendiri, harus lewat @include.
--}}
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