@php
    $publicSite = app(\App\Support\PublicSiteSettings::class)->all();
    $siteSettings = app(\App\Support\SiteSettings::class);
    $registry = app(\App\Support\ThemeRegistry::class);

    // The controller must authorize preview access before passing this value.
    $theme = is_string($previewTheme ?? null)
        ? $registry->find($previewTheme)
        : null;

    $theme = $theme ?? $registry->active();
    $themeOptions = app(\App\Support\ThemeOptionsConfiguration::class)->publicValues($theme);

    if ($theme['slug'] !== $registry::CORPORATE) {
        echo view($theme['view'].'.layouts.app', get_defined_vars())->render();
        return;
    }

    $isCorporate = $theme['slug'] === \App\Support\ThemeRegistry::CORPORATE;

    $brandValue = $themeOptions['brand_color'] ?? '#000080';

    $brandColor = is_string($brandValue)
        && preg_match('/\A#[0-9a-fA-F]{6}\z/', $brandValue)
            ? $brandValue
            : '#000080';

    $headingFonts = [
        'Georgia' => 'Georgia',
        'Times New Roman' => "'Times New Roman'",
    ];

    $bodyFonts = [
        'Instrument Sans' => "'Instrument Sans'",
        'Arial' => 'Arial',
    ];

    $headingFont = app(\App\Support\ThemeOptionsConfiguration::class)->fontFamily((string) ($themeOptions['heading_font'] ?? 'Georgia'), 'Georgia');
    $bodyFont = app(\App\Support\ThemeOptionsConfiguration::class)->fontFamily((string) ($themeOptions['body_font'] ?? 'Instrument Sans'), 'Instrument Sans');

    $customHtmlSections = app(\App\Support\CustomHtmlConfiguration::class)->sections();
    $customHtmlHeader = $customHtmlSections['header']['enabled'] ? $customHtmlSections['header']['content'] : '';
    $customHtmlBody = $customHtmlSections['body']['enabled'] ? $customHtmlSections['body']['content'] : '';
    $customHtmlFooter = $customHtmlSections['footer']['enabled'] ? $customHtmlSections['footer']['content'] : '';

    $corporateCssVersion = is_file(public_path('css/rexxsol-corporate.css'))
        ? filemtime(public_path('css/rexxsol-corporate.css'))
        : '1.0.1';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >

        <meta
            name="csrf-token"
            content="{{ csrf_token() }}"
        >

        @include('components.seo-meta', [
            'content' => $seoContent ?? null,
            'url' => $seoUrl ?? url()->current(),
            'pageTitle' => $seoPageTitle ?? null,
        ])

        @if (is_string($previewTheme ?? null))
            <meta name="robots" content="noindex, nofollow">
        @endif

        @if (! empty($publicSite['branding.favicon']))
            <link rel="icon" href="{{ $publicSite['branding.favicon'] }}" type="image/png">
            <link rel="apple-touch-icon" href="{{ $publicSite['branding.favicon'] }}">
        @else
            <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
            <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
        @endif

        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
        ])

        @if ($isCorporate)
            <link
                rel="stylesheet"
                href="{{ asset('css/rexxsol-corporate.css') }}?v={{ $corporateCssVersion }}"
            >
        @endif

        @stack('styles')

        @if (\Illuminate\Support\Facades\Route::has('custom-css'))
            <link
                rel="stylesheet"
                href="{{ route('custom-css', ['v' => app(\App\Support\CustomCssConfiguration::class)->stylesheetVersion()]) }}"
            >
        @endif

        {{-- Keep custom HTML editing restricted to trusted administrators. --}}
        @if ($customHtmlHeader)
            {!! $customHtmlHeader !!}
        @endif
    </head>

    <body
        class="theme-{{ $theme['slug'] }} site-page-background antialiased"
        style="
            --corporate-navy: {{ $brandColor }};
            --cms-brand-color: {{ $brandColor }};
            --corporate-heading-font: {{ $headingFont }};
            --corporate-body-font: {{ $bodyFont }};
            --cms-heading-font: {{ $headingFont }};
            --cms-body-font: {{ $bodyFont }};
        "
    >
        @if ($customHtmlBody)
            {!! $customHtmlBody !!}
        @endif

        @if ($isCorporate)
            <a
                href="#main-content"
                class="corporate-skip-link"
            >
                Skip to content
            </a>
        @endif

        <div class="min-h-screen">
            @include('website.partials.header')

            <main id="main-content" tabindex="-1">
                @yield('content')
            </main>

            @include('website.partials.footer')
        </div>

        @if ($customHtmlFooter)
            {!! $customHtmlFooter !!}
        @endif

        @stack('scripts')
    </body>
</html>