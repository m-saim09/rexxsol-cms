@php($hero = is_array($section['settings'] ?? null) ? $section['settings'] : [])
@php($isPlaceholderCopy = str_contains(strtolower((string) ($hero['eyebrow'] ?? '')), 'demo content') || str_contains(strtolower((string) ($hero['description'] ?? '')), 'placeholder content for local ui testing'))
@php($displayCopy = $isPlaceholderCopy ? [] : $hero)
@php($text = static fn (string $key, string $fallback): string => is_string($displayCopy[$key] ?? null) && trim($displayCopy[$key]) !== '' ? trim($displayCopy[$key]) : $fallback)
@php($hasHeadline = is_string($displayCopy['headline'] ?? null) && trim($displayCopy['headline']) !== '')
@php($expanded = mb_strlen((string) ($displayCopy['headline'] ?? '')) > 42 || mb_strlen((string) ($displayCopy['description'] ?? '')) > 110)
@php($safeUrl = static function ($value, string $fallback): string { if (!is_string($value) || trim($value) === '') return $fallback; $value = trim($value); if (preg_match('/[\x00-\x20\x7f\\\\]/', $value)) return $fallback; if (str_starts_with($value, '/') && !str_starts_with($value, '//')) return $value; if (str_starts_with($value, '#') && strlen($value) > 1) return $value; if (filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true)) return $value; return $fallback; })
@php($primaryUrl = $safeUrl($isPlaceholderCopy ? null : ($hero['primary_cta_url'] ?? null), route('website.contact')))
@php($secondaryUrl = $safeUrl($isPlaceholderCopy ? null : ($hero['secondary_cta_url'] ?? null), route('website.portfolio')))
<section @class(['nova-hero', 'nova-hero--expanded' => $expanded]) aria-labelledby="nova-hero-title">
    @if (!empty($hero['hero_image']))<img class="nova-hero-image" src="{{ $hero['hero_image'] }}" alt="" aria-hidden="true">@endif
    <div class="nova-hero-rays" aria-hidden="true"></div>
    <div class="nova-hero-content">
        <p class="nova-eyebrow nova-enter nova-enter-1"><span></span>{{ $text('eyebrow', 'Software. Innovation. Growth.') }}</p>
        <h1 class="nova-title nova-enter nova-enter-2" id="nova-hero-title">
            @if ($hasHeadline)
                @php($configuredHeadline = trim($hero['headline']))
                @php($brandPosition = stripos($configuredHeadline, 'Rexxsol'))
                @if ($brandPosition === false)
                    <span>{{ $configuredHeadline }}</span>
                @else
                    <span>{{ substr($configuredHeadline, 0, $brandPosition) }}<span class="nova-title-brand">{{ substr($configuredHeadline, $brandPosition, 7) }}</span>{{ substr($configuredHeadline, $brandPosition + 7) }}</span>
                @endif
            @else
                <span>Build Your Digital Future</span><span>With <span class="nova-title-brand">Rexxsol</span></span>
            @endif
        </h1>
        <p class="nova-description nova-enter nova-enter-3">{{ $text('description', 'Thoughtfully built websites, software and digital solutions for businesses ready to move forward.') }}</p>
        <div class="nova-hero-actions nova-enter nova-enter-4">
            <a class="nova-button nova-button-primary" href="{{ $primaryUrl }}">{{ $text('primary_cta_label', 'Start a Project') }} <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a>
            <a class="nova-button nova-button-secondary" href="{{ $secondaryUrl }}">{{ $text('secondary_cta_label', 'Explore Our Work') }}</a>
        </div>
    </div>

    <aside class="nova-corner-card nova-card-left" aria-label="From idea to launch">
        <div class="nova-corner-card-heading"><h2>{{ $text('left_card_heading', 'From idea to launch') }}</h2><span class="nova-card-dots" aria-hidden="true"><i></i><i></i><i></i></span></div>
        <div class="nova-hero-chart" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
    </aside>

    <aside class="nova-corner-card nova-card-right" aria-label="Built for your business">
        <h2>{{ $text('right_card_heading', 'Built for your business') }}</h2>
        <ul>
            <li><span class="nova-service-icon" aria-hidden="true"><i class="fa-solid fa-laptop"></i></span><span>Web Development</span><b aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></b></li>
            <li><span class="nova-service-icon" aria-hidden="true"><i class="fa-solid fa-code"></i></span><span>Custom Software</span><b aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></b></li>
            <li><span class="nova-service-icon" aria-hidden="true"><i class="fa-solid fa-gears"></i></span><span>Automation</span><b aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></b></li>
        </ul>
    </aside>

    <a class="nova-scroll" href="#nova-next-section"><span>Scroll to explore</span><i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a>
</section>