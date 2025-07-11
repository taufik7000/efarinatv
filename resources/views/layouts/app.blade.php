<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Efarina TV')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans flex flex-col min-h-screen">

    @include('layouts.partials.header')

    <main class="flex-grow">
        @yield('content')
    </main>

    @include('layouts.partials.footer')

</body>
</html>