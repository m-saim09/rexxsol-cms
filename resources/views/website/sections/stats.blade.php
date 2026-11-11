@php($numbers = $section['items'] ?? [])
@if (! empty($numbers))
<section class="relative overflow-hidden bg-white py-8 text-[#101a44] sm:py-10">
    <div class="site-container relative">
        <div class="mx-auto grid max-w-5xl gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($numbers as $item)
                <div class="rounded-[1.5rem] border border-[#edf1fb] bg-[#f9faff] px-5 py-6 text-center shadow-[0_12px_28px_rgba(24,34,77,0.03)]">
                    <div class="text-3xl font-bold tracking-[-0.06em] text-[#101a44] sm:text-4xl">{{ $item['title'] ?? 'N/A' }}</div>
                    <p class="mt-2 text-xs font-medium uppercase tracking-[0.14em] text-[#7987a9]">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
