<!DOCTYPE html>
<html lang="id">

<head>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Admin Artiv</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body, main, div {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
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

<body class="bg-[#F3F1FA] antialiased relative"
      x-data="{ showToast: false, message: '', type: 'success', toastTimer: null }"
      @toast.window="
          showToast = true;
          message = $event.detail.message;
          type = $event.detail.type || 'success';
          clearTimeout(toastTimer);
          toastTimer = setTimeout(() => showToast = false, $event.detail.timeout || 4000);
      "
      @if(session('success'))
          x-init="$dispatch('toast', { message: {{ json_encode(session('success')) }}, type: 'success' })"
      @elseif(session('info'))
          x-init="$dispatch('toast', { message: {{ json_encode(session('info')) }}, type: 'info' })"
      @elseif(session('error') || $errors->any())
          x-init="$dispatch('toast', { message: {{ json_encode(session('error') ?? $errors->first()) }}, type: 'error' })"
      @endif
>

    {{-- Toast Notification 3 Warna (Emerald, Neutral, Rose) --}}
    <div x-show="showToast" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-2 opacity-0"
         class="fixed bottom-6 right-6 z-[99999] px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-sm text-white font-medium pointer-events-auto"
         :class="{
             'bg-emerald-600': type === 'success',
             'bg-neutral-900': type === 'info',
             'bg-rose-600': type === 'error'
         }">
        <span class="w-2.5 h-2.5 rounded-full shrink-0" 
              :class="{
                  'bg-white': type === 'success' || type === 'error',
                  'bg-[#D5FC55]': type === 'info'
              }"></span>
        <span x-text="message"></span>
    </div>

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <x-admin.sidebar />

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Topbar --}}
            <header
                class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 sticky top-0 z-30">
                <h1 class="text-lg font-semibold text-gray-900">
                    @yield('title', 'Dashboard')
                </h1>

                <div class="flex items-center gap-4">
                    @include('partials.notification-dropdown', [
            'bellClass' => 'border-slate-300 text-slate-600 hover:bg-slate-100',
            'ringColor' => '#FFFFFF',
        ])
                    <x-shared.user-menu variant="dark" />
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 p-6">
                @yield('content')
            </main>

        </div>
    </div>

    @stack('scripts')
    @include('components.shared.modals.confirm')

</body>

</html>