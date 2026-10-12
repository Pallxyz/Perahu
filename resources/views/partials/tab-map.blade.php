<div
    class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
    <!-- Header Card Map -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">location_on</span>
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Peta Navigasi Kapal (USV)</h3>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="text-gray-500 dark:text-gray-400">GPS Signal:</span>
            <span
                class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 font-semibold">Connected</span>
        </div>
    </div>

    <!-- Container Peta Leaflet -->
    <div id="map" class="w-full h-[450px] rounded-xl border border-gray-200 dark:border-gray-700 z-10"></div>
</div>

<style>
    /* Efek gelap untuk tile saat mode dark */
    html.dark #map .leaflet-tile-pane {
        filter: invert(1) hue-rotate(180deg) brightness(0.95) contrast(0.9);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Posisi cadangan kalau lokasi tidak bisa didapat (sekitar Cirebon)
        const defaultPos = [-6.7063, 108.5570];

        window.leafletMap = L.map('map').setView(defaultPos, 15);

        const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(window.leafletMap);

        let fallbackDipakai = false;
        osm.on('tileerror', function() {
            if (fallbackDipakai) return;
            fallbackDipakai = true;
            window.leafletMap.removeLayer(osm);
            L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Tiles &copy; Esri'
                }).addTo(window.leafletMap);
        });

        window.usvMarker = L.marker(defaultPos).addTo(window.leafletMap)
            .bindPopup('<b>USV NauTech</b><br>Status: Navigating');

        // Ambil lokasi asli dari browser
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const here = [pos.coords.latitude, pos.coords.longitude];
                    window.usvMarker.setLatLng(here);
                    window.leafletMap.setView(here, 16);
                    window.usvMarker.openPopup();
                },
                function(err) {
                    console.warn('Gagal ambil lokasi browser:', err.message);
                    window.usvMarker.openPopup();
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        }

        window.addEventListener('resize', () => window.leafletMap?.invalidateSize());
    });
</script>
