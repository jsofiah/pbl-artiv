<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif; }
        </style>
    </head>
    <body class="bg-[#F3F1FA] antialiased">
        <div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
            <div class="w-full max-w-5xl bg-white rounded-[28px] shadow-xl shadow-purple-900/5 p-3 grid grid-cols-1 lg:grid-cols-2 gap-3">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>