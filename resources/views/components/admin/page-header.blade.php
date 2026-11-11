@props(['title', 'description'])

<div class="flex flex-col gap-4 border-b border-[#e5eaf2] pb-6 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#000080]">Overview</p>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-[#172033]">{{ $title }}</h2>
        <p class="mt-2 max-w-2xl text-sm text-[#64748b]">{{ $description }}</p>
    </div>
    <div class="flex items-center gap-2 text-xs font-medium text-[#64748b]"><span class="size-2 rounded-full bg-emerald-500"></span> Workspace online</div>
</div>
