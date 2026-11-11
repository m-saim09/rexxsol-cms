@php($settings = $section['settings'] ?? [])
@php($items = $section['items'] ?? [])
@if (! empty($items))
<section class="site-section bg-white">
    <div class="site-container">
        <div class="mb-10 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <p class="site-eyebrow">{{ $settings['eyebrow'] ?? 'Insights' }}</p>
                <h2 class="site-heading mt-4 text-[clamp(2.2rem,4vw,3rem)]">{{ $settings['heading'] ?? 'Fresh perspectives' }}</h2>
            </div>
            <a href="{{ route('website.blog') }}" class="site-link">Visit blog <span aria-hidden="true">&#8594;</span></a>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $item)
                <article class="site-card overflow-hidden">
                    @if (! empty($item['image']))
                        <div class="site-media h-56"><img src="{{ $item['image'] }}" alt="{{ $item['image_alt'] ?? $item['title'] }}" class="w-full object-cover" /></div>
                    @endif
                    <div class="p-6">
                        <p class="site-eyebrow text-[0.66rem]">Insights</p>
                        <h3 class="mt-3 text-2xl font-bold tracking-[-0.05em] text-[#101c3b]">{{ $item['title'] }}</h3>
                        @if (! empty($item['description']))
                            <p class="site-copy mt-3 text-sm leading-7">{{ $item['description'] }}</p>
                        @endif
                        @if (! empty($item['slug']))
                            <a href="{{ route('website.blog.post', $item['slug']) }}" class="site-link mt-6">Read article <span aria-hidden="true">&#8594;</span></a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
