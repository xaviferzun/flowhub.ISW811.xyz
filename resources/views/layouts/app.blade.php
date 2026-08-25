<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

            <svg class="fixed -bottom-24 -right-24 w-[520px] h-[520px] text-gray-900 dark:text-white opacity-[0.06] pointer-events-none select-none z-0" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M50,20 Q75,35 78,68" fill="none" stroke="currentColor" stroke-width="3"/>
                <path d="M78,68 Q50,85 22,68" fill="none" stroke="currentColor" stroke-width="3"/>
                <path d="M22,68 Q25,35 50,20" fill="none" stroke="currentColor" stroke-width="3"/>
                <circle cx="50" cy="20" r="11" fill="currentColor"/>
                <circle cx="78" cy="68" r="11" fill="currentColor"/>
                <circle cx="22" cy="68" r="11" fill="currentColor"/>
            </svg>

            <div class="relative z-10">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white dark:bg-gray-800 shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
