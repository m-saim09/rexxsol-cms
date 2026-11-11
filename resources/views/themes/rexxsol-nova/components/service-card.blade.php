@php($title = $item['title'] ?? 'Digital service')
@php($normalizedTitle = \Illuminate\Support\Str::lower($title))
@php($icon = 'fa-solid fa-laptop-code')
@if (str_contains($normalizedTitle, 'strategy'))@php($icon = 'fa-solid fa-compass-drafting')
@elseif (str_contains($normalizedTitle, 'mobile') || str_contains($normalizedTitle, 'app'))@php($icon = 'fa-solid fa-mobile-screen-button')
@elseif (str_contains($normalizedTitle, 'design'))@php($icon = 'fa-solid fa-pen-ruler')
@elseif (str_contains($normalizedTitle, 'automation'))@php($icon = 'fa-solid fa-diagram-project')
@elseif (str_contains($normalizedTitle, 'integration'))@php($icon = 'fa-solid fa-layer-group')
@elseif (str_contains($normalizedTitle, 'software') || str_contains($normalizedTitle, 'development') || str_contains($normalizedTitle, 'web'))@php($icon = 'fa-solid fa-code')@endif
@php($storedIcon = trim((string) ($item['icon'] ?? '')))
@if (str_starts_with($storedIcon, 'fa-'))@php($icon = preg_match('/(?:^|\s)fa-(?:solid|regular|brands|light|thin|duotone|sharp)(?:\s|$)/', $storedIcon) ? $storedIcon : 'fa-solid '.$storedIcon)@endif
@php($serviceUrl = ! empty($item['slug']) ? route('website.service', $item['slug']) : route('website.services'))
@php($serviceUrl = ($previewTheme ?? null) ? $serviceUrl.(str_contains($serviceUrl, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $serviceUrl)
<article class="nova-service-card">
    <span class="nova-service-number" aria-hidden="true">{{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}</span>
    <span class="nova-icon-tile" aria-hidden="true"><i class="{{ $icon }}"></i></span>
    <div class="nova-service-copy">
        <h3>{{ $title }}</h3>
        @if (! empty($item['description']))<p>{{ $item['description'] }}</p>@endif
        <a class="nova-service-link" href="{{ $serviceUrl }}">Explore service <span aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span></a>
    </div>
</article>
