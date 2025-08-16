<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="antialiased text-gray-900">
    <!-- Include the navigation bar -->
    @livewire('components.navigation')

    <main class="max-w-screen-xl px-4  mx-auto space-y-12 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <x-footer />

    @livewireScripts
</body>
</html>