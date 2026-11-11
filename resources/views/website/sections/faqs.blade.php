@php($items = $section['items'] ?? [])
@if (! empty($items))
<section class="site-section bg-[#f7f9fc]">
    <div class="mx-auto w-[min(100%-1.5rem,52rem)]">
        <div class="mb-10 text-center">
            <p class="site-eyebrow">FAQ</p>
            <h2 class="site-heading mx-auto mt-4 text-center text-[clamp(2.2rem,4vw,3rem)]">Frequently asked questions</h2>
        </div>

        <div class="space-y-3">
            @foreach ($items as $item)
                <details class="group rounded-2xl border border-[#e3eaf9] bg-white px-5 py-4 shadow-[0_12px_28px_rgba(25,37,77,0.02)] open:bg-[#fbfcff]">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left">
                        <span class="font-semibold text-[#101c3b]">{{ $item['title'] ?? 'Question' }}</span>
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#edf2ff] text-lg font-semibold text-[#4159dd] transition group-open:rotate-45">+</span>
                    </summary>
                    @if (! empty($item['answer']))
                        <div class="site-copy mt-4 text-sm leading-7">{{ $item['answer'] }}</div>
                    @endif
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif
