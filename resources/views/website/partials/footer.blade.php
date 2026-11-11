@php($footerData = app(\App\Support\FooterService::class)->data($previewTheme ?? null))
@php($theme = app(\App\Support\ThemeRegistry::class)->find($previewTheme ?? '') ?? app(\App\Support\ThemeRegistry::class)->active())
@php($footerLocations = collect($theme['menu_locations'] ?? [])->filter(fn ($location) => str_contains((string) $location, 'footer') && !str_contains((string) $location, 'legal'))->values()->all())
@php($site = app(\App\Support\PublicSiteSettings::class)->all())
<footer class="border-t border-[#e9edf8] bg-white text-[#62708d]">
    <div class="site-container py-14 sm:py-16">
        <div class="grid gap-10 lg:grid-cols-[1.5fr_1fr_1fr_1fr]">
            <div>
                @if (!empty($footerData['brand']['logo']))
                    <img src="{{ $footerData['brand']['logo'] }}" alt="{{ $footerData['brand']['name'] }}" class="h-10 w-auto" />
                @else
                    <div class="text-lg font-black tracking-[0.18em] text-[#07184f]">{{ Str::upper($footerData['brand']['name']) }}</div>
                @endif
                <p class="mt-5 max-w-md text-sm leading-7 text-[#64748b]">{{ $footerData['brand']['description'] }}</p>
            </div>

            @foreach ($footerLocations as $location)
                @php($colData = $footerData['locations'][$location] ?? null)
                @if ($colData && !empty($colData['links']))
                    <div>
                        <h3 class="text-[0.7rem] font-bold uppercase tracking-[0.18em] text-[#0f172a]">{{ $colData['heading'] }}</h3>
                        <ul class="mt-5 space-y-3 text-sm">
                            @foreach ($colData['links'] as $item)
                                <li>
                                    <a href="{{ $item['url'] ?? '#' }}" @if($item['is_external'] ?? false) target="_blank" rel="noopener noreferrer" @endif class="transition hover:text-[#4d67ff]">
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

            <div>
                <h3 class="text-[0.7rem] font-bold uppercase tracking-[0.18em] text-[#0f172a]">Contact</h3>
                <ul class="mt-5 space-y-3 text-sm">
                    @if (!empty($footerData['contact']['email']))
                        <li><a href="mailto:{{ $footerData['contact']['email'] }}" class="transition hover:text-[#4d67ff]">{{ $footerData['contact']['email'] }}</a></li>
                    @endif
                    @if (!empty($footerData['contact']['phone']))
                        <li><a href="tel:{{ preg_replace('/\D+/', '', $footerData['contact']['phone']) }}" class="transition hover:text-[#4d67ff]">{{ $footerData['contact']['phone'] }}</a></li>
                    @endif
                    @if (!empty($footerData['contact']['address']))
                        <li><a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode(strip_tags($footerData['contact']['address'])) }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-[#4d67ff]">{{ Str::limit(strip_tags($footerData['contact']['address']), 120) }}</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <h3 class="text-[0.7rem] font-bold uppercase tracking-[0.18em] text-[#0f172a]">Connect</h3>
                <div class="mt-5 flex flex-wrap gap-3 text-sm">
                    @foreach ($footerData['socials'] as $network => $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-[#4d67ff]">{{ $social['label'] }}</a>
                    @endforeach
                </div>
            </div>

            @if (!empty($site['widgets.footer_text']))
                <div class="text-sm leading-7">{!! nl2br(e($site['widgets.footer_text'])) !!}</div>
            @endif
        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-[#edf1fb] pt-6 text-sm text-[#90a0bd] sm:flex-row sm:items-center sm:justify-between">
            <p>{{ $footerData['copyright'] }}</p>
            @if (!empty($footerData['legal']))
                <nav class="flex flex-wrap gap-4 text-xs" aria-label="Legal navigation">
                    @foreach ($footerData['legal'] as $legal)
                        <a href="{{ $legal['url'] }}" @if($legal['is_external'] ?? false) target="_blank" rel="noopener noreferrer" @endif class="transition hover:text-[#4d67ff]">{{ $legal['label'] }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>
</footer>
