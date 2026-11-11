@php($items = $section['items'] ?? [])
@if (! empty($items))
<section class="site-section bg-[#f4f7ff]">
    <div class="site-container">
        <div class="mb-10 max-w-2xl">
            <p class="site-eyebrow">Why choose us</p>
            <h2 class="site-heading mt-4 text-[clamp(2.2rem,4vw,3rem)]">A practical partner for ambitious organizations.</h2>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $item)
                <div class="site-card p-6">
                    <div class="corporate-icon mb-5 inline-flex h-11 w-11 items-center justify-center border border-[#dbe2ed] text-lg font-black">{{ $item['icon'] ?? '•' }}</div>
                    <h3 class="text-xl font-bold tracking-[-0.04em] text-[#101c3b]">{{ $item['title'] }}</h3>
                    @if (! empty($item['description']))
                        <p class="site-copy mt-3 text-sm leading-7">{{ $item['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
