@props(['label', 'value', 'icon', 'detail' => null])

<div class="border border-[#e5eaf2] bg-white p-5 shadow-[0_2px_12px_rgba(23,32,51,0.04)]">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#64748b]">{{ $label }}</p>
            <p class="mt-4 text-3xl font-bold tracking-tight text-[#172033]">{{ number_format($value) }}</p>
            @if ($detail)
                <p class="mt-2 text-xs text-[#64748b]">{{ $detail }}</p>
            @endif
        </div>
        <span class="flex size-11 items-center justify-center rounded-lg bg-[#eef4ff] text-[#000080]"><flux:icon :name="$icon" class="size-5" /></span>
    </div>
</div>
