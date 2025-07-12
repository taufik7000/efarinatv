<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Efarina TV')</title>
    @vite('resources/css/app.css')
    
    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom gradient animation */
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 3s ease infinite;
        }
        
        /* Custom backdrop blur for older browsers */
        .backdrop-blur-fallback {
            background-color: rgba(255, 255, 255, 0.95);
        }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans flex flex-col min-h-screen antialiased">

    @include('layouts.partials.header')

    <!-- Main content with dynamic padding for header scroll behavior -->
    <main class="flex-grow" style="padding-top: 152px;">
        @yield('content')
    </main>

    @include('layouts.partials.footer')

</body>
</html>