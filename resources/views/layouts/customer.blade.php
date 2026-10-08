<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — Artiv</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif; }
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

    <!-- Toast Notification  -->
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

    @php
        $navTitle = trim(View::yieldContent('title')) ?: 'Beranda';
    @endphp
    <x-customer.navbar :title="$navTitle" />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    @stack('scripts')

</body>
</html>