<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Designer Artiv</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
        }
        /* Sembunyikan scrollbar vertikal halaman di Brave/Chromium */
        html, body, main, div {
            -ms-overflow-style: none !important; /* IE/Edge */
            scrollbar-width: none !important;    /* Firefox */
        }
        
        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        main::-webkit-scrollbar,
        div::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        body {
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
        }
    </style>
</head>
<body class="bg-[#F3F1FA] antialiased min-h-screen relative"
      x-data="{ showToast: false, message: '', type: 'success' }"
      @if(session('success'))
          x-init="showToast = true; message = {{ json_encode(session('success')) }}; type = 'success'; setTimeout(() => showToast = false, 4000)"
      @elseif(session('error') || $errors->any())
          x-init="showToast = true; message = {{ json_encode(session('error') ?? $errors->first()) }}; type = 'error'; setTimeout(() => showToast = false, 4000)"
      @endif
>

    <!-- Toast Notification -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-2 opacity-0"
         class="fixed bottom-6 right-6 z-[99999] px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-sm text-white font-medium pointer-events-auto"
         :class="type === 'success' ? 'bg-neutral-900' : 'bg-red-600'"
         style="display: none;">
        <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="type === 'success' ? 'bg-[#D5FC55]' : 'bg-white'"></span>
        <span x-text="message"></span>
    </div>

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <x-designer.sidebar />

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            {{-- Topbar --}}
            <header
                class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 sticky top-0 z-30">
                <h1 class="text-lg font-semibold text-gray-900">
                    @yield('title', 'Dashboard')
                </h1>

                <div class="flex items-center gap-4">
                    <x-shared.notification-icon variant="dark" />
                    <x-shared.user-menu variant="dark" />
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>

        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')

</body>

</html>
