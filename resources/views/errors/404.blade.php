<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found - {{ config('app.name', 'Rexxsol') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-rexxsol-corporate flex min-h-screen items-center bg-white text-[#172033]">
    <main class="site-container py-20 text-center">
        <p class="site-eyebrow">404 · Page not found</p>
        <h1 class="site-heading mx-auto mt-5">This page has moved on.</h1>
        <p class="site-copy mx-auto mt-5 max-w-xl">The page you requested is unavailable or has not been published.</p>
        <a href="{{ route('home') }}" class="site-button mt-8">Back to home <span aria-hidden="true">&#8594;</span></a>
    </main>
</body>
</html>
