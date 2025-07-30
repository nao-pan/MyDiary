<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-normal">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-pink-50">
        <div class="min-h-screen bg-pink-50 dark:bg-pink-100">
            @include('layouts.navigation')
             {{-- テーマ切り替えセレクター --}}
    <div class="p-4 flex justify-end">
      <div x-data="{ theme: localStorage.getItem('theme') || 'rose' }"
           x-init="document.documentElement.classList.add(`theme-${theme}`)"
           @change="(e) => {
             document.documentElement.classList.remove(`theme-${theme}`);
             theme = e.target.value;
             localStorage.setItem('theme', theme);
             document.documentElement.classList.add(`theme-${theme}`);
           }">
        <select class="p-2 border rounded text-sm" :value="theme">
            <option value="normal">🌼 Default</option>
          <option value="rose">🌹 Rose Elegance</option>
          <option value="mist">☁️ Serenity Mist</option>
          <option value="antique">✨ Antique Glow</option>
        </select>
      </div>
    </div>
<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"  defer></script>
            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset
            @if (session('error'))
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Page Content -->
            <main class="container">
                <div class='font-elegant space-y-4'>
                @yield('content')
                </div>
            </main>
        </div>
         @yield('scripts')
         @stack('scripts')
    </body>
</html>
