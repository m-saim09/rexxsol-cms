@php($settings = $section['settings'] ?? [])
@php($content = $settings['description'] ?? null)
<section class="site-section bg-white">
    <div class="site-container">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div class="site-media overflow-hidden rounded-[1.75rem] border border-[#edf1fb] shadow-[0_28px_60px_rgba(25,37,77,0.06)]">
                @if (! empty($settings['about_image']))
                    <img src="{{ $settings['about_image'] }}" alt="{{ $settings['about_image_alt'] ?? ($settings['heading'] ?? 'Rexxsol') }}" class="h-full min-h-[320px] w-full object-cover lg:min-h-[420px]" />
                @else
                    <div class="flex h-full min-h-[320px] items-center justify-center border border-[#e5eaf2] bg-[#f7f9fc] text-sm font-semibold uppercase tracking-[0.14em] text-[#94a3b8] lg:min-h-[420px]">About image unavailable</div>
                @endif
            </div>

            <div>
                <p class="site-eyebrow">{{ $settings['eyebrow'] ?? 'About us' }}</p>
                <h2 class="site-heading mt-4 text-[clamp(2.3rem,4vw,3.2rem)]">{{ $settings['heading'] ?? 'Built for the next chapter of growth' }}</h2>
                @if ($content)
                    <p class="site-copy mt-6 max-w-xl text-base">{{ $content }}</p>
                @endif
                @if (($settings['cta_label'] ?? null) && ($settings['cta_url'] ?? null))
                    <div class="mt-8 flex items-center gap-3">
                        <a href="{{ $settings['cta_url'] }}" class="site-button">{{ $settings['cta_label'] }} <span aria-hidden="true">&#8594;</span></a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
