@php($site = app(App\Support\PublicSiteSettings::class)->all())
@php($menus = app(App\Support\NavigationService::class)->menus())
@php($primaryMenu = $menus['primary'] ?? [])
@php($currentPath = '/'.trim(request()->path(), '/'))
@php($isActiveUrl = function (string $url) use ($currentPath): bool { $parts = parse_url($url); if (!empty($parts['host']) && $parts['host'] !== request()->getHost()) { return false; } $path = '/'.trim((string) ($parts['path'] ?? ''), '/'); return $path === '/' ? in_array($currentPath, ['/', '/home'], true) : ($currentPath === $path || str_starts_with($currentPath, rtrim($path, '/').'/')); })
@php($isActiveItem = function (array $item) use ($isActiveUrl): bool { $label = strtolower(trim((string) ($item['label'] ?? ''))); $routeActive = match ($label) { 'home' => request()->routeIs('home', 'website.home'), 'about' => request()->routeIs('website.about'), 'services' => request()->routeIs('website.services', 'website.service'), 'portfolio', 'projects' => request()->routeIs('website.portfolio'), 'contact' => request()->routeIs('website.contact', 'website.contact.store'), 'insights', 'blog' => request()->routeIs('website.blog', 'website.blog.post'), default => false }; if ($routeActive || $isActiveUrl((string) ($item['url'] ?? ''))) { return true; } foreach ($item['children'] ?? [] as $child) { if ($isActiveUrl((string) ($child['url'] ?? ''))) { return true; } } return false; })
@if (empty($primaryMenu))
    @php($primaryMenu = [
        ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home', 'website.home')],
        ['label' => 'About', 'url' => route('website.about'), 'active' => request()->routeIs('website.about')],
        ['label' => 'Services', 'url' => route('website.services'), 'active' => request()->routeIs('website.services', 'website.service')],
        ['label' => 'Portfolio', 'url' => route('website.portfolio'), 'active' => request()->routeIs('website.portfolio')],
        ['label' => 'Insights', 'url' => route('website.blog'), 'active' => request()->routeIs('website.blog', 'website.blog.post')],
    ])
@endif

<header class="site-header sticky top-0 z-50">
    <div class="site-container">
        <div class="site-header-shell">
            <a href="{{ url('/') }}" class="site-brand" aria-label="Home">
                @if (($site['branding.logo'] ?? null))
                    <img src="{{ $site['branding.logo'] }}" alt="{{ $site['site.name'] ?? 'Rexxsol' }}" class="site-brand-logo" />
                @else
                    <img src="{{ asset('images/logo.png') }}" alt="Rexxsol" class="site-brand-logo" />
                @endif
            </a>

            <nav class="site-nav hidden lg:flex" aria-label="Main navigation">
                @foreach ($primaryMenu as $item)
                    @if (empty($item['children']))
                        @php($isActive = ($item['active'] ?? false) || $isActiveItem($item))
                        <a href="{{ $item['url'] }}" class="site-nav-link {{ $isActive ? 'site-nav-link--active' : '' }}" @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if ($isActive) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @else
                        @php($isActive = ($item['active'] ?? false) || $isActiveItem($item))
                        <div class="site-nav-group">
                            <button type="button" class="site-nav-link site-nav-link--button {{ $isActive ? 'site-nav-link--active' : '' }}" aria-label="{{ $item['label'] }} submenu" @if ($isActive) aria-current="page" @endif>
                                <span>{{ $item['label'] }}</span>
                                <svg viewBox="0 0 20 20" fill="currentColor" class="site-nav-caret" aria-hidden="true">
                                    <path d="M5.5 7.5 10 12l4.5-4.5"/>
                                </svg>
                            </button>
                            <div class="site-nav-menu" role="menu" aria-label="{{ $item['label'] }} submenu">
                                @foreach ($item['children'] as $child)
                                    @php($isChildActive = $isActiveUrl((string) ($child['url'] ?? '')))
                                    <a href="{{ $child['url'] }}" class="site-nav-menu-item {{ $isChildActive ? 'site-nav-menu-item--active' : '' }}" role="menuitem" @if (($child['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if ($isChildActive) aria-current="page" @endif>{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            @if (($site['header.cta_label'] ?? null) || true)
                <div class="site-header-actions hidden lg:flex">
                    <a href="{{ $site['header.cta_url'] ?? route('website.contact') }}" class="site-header-cta">
                        <span>{{ $site['header.cta_label'] ?? "Let's Talk" }}</span>
                        <span aria-hidden="true" class="site-header-cta-arrow">→</span>
                    </a>
                </div>
            @endif

            <details class="site-mobile-menu-shell lg:hidden" data-nav-details>
                <summary class="site-mobile-toggle" aria-label="Toggle menu" aria-controls="site-mobile-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </summary>

                <div id="site-mobile-menu" class="site-mobile-menu-panel">
                    <nav class="site-mobile-nav" aria-label="Mobile navigation">
                        @foreach ($primaryMenu as $item)
                            @if (empty($item['children']))
                                @php($isActive = ($item['active'] ?? false) || $isActiveItem($item))
                                <a href="{{ $item['url'] }}" class="site-mobile-nav-link {{ $isActive ? 'site-mobile-nav-link--active' : '' }}" @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if ($isActive) aria-current="page" @endif>{{ $item['label'] }}</a>
                            @else
                                <div class="site-mobile-nav-group">
                                    @php($isActive = ($item['active'] ?? false) || $isActiveItem($item))
                                    <div class="site-mobile-nav-heading {{ $isActive ? 'site-mobile-nav-heading--active' : '' }}">{{ $item['label'] }}</div>
                                    @foreach ($item['children'] as $child)
                                        @php($isChildActive = $isActiveUrl((string) ($child['url'] ?? '')))
                                        <a href="{{ $child['url'] }}" class="site-mobile-nav-link site-mobile-nav-link--sub {{ $isChildActive ? 'site-mobile-nav-link--active' : '' }}" @if (($child['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if ($isChildActive) aria-current="page" @endif>{{ $child['label'] }}</a>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </nav>

                    @if (($site['header.cta_label'] ?? null) || true)
                        <a href="{{ $site['header.cta_url'] ?? route('website.contact') }}" class="site-header-cta site-header-cta--mobile">
                            <span>{{ $site['header.cta_label'] ?? "Let's Talk" }}</span>
                            <span aria-hidden="true" class="site-header-cta-arrow">→</span>
                        </a>
                    @endif
                </div>
            </details>
        </div>
    </div>
</header>
