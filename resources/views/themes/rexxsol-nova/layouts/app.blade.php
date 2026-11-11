@php
    $site = $settings ?? app(\App\Support\PublicSiteSettings::class)->all();
    $theme = app(\App\Support\ThemeRegistry::class)->find($previewTheme ?? '') ?? app(\App\Support\ThemeRegistry::class)->active();
    $favicon = $site['branding.favicon'] ?? $site['branding.logo'] ?? asset('favicon.png');
    $customHtmlEnabled = app(\App\Support\SiteSettings::class)->get('appearance.custom_html_enabled');
    $customHtml = $customHtmlEnabled ? app(\App\Support\SiteSettings::class)->get('appearance.custom_html', '') : '';
    $customHtmlLocation = app(\App\Support\SiteSettings::class)->get('appearance.custom_html_location', 'head');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-rexxsol-nova-document">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.seo-meta', ['content' => $seoContent ?? null, 'url' => $seoUrl ?? url()->current(), 'pageTitle' => $seoPageTitle ?? null])
    @if ($previewTheme ?? false)<meta name="robots" content="noindex,nofollow">@endif
    <link rel="icon" href="{{ $favicon }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('themes/rexxsol-nova/theme.css') }}?v=2.0.38">
    <link rel="stylesheet" href="{{ asset('themes/rexxsol-nova/cta-footer.css') }}?v=1.0.3">
    @if (\Illuminate\Support\Facades\Route::has('custom-css'))<link rel="stylesheet" href="{{ route('custom-css') }}">@endif
    @stack('styles')
    @if ($customHtml && $customHtmlLocation === 'head'){!! $customHtml !!}@endif
</head>
<body id="top" class="theme-rexxsol-nova antialiased{{ request()->routeIs('website.about') ? ' nova-about-route' : '' }}" data-preview-theme="{{ $previewTheme ?? '' }}">
    @if ($customHtml && $customHtmlLocation === 'body_start'){!! $customHtml !!}@endif
    <a class="nova-skip-link" href="#main-content">Skip to content</a>
    @include('themes.rexxsol-nova.partials.header', ['site' => $site, 'menus' => $menus ?? app(\App\Support\NavigationService::class)->menus()])
    <main id="main-content" tabindex="-1">@yield('content')</main>
    @if (! request()->routeIs('website.contact'))
        @if (request()->routeIs('website.about'))
            @include('themes.rexxsol-nova.components.project-cta')
        @else
            @include('themes.rexxsol-nova.components.project-cta-reference')
        @endif
    @endif
    @include('themes.rexxsol-nova.partials.footer')
    @if ($customHtml && $customHtmlLocation === 'body_end'){!! $customHtml !!}@endif
    <script src="{{ asset('themes/rexxsol-nova/theme.js') }}?v=1.5.5" defer></script>
    @stack('scripts')
</body>
</html>