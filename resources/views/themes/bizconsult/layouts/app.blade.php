@php
    $site = $settings ?? app(\App\Support\PublicSiteSettings::class)->all();
    $theme = app(\App\Support\ThemeRegistry::class)->find($previewTheme ?? '') ?? app(\App\Support\ThemeRegistry::class)->active();
    $brandLogo = $site['branding.logo'] ?? $site['branding.dark_logo'] ?? null;
    $brandFavicon = $site['branding.favicon'] ?? $brandLogo;
    $themeOptions = app(\App\Support\ThemeOptionsConfiguration::class)->publicValues($theme);
    $themeOptionsService = app(\App\Support\ThemeOptionsConfiguration::class);
    $customHtmlSections = app(\App\Support\CustomHtmlConfiguration::class)->sections();
    $customHtmlHeader = $customHtmlSections['header']['enabled'] ? $customHtmlSections['header']['content'] : '';
    $customHtmlBody = $customHtmlSections['body']['enabled'] ? $customHtmlSections['body']['content'] : '';
    $customHtmlFooter = $customHtmlSections['footer']['enabled'] ? $customHtmlSections['footer']['content'] : '';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.seo-meta', ['content' => $seoContent ?? null, 'url' => $seoUrl ?? url()->current(), 'pageTitle' => $seoPageTitle ?? null])
    @if ($previewTheme ?? false)<meta name="robots" content="noindex,nofollow">@endif
    @if ($brandFavicon)
        <link rel="icon" href="{{ $brandFavicon }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ $brandFavicon }}">
    @else
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets-bizconsult/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets-bizconsult/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets-bizconsult/css/cms-theme.css') }}">
    @stack('styles')
    <style>
        .theme-bizconsult { color: {{ $themeOptions['brand_color'] ?? '#78b77a' }}; font-family: {{ $themeOptionsService->fontFamily((string) ($themeOptions['body_font'] ?? 'Instrument Sans'), 'Instrument Sans') }}; }
        .theme-bizconsult h1, .theme-bizconsult h2, .theme-bizconsult h3, .theme-bizconsult h4, .theme-bizconsult h5, .theme-bizconsult h6 { font-family: {{ $themeOptionsService->fontFamily((string) ($themeOptions['heading_font'] ?? 'Georgia'), 'Georgia') }}; }
        .theme-bizconsult .btn-primary, .theme-bizconsult a:hover { border-color: {{ $themeOptions['brand_color'] ?? '#78b77a' }}; color: {{ $themeOptions['brand_color'] ?? '#78b77a' }}; }
        .theme-bizconsult .btn-primary { background-color: {{ $themeOptions['brand_color'] ?? '#78b77a' }}; color: #fff; }
    </style>
    @if (Route::has('custom-css'))<link rel="stylesheet" href="{{ route('custom-css', ['v' => app(\App\Support\CustomCssConfiguration::class)->stylesheetVersion()]) }}">@endif
    @if ($customHtmlHeader){!! $customHtmlHeader !!}@endif
</head>
<body class="theme-bizconsult {{ request()->routeIs('home') ? 'theme-bizconsult-home' : 'theme-bizconsult-inner' }}">
    @if ($customHtmlBody){!! $customHtmlBody !!}@endif
    @include('themes.bizconsult.partials.header')
    <main id="main-content">@yield('content')</main>
    @include('themes.bizconsult.partials.footer')
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top" aria-label="Back to top"><i class="bi bi-arrow-up"></i></a>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets-bizconsult/lib/wow/wow.min.js') }}"></script>
    <script src="{{ asset('assets-bizconsult/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('assets-bizconsult/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets-bizconsult/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets-bizconsult/js/main.js') }}"></script>
    @if ($customHtmlFooter){!! $customHtmlFooter !!}@endif
    @stack('scripts')
</body>
</html>