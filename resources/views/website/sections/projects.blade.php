@php($settings = $section['settings'] ?? [])
@php($items = $section['items'] ?? [])
@if (! empty($items))
<section class="site-section bg-white">
    <div class="site-container">
        <div class="mb-10 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <p class="site-eyebrow">{{ $settings['eyebrow'] ?? 'SELECTED WORK' }}</p>
                <h2 class="site-heading mt-4 text-[clamp(2.2rem,4vw,3rem)]">{{ $settings['heading'] ?? 'Solutions in action.' }}</h2>
            </div>
            <a href="{{ route('website.projects') }}" class="site-link">View All Projects <span aria-hidden="true">&#8594;</span></a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ($items as $item)
                <article class="site-card overflow-hidden">
                    @if (! empty($item['image']))
                        <div class="site-media h-72 md:h-80"><img src="{{ $item['image'] }}" alt="{{ $item['image_alt'] ?? $item['title'] }}" class="w-full object-cover" /></div>
                    @else
                        <div class="site-media flex h-72 items-end border-b border-[#e5eaf2] bg-[#f7f9fc] p-6 text-[#64748b] md:h-80"><span class="text-sm font-semibold uppercase tracking-[0.18em]">Project image unavailable</span></div>
                    @endif

                    <div class="p-6 sm:p-7">
                        <p class="site-eyebrow text-[0.68rem]">Case study</p>
                        <h3 class="mt-3 text-2xl font-bold tracking-[-0.05em] text-[#101c3b]">{{ $item['title'] }}</h3>
                        @if (! empty($item['description']))
                            <p class="site-copy mt-3 text-sm leading-7">{{ $item['description'] }}</p>
                        @endif
                        @if (! empty($item['slug']))
                            <a href="{{ route('website.contact') }}" class="site-link mt-6">Start a similar project <span aria-hidden="true">&#8594;</span></a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
