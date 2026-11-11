@php($settings = $section['settings'] ?? [])
@php($items = $section['items'] ?? [])
@php($withPreview = fn (string $url): string => ($previewTheme ?? null) ? $url.(str_contains($url, '?') ? '&' : '?').'theme='.urlencode($previewTheme) : $url)
@if (! empty($items))
<section class="site-section corporate-pale">
    <div class="site-container">
        <div class="mb-10 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <p class="site-eyebrow">{{ $settings['eyebrow'] ?? 'Capabilities' }}</p>
                <h2 class="site-heading mt-4 text-[clamp(2.2rem,4vw,3rem)]">{{ $settings['heading'] ?? 'The right technology. Real business impact.' }}</h2>
                <p class="site-copy mt-4 max-w-xl">{{ $settings['description'] ?? 'Focused expertise for teams that need technology to create measurable progress.' }}</p>
            </div>
            <a href="{{ $withPreview(route('website.services')) }}" class="site-link">View all services <span aria-hidden="true">&#8594;</span></a>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $item)
                <article class="site-card group flex h-full flex-col p-6">
                    <div class="corporate-icon mb-5 inline-flex h-11 w-11 items-center justify-center border border-[#dbe2ed] text-base font-black">{{ Str::upper(Str::substr($item['title'], 0, 1)) }}</div>
                    <h3 class="text-xl font-bold tracking-[-0.04em] text-[#101c3b]">{{ $item['title'] }}</h3>
                    @if (! empty($item['description']))
                        <p class="site-copy mt-3 text-sm leading-7">{{ $item['description'] }}</p>
                    @endif
                    @if (! empty($item['slug']))
                        <a href="{{ $withPreview(route('website.service', $item['slug'])) }}" class="site-link mt-auto pt-6 text-sm">Learn more <span aria-hidden="true">&#8594;</span></a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
