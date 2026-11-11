@php($settings = $section['settings'] ?? [])
<section class="corporate-cta relative overflow-hidden py-16 sm:py-20">
    <div class="site-container relative">
        <div class="px-6 py-8 sm:px-8 lg:px-10 lg:py-10">
            <div class="site-grid absolute inset-0 opacity-25"></div>
            <div class="relative lg:flex lg:items-center lg:justify-between lg:gap-12">
                <div>
                    <p class="site-eyebrow">{{ $settings['eyebrow'] ?? 'LET’S BUILD' }}</p>
                    <h2 class="site-heading mt-4 max-w-2xl text-3xl sm:text-4xl lg:text-[3rem]">{{ $settings['heading'] ?? 'Let’s build your next chapter.' }}</h2>
                    <p class="site-copy mt-4 max-w-xl text-base">{{ $settings['description'] ?? 'Tell us what you are building and we will bring the right technology and people into the conversation.' }}</p>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:mt-0">
                    <a href="{{ $settings['cta_url'] ?? route('website.contact') }}" class="site-button">{{ $settings['cta_label'] ?? 'Discuss Your Project' }} <span aria-hidden="true">&#8594;</span></a>
                    @if (($settings['secondary_cta_label'] ?? null) && ($settings['secondary_cta_url'] ?? null))
                        <a href="{{ $settings['secondary_cta_url'] }}" class="site-button site-button--light">{{ $settings['secondary_cta_label'] }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
