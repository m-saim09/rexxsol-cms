@php($footerData = app(\App\Support\FooterService::class)->data($previewTheme ?? null))
@php($theme = app(\App\Support\ThemeRegistry::class)->find($previewTheme ?? '') ?? app(\App\Support\ThemeRegistry::class)->active())
@php($footerLocations = collect($theme['menu_locations'] ?? [])->filter(fn ($location) => str_contains((string) $location, 'footer') && !str_contains((string) $location, 'legal'))->values()->all())
@php($withPreview = fn (string $url): string => ($previewTheme ?? null) ? $url.(str_contains($url, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $url)
<div aria-hidden="true" style="width: 100%; background: #ffffff; line-height: 0; overflow: visible;">
    <svg viewBox="0 0 1440 180" preserveAspectRatio="none" role="presentation" focusable="false" style="display: block; width: 100%; height: clamp(65px, 10vw, 150px); margin-bottom: -1px; fill: #00B98E;"><path d="M0 116C126 44 249 23 383 69C523 117 648 158 778 119C902 82 960 22 1068 39C1150 52 1160 101 1222 81C1312 52 1375 12 1440 0V180H0Z"></path></svg>
</div>
<footer class="container-fluid footer bizconsult-footer" style="background: #00B98E; color: #ffffff; padding: 0 0 0;">
    @if (($footerData['capabilities']['supports_cta'] ?? false) && ($footerData['cta']['enabled'] ?? true))
        <div style="background: #00B98E;">
            <div class="container" style="position: relative; z-index: 1;">
                <div class="bizconsult-footer-cta" style="display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(280px, .95fr); align-items: center; gap: 56px; padding: 38px 0 66px;">
                    <div>
                        @if (!empty($footerData['cta']['eyebrow']))
                            <p style="margin: 0 0 10px; color: rgba(255, 255, 255, .85); font-size: 0.95rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">{{ $footerData['cta']['eyebrow'] }}</p>
                        @endif
                        <h2 style="margin: 0 0 14px; color: #ffffff; font-family: inherit; font-size: clamp(2rem, 4vw, 3.35rem); font-weight: 800; line-height: 1.08;">{{ $footerData['cta']['heading'] }}</h2>
                        <p style="margin: 0 0 28px; color: rgba(255, 255, 255, .92); font-size: 1.15rem; line-height: 1.6;">{{ $footerData['cta']['description'] }}</p>
                        <a href="{{ $withPreview($footerData['cta']['button_url']) }}" class="bizconsult-footer-pill" style="display: inline-flex; align-items: center; gap: 10px; min-height: 50px; padding: 12px 22px; border: 2px solid #ffffff; border-radius: 999px; color: #ffffff; font-weight: 700; line-height: 1.2; text-decoration: none;">
                            <span>{{ $footerData['cta']['button_label'] }}</span><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13"></path><path d="m13 6 6 6-6 6"></path></svg>
                        </a>
                    </div>
                    <div style="display: flex; justify-content: center; align-items: center; min-width: 0;">
                        <img src="{{ $footerData['cta']['image'] }}" alt="Business consulting illustration" style="display: block; width: min(100%, 510px); height: auto; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="container" style="position: relative; z-index: 1;">
        @php($orderedFooterLocations = collect($footerLocations)->sortBy(function ($location) use ($footerData) {
            $heading = strtolower((string) ($footerData['locations'][$location]['heading'] ?? ''));
            if (str_contains($heading, 'explore')) return 0;
            if (str_contains($heading, 'resource')) return 1;
            if (str_contains($heading, 'contact')) return 2;
            return 99;
        })->values()->all())
        <div style="height: 1px; background: rgba(255, 255, 255, .18);"></div>
        <div class="bizconsult-footer-columns" style="display: grid; grid-template-columns: minmax(0, 1.35fr) repeat(3, minmax(0, 1fr)); gap: 42px; padding: 42px 0 38px;">
            <div>
                @if (!empty($footerData['brand']['logo']))
                    <img src="{{ $footerData['brand']['logo'] }}" alt="{{ $footerData['brand']['name'] }}" style="display: block; max-width: 190px; max-height: 48px; width: auto; margin-bottom: 18px; object-fit: contain; filter: brightness(0) invert(1);">
                @else
                    <h3 style="margin: 0 0 12px; color: #ffffff; font-family: inherit; font-size: 1.65rem; font-weight: 800;">{{ $footerData['brand']['name'] }}</h3>
                @endif
                <p style="max-width: 220px; margin: 0 0 22px; color: rgba(255, 255, 255, .88); line-height: 1.55;">{{ $footerData['brand']['description'] }}</p>
                @if (!empty($footerData['socials']))
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @foreach ($footerData['socials'] as $network => $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}" class="bizconsult-footer-social" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border: 1px solid rgba(255, 255, 255, .72); border-radius: 50%; color: #ffffff; text-decoration: none;">
                                <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if (!empty($footerData['automatic_links']))
                <nav aria-label="Pages">
                    <h3 style="margin: 0 0 18px; color: #ffffff; font-family: inherit; font-size: 1.05rem; font-weight: 800;">Pages</h3>
                    @foreach ($footerData['automatic_links'] as $item)
                        <a href="{{ $withPreview($item['url'] ?? '#') }}" class="bizconsult-footer-link" style="display: block; margin-bottom: 12px; color: rgba(255, 255, 255, .9); text-decoration: none;">{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            @endif

            @php($exploreLocation = collect($orderedFooterLocations)->first(fn($location) => str_contains(strtolower((string) ($footerData['locations'][$location]['heading'] ?? '')), 'explore')))
            @php($resourceLocation = collect($orderedFooterLocations)->first(fn($location) => str_contains(strtolower((string) ($footerData['locations'][$location]['heading'] ?? '')), 'resource')))
            @php($exploreData = $exploreLocation ? ($footerData['locations'][$exploreLocation] ?? null) : null)
            @php($resourceData = $resourceLocation ? ($footerData['locations'][$resourceLocation] ?? null) : null)
            @php($automaticFooterUrls = collect($footerData['automatic_links'] ?? [])->pluck('url')->all())

            @if ($exploreData && !empty($exploreData['links']))
                <nav aria-label="{{ $exploreData['heading'] }}">
                    <h3 style="margin: 0 0 18px; color: #ffffff; font-family: inherit; font-size: 1.05rem; font-weight: 800;">{{ $exploreData['heading'] }}</h3>
                    @foreach ($exploreData['links'] as $item)
                        @unless (in_array($item['url'] ?? '', $automaticFooterUrls, true))<a href="{{ $withPreview($item['url'] ?? '#') }}" @if($item['is_external'] ?? false) target="_blank" rel="noopener noreferrer" @endif class="bizconsult-footer-link" style="display: block; margin-bottom: 12px; color: rgba(255, 255, 255, .9); text-decoration: none;">{{ $item['label'] }}</a>@endunless
                    @endforeach
                </nav>
            @endif

            @if ($resourceData && !empty($resourceData['links']))
                <nav aria-label="{{ $resourceData['heading'] }}">
                    <h3 style="margin: 0 0 18px; color: #ffffff; font-family: inherit; font-size: 1.05rem; font-weight: 800;">{{ $resourceData['heading'] }}</h3>
                    @foreach ($resourceData['links'] as $item)
                        @unless (in_array($item['url'] ?? '', $automaticFooterUrls, true))<a href="{{ $withPreview($item['url'] ?? '#') }}" @if($item['is_external'] ?? false) target="_blank" rel="noopener noreferrer" @endif class="bizconsult-footer-link" style="display: block; margin-bottom: 12px; color: rgba(255, 255, 255, .9); text-decoration: none;">{{ $item['label'] }}</a>@endunless
                    @endforeach
                </nav>
            @endif

            @php($renderedFooterLocations = array_filter([$exploreLocation, $resourceLocation]))
            @php($renderedFooterUrls = collect([$exploreData, $resourceData])->flatMap(fn ($data) => $data['links'] ?? [])->pluck('url')->merge(collect($footerData['automatic_links'] ?? [])->pluck('url'))->all())
            @foreach ($orderedFooterLocations as $location)
                @php($locationData = $footerData['locations'][$location] ?? null)
                @php($remainingLinks = collect($locationData['links'] ?? [])->reject(fn (array $item) => in_array($item['url'] ?? '', $renderedFooterUrls, true))->values())
                @if (!in_array($location, $renderedFooterLocations, true) && $remainingLinks->isNotEmpty())
                    <nav aria-label="{{ $locationData['heading'] }}">
                        <h3 style="margin: 0 0 18px; color: #ffffff; font-family: inherit; font-size: 1.05rem; font-weight: 800;">{{ $locationData['heading'] }}</h3>
                        @foreach ($remainingLinks as $item)
                            <a href="{{ $withPreview($item['url'] ?? '#') }}" @if($item['is_external'] ?? false) target="_blank" rel="noopener noreferrer" @endif class="bizconsult-footer-link" style="display: block; margin-bottom: 12px; color: rgba(255, 255, 255, .9); text-decoration: none;">{{ $item['label'] }}</a>
                            @php($renderedFooterUrls[] = $item['url'] ?? '')
                        @endforeach
                    </nav>
                @endif
            @endforeach

            <div>
                <h3 style="margin: 0 0 18px; color: #ffffff; font-family: inherit; font-size: 1.05rem; font-weight: 800;">Contact</h3>
                @if (!empty($footerData['contact']['email']))
                    <p style="margin: 0 0 8px; color: rgba(255, 255, 255, .9); line-height: 1.4;"><a href="mailto:{{ $footerData['contact']['email'] }}" style="color: rgba(255, 255, 255, .9); text-decoration: none;">{{ $footerData['contact']['email'] }}</a></p>
                @endif
                @if (!empty($footerData['contact']['phone']))
                    <p style="margin: 0 0 8px; color: rgba(255, 255, 255, .9); line-height: 1.4;"><a href="tel:{{ preg_replace('/\D+/', '', $footerData['contact']['phone']) }}" style="color: inherit; text-decoration: none;">{{ $footerData['contact']['phone'] }}</a></p>
                @endif
                @if (!empty($footerData['contact']['address']))
                    <p style="margin: 0 0 16px; color: rgba(255, 255, 255, .8); font-size: 0.9rem; line-height: 1.45;"><a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode(strip_tags($footerData['contact']['address'])) }}" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">{{ Str::limit(strip_tags($footerData['contact']['address']), 90) }}</a></p>
                @endif
                <a href="{{ $withPreview(route('website.contact')) }}" class="bizconsult-footer-pill" style="display: inline-flex; align-items: center; gap: 10px; min-height: 44px; padding: 10px 20px; border: 2px solid #ffffff; border-radius: 999px; color: #ffffff; font-weight: 700; line-height: 1.2; text-decoration: none;">
                    <span>Get in touch</span><svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13"></path><path d="m13 6 6 6-6 6"></path></svg>
                </a>
            </div>


        </div>

        <div class="bizconsult-footer-bottom" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; min-height: 68px; border-top: 1px solid rgba(255, 255, 255, .34); color: rgba(255, 255, 255, .82); font-size: .875rem;">
            <p style="margin: 0;">{{ $footerData['copyright'] }}</p>
        </div>
    </div>
</footer>
