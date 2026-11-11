@php($items = $section['items'] ?? [])
@if (! empty($items))
<section class="site-section bg-[#f7f9fc]">
    <div class="site-container">
        <div class="mb-10 max-w-2xl">
            <p class="site-eyebrow">Leadership</p>
            <h2 class="site-heading mt-4 text-[clamp(2.2rem,4vw,3rem)]">Meet the team behind the work.</h2>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($items as $item)
                <article class="site-card overflow-hidden p-3">
                    @if (! empty($item['image']))
                        <div class="site-media overflow-hidden rounded-[1.2rem]">
                            <img src="{{ $item['image'] }}" alt="{{ $item['image_alt'] ?? $item['title'] }}" class="h-72 w-full object-cover" />
                        </div>
                    @else
                        <div class="flex h-72 items-center justify-center border border-[#e5eaf2] bg-[#f7f9fc] text-sm font-semibold uppercase tracking-[0.14em] text-[#94a3b8]">Profile image unavailable</div>
                    @endif
                    <div class="px-2 pb-3 pt-5">
                        <h3 class="text-lg font-bold tracking-[-0.04em] text-[#101c3b]">{{ $item['title'] }}</h3>
                        @if (! empty($item['designation']))
                            <p class="mt-1 text-sm text-[#4d67ff]">{{ $item['designation'] }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
