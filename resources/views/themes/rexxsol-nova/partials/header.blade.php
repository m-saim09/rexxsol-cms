@php($primaryMenu = collect(($menus ?? [])['primary'] ?? [])->map(static function (array $item): ?array { $label = strtolower(trim((string) ($item['label'] ?? ''))); $path = trim((string) parse_url((string) ($item['url'] ?? ''), PHP_URL_PATH), '/'); $key = match ($label) { 'home' => 'home', 'about' => 'about', 'services' => 'services', 'portfolio', 'projects' => 'portfolio', 'contact' => 'contact', default => match ($path) { '' => 'home', 'about' => 'about', 'services' => 'services', 'portfolio', 'projects' => 'portfolio', 'contact' => 'contact', default => null } }; if ($key === null) return null; $item['label'] = match ($key) { 'home' => 'Home', 'about' => 'About', 'services' => 'Services', 'portfolio' => 'Portfolio', 'contact' => 'Contact' }; $item['_nova_order'] = array_search($key, ['home', 'about', 'services', 'portfolio', 'contact'], true); return $item; })->filter()->sortBy('_nova_order')->values()->all())
@php($currentPath = trim((string) parse_url(url()->current(), PHP_URL_PATH), '/'))
@php($primaryMenu = collect($primaryMenu)->map(function (array $item) use ($currentPath): array { $itemUrl = trim((string) ($item['url'] ?? '')); $itemPath = trim((string) parse_url($itemUrl, PHP_URL_PATH), '/'); $item['is_active'] = $itemUrl !== '' && (($currentPath === $itemPath) || ($itemPath !== '' && str_starts_with($currentPath, $itemPath . '/')) || ($currentPath === '' && $itemPath === '')); foreach (($item['children'] ?? []) as $childIndex => $child) { $childUrl = trim((string) ($child['url'] ?? '')); $childPath = trim((string) parse_url($childUrl, PHP_URL_PATH), '/'); $item['children'][$childIndex]['is_active'] = $childUrl !== '' && (($currentPath === $childPath) || ($childPath !== '' && str_starts_with($currentPath, $childPath . '/')) || ($currentPath === '' && $childPath === '')); } return $item; })->all())
@php($headerCtaLabel = $site['header.cta_label'] ?? "Let's Talk")
@php($headerCtaUrl = $site['header.cta_url'] ?? route('website.contact'))
@php($isHomePage = request()->routeIs('home') || (request()->routeIs('theme.preview') && ($previewTheme ?? null) === 'rexxsol-nova'))
<div class="nova-header-slot" data-nova-header-slot data-home-page="{{ $isHomePage ? 'true' : 'false' }}">
<header class="nova-header" data-nova-header data-home-page="{{ $isHomePage ? 'true' : 'false' }}">
    <div class="nova-container nova-header-inner">
        <a class="nova-brand" href="{{ route('home') }}" aria-label="{{ $site['site.name'] ?? 'Rexxsol' }} home">
            <img src="{{ $site['branding.logo'] ?? $site['branding.dark_logo'] ?? asset('images/logo.png') }}" alt="{{ $site['site.name'] ?? 'Rexxsol' }}">
        </a>
        <nav class="nova-navigation" aria-label="Main navigation">
            @foreach ($primaryMenu as $item)
                @if (empty($item['children']))
                    <a href="{{ $item['url'] }}" @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if (($item['is_active'] ?? false)) aria-current="page" @endif>{{ $item['label'] }}</a>
                @else
                    <details class="nova-nav-group">
                        <summary @if (($item['is_active'] ?? false)) aria-current="page" @endif>{{ $item['label'] }}<i class="fa-solid fa-angle-down" aria-hidden="true"></i></summary>
                        <div class="nova-nav-dropdown">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] }}" @if (($child['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if (($child['is_active'] ?? false)) aria-current="page" @endif>{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </details>
                @endif
            @endforeach
        </nav>
        <a class="nova-header-cta" href="{{ $headerCtaUrl }}">{{ $headerCtaLabel }} <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a>
        <details class="nova-mobile-menu" data-nova-menu>
            <summary aria-label="Open navigation menu" aria-controls="nova-mobile-panel"><i class="fa-solid fa-bars nova-menu-open-icon" aria-hidden="true"></i><i class="fa-solid fa-xmark nova-menu-close-icon" aria-hidden="true"></i></summary>
            <div id="nova-mobile-panel" class="nova-mobile-panel">
                <nav aria-label="Mobile navigation">
                    @foreach ($primaryMenu as $item)
                        @if (empty($item['children']))
                            <a href="{{ $item['url'] }}" @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>{{ $item['label'] }}</a>
                        @else
                            <details class="nova-mobile-group">
                                <summary>{{ $item['label'] }}</summary>
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" @if (($child['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>{{ $child['label'] }}</a>
                                @endforeach
                            </details>
                        @endif
                    @endforeach
                </nav>
                <a class="nova-header-cta" href="{{ $headerCtaUrl }}">{{ $headerCtaLabel }} <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a>
            </div>
        </details>
    </div>
</header>
</div>