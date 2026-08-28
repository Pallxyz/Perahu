<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BoatControl') }}</title>

    {{--
        Layout khusus untuk dashboard USV - SENGAJA TIDAK memakai
        <x-app-layout> Breeze, karena layout itu bawa header/nav bawaan
        (termasuk logo Laravel) yang duplikat dengan header custom
        "BoatControl" di dalam {{ $slot }} / @yield('content') di bawah.
        Halaman login/register/profile tetap pakai layout Breeze asli
        (resources/views/layouts/app.blade.php) - TIDAK disentuh oleh file ini.
    --}}

    {{-- Tailwind via CDN, karena setup ini tidak menjalankan build step Vite khusus untuk halaman ini --}}
    <script src="https://cdn.tailwindcss.com"></script>

    @vite(['resources/css/app.css'])
</head>
<body class="antialiased">
    {{ $slot }}

    {{-- Tombol logout kecil, mengambang di pojok - satu-satunya sisa fungsi Breeze yang kita pertahankan --}}
    <form method="POST" action="{{ route('logout') }}" class="fixed top-3 right-3 z-50">
        @csrf
        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-white/90 backdrop-blur border shadow-sm text-gray-600 hover:text-red-600 hover:border-red-300 transition">
            Logout
        </button>
    </form>
</body>
</html>