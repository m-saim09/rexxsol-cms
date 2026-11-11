@php($site = $settings ?? app(\App\Support\PublicSiteSettings::class)->all())
@php($menus = $menus ?? app(\App\Support\NavigationService::class)->menus())
@php($themeSlug = $previewTheme ?? app(\App\Support\ThemeRegistry::class)->active()['slug'])
@php($navigation = app(\App\Support\NavigationService::class))
@php($primary = $navigation->forLocation('primary', $themeSlug))
@php($mobile = $navigation->forLocation('mobile', $themeSlug) ?: $primary)
@php($brandLogo = $site['branding.logo'] ?? $site['branding.dark_logo'] ?? null)
@php($isHomepage = request()->routeIs('home'))
@php($withPreview = fn (string $url): string => ($previewTheme ?? null) ? $url.(str_contains($url, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $url)
@php($currentPath = '/'.trim(request()->path(), '/'))
@php($isActiveNav = function (string $label, string $url) use ($currentPath): bool { $routeActive = match (strtolower($label)) { 'home' => request()->routeIs('home', 'website.home'), 'about' => request()->routeIs('website.about'), 'services' => request()->routeIs('website.services', 'website.service'), 'portfolio', 'projects' => request()->routeIs('website.portfolio'), 'contact' => request()->routeIs('website.contact', 'website.contact.store'), 'insights', 'blog' => request()->routeIs('website.blog', 'website.blog.post'), default => false }; $parts = parse_url($url); $sameHost = empty($parts['host']) || $parts['host'] === request()->getHost(); $path = '/'.trim((string) ($parts['path'] ?? ''), '/'); $pathActive = $sameHost && ($path === '/' ? in_array($currentPath, ['/', '/home'], true) : ($currentPath === $path || str_starts_with($currentPath, rtrim($path, '/').'/'))); return $routeActive || $pathActive; })
<div class="container-xxl position-relative p-0 bizconsult-header">
    <nav class="navbar navbar-expand-lg navbar-light px-3 px-lg-5 py-3 bizconsult-navbar" @if ($isHomepage) style="background: transparent; border-bottom: 1px solid rgba(255, 255, 255, .2); box-shadow: none;" @else style="background: #ffffff; border-bottom: 1px solid #e8eef2; box-shadow: 0 8px 24px rgba(14, 46, 80, .08);" @endif>
        <a href="{{ route('home') }}" class="navbar-brand p-0 bizconsult-brand" aria-label="{{ $site['site.name'] ?? 'Home' }}">
            @if ($brandLogo)<img src="{{ $brandLogo }}" alt="{{ $site['site.name'] ?? 'Company logo' }}" class="bizconsult-logo">@else<h1 class="m-0">{{ $site['site.name'] ?? $site['site.company'] ?? 'Rexxsol' }}</h1>@endif
        </a>
        <button class="navbar-toggler bizconsult-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-label="Toggle navigation" style="border-color: #dbe5ea; color: #103354;"><span class="fa fa-bars"></span></button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-3 py-lg-0 bizconsult-nav-links d-none d-lg-flex" aria-label="Desktop navigation">
                @php($portfolioNavRendered = false)
                @forelse ($primary as $item)
                    @if (empty($item['children']))
                        @php($navLabel = $item['label'] ?? '')
                        @php($navUrl = $item['url'] ?? '#')
                        @php($isActive = $isActiveNav($navLabel, $navUrl))
                        <a href="{{ $withPreview($navUrl) }}" class="nav-item nav-link {{ $isActive ? 'active' : '' }}" @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if ($isActive) aria-current="page" @endif @unless ($isHomepage) style="color: #103354;" @endunless>{{ $navLabel }}</a>
                    @else
                        @php($isActive = $isActiveNav($item['label'] ?? '', $item['url'] ?? '#'))
                        <div class="nav-item dropdown">
                            <button class="nav-link dropdown-toggle {{ $isActive ? 'active' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" @unless ($isHomepage) style="color: #103354;" @endunless>{{ $item['label'] }}</button>
                            <div class="dropdown-menu">
                                @foreach ($item['children'] as $child)
                                    @php($isChildActive = $isActiveNav($child['label'] ?? '', $child['url'] ?? '#'))
                                    <a href="{{ $withPreview($child['url'] ?? '#') }}" class="dropdown-item {{ $isChildActive ? 'active' : '' }}" @if (($child['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if ($isChildActive) aria-current="page" @endif>{{ $child['label'] ?? '' }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @empty
                    @foreach ([['Home', route('home')], ['Portfolio', route('website.portfolio')], ['About', route('website.about')], ['Services', route('website.services')], ['Contact', route('website.contact')]] as [$navLabel, $navUrl])
                        @php($isActive = $isActiveNav($navLabel, $navUrl))
                        <a href="{{ $withPreview($navUrl) }}" class="nav-item nav-link {{ $isActive ? 'active' : '' }}" @if ($isActive) aria-current="page" @endif @unless ($isHomepage) style="color: #103354;" @endunless>{{ $navLabel }}</a>
                    @endforeach
                @endforelse
            </div>
            <div class="navbar-nav ms-auto py-3 py-lg-0 bizconsult-nav-links d-flex d-lg-none" aria-label="Mobile navigation">
                @foreach ($mobile as $item)
                    @if (empty($item['children']))
                        @php($navLabel = $item['label'] ?? '')
                        @php($navUrl = $item['url'] ?? '#')
                        @php($isActive = $isActiveNav($navLabel, $navUrl))
                        <a href="{{ $withPreview($navUrl) }}" class="nav-item nav-link {{ $isActive ? 'active' : '' }}" @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if ($isActive) aria-current="page" @endif @unless ($isHomepage) style="color: #103354;" @endunless>{{ $navLabel }}</a>
                    @else
                        @php($isActive = $isActiveNav($item['label'] ?? '', $item['url'] ?? '#'))
                        <div class="nav-item dropdown">
                            <button class="nav-link dropdown-toggle {{ $isActive ? 'active' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" @unless ($isHomepage) style="color: #103354;" @endunless>{{ $item['label'] }}</button>
                            <div class="dropdown-menu">
                                @foreach ($item['children'] as $child)
                                    @php($isChildActive = $isActiveNav($child['label'] ?? '', $child['url'] ?? '#'))
                                    <a href="{{ $withPreview($child['url'] ?? '#') }}" class="dropdown-item {{ $isChildActive ? 'active' : '' }}" @if (($child['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif @if ($isChildActive) aria-current="page" @endif>{{ $child['label'] ?? '' }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <a href="{{ $withPreview($site['header.cta_url'] ?? route('website.contact')) }}" class="btn btn-primary rounded-pill py-2 px-4 ms-lg-4 bizconsult-nav-cta" style="background: #00B98E; border-color: #00B98E; color: #ffffff;">{{ $site['header.cta_label'] ?? 'Get Started' }}</a>
        </div>
    </nav>
</div>