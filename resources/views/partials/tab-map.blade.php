<div class="bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition-colors duration-300">
    <!-- Header Card Map -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">location_on</span>
            <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Peta Navigasi Kapal (USV)</h3>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="text-gray-500 dark:text-gray-400">GPS Signal:</span>
            <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 font-semibold">Connected</span>
        </div>
    </div>

    <!-- Container Peta Leaflet -->
    <div id="map" class="w-full h-[450px] rounded-xl border border-gray-200 dark:border-gray-700 z-10"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const lat = -6.2088;
        const lng = 106.8456;
        
        // Simpan instance ke window agar bisa diakses dari luar
        window.leafletMap = L.map('map').setView([lat, lng], 15);

        const lightTile = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        });

        const darkTile = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        });

        function updateMapTheme() {
            if (document.documentElement.classList.contains('dark')) {
                window.leafletMap.removeLayer(lightTile);
                darkTile.addTo(window.leafletMap);
            } else {
                window.leafletMap.removeLayer(darkTile);
                lightTile.addTo(window.leafletMap);
            }
        }

        updateMapTheme();

        // Simpan marker ke window
        window.usvMarker = L.marker([lat, lng]).addTo(window.leafletMap)
            .bindPopup('<b>USV NauTech</b><br>Status: Navigating')
            .openPopup();

        const observer = new MutationObserver(updateMapTheme);
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        window.addEventListener('resize', () => window.leafletMap?.invalidateSize());
    });
</script>