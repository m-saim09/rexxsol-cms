@php($items = $section['items'] ?? [])
@if (! empty($items))
<section class="site-section bg-[#f8faff]">
    <div class="site-container">
        <div class="mb-10 max-w-2xl">
            <p class="site-eyebrow">Testimonials</p>
            <h2 class="site-heading mt-4 text-[clamp(2.2rem,4vw,3rem)]">What our clients say.</h2>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            @foreach ($items as $item)
                <blockquote class="site-card flex h-full flex-col p-6 sm:p-7">
                    <p class="text-lg leading-8 tracking-[-0.03em] text-[#101c3b]">“{{ $item['quote'] ?? '' }}”</p>
                    <footer class="mt-6 border-t border-[#edf1fb] pt-5">
                        <div class="font-bold text-[#101c3b]">{{ $item['title'] ?? 'Client' }}</div>
                        @if (! empty($item['designation']))
                            <div class="mt-1 text-sm text-[#4d67ff]">{{ $item['designation'] }}</div>
                        @endif
                        @if (! empty($item['company']))
                            <div class="mt-1 text-sm text-[#687a97]">{{ $item['company'] }}</div>
                        @endif
                    </footer>
                </blockquote>
            @endforeach
        </div>
    </div>
</section>
@endif
