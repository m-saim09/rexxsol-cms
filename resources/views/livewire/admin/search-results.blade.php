<div class="mx-auto max-w-[1100px] space-y-6">
    <x-admin.page-header title="Global Search" description="Search authorized CMS content from one place." />
    <form method="GET" action="{{ route('admin.search') }}" class="border border-[#e5eaf2] bg-white p-5 shadow-[0_2px_12px_rgba(23,32,51,0.04)]"><label for="admin-search" class="sr-only">Search CMS content</label><div class="flex gap-3"><input id="admin-search" name="q" value="{{ $query }}" type="search" autofocus placeholder="Search pages, posts, services and media..." class="min-w-0 flex-1 border border-[#dbe2ed] px-3 py-2.5 text-sm focus:border-[#2563eb] focus:outline-none" /><button type="submit" class="bg-[#000080] px-5 py-2.5 text-sm font-semibold text-white">Search</button></div></form>
    @if ($query === '')
        <div class="border border-[#e5eaf2] bg-white px-6 py-16 text-center text-sm text-[#64748b]">Enter a search term to find CMS content.</div>
    @elseif ($results->isEmpty())
        <div class="border border-[#e5eaf2] bg-white px-6 py-16 text-center text-sm text-[#64748b]">No authorized content matched “{{ $query }}”.</div>
    @else
        <section class="border border-[#e5eaf2] bg-white shadow-[0_2px_12px_rgba(23,32,51,0.04)]"><div class="border-b border-[#eef1f6] px-5 py-4 text-sm font-semibold text-[#172033]">{{ $results->count() }} result{{ $results->count() === 1 ? '' : 's' }}</div><div class="divide-y divide-[#eef1f6]">@foreach ($results as $result)<a href="{{ $result['url'] }}" wire:navigate class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-[#f8fafc]"><span class="min-w-0"><span class="block truncate text-sm font-semibold text-[#172033]">{{ $result['title'] }}</span>@if ($result['meta'])<span class="mt-1 block truncate text-xs text-[#64748b]">{{ $result['meta'] }}</span>@endif</span><span class="shrink-0 text-[11px] font-bold uppercase tracking-[0.12em] text-[#2563eb]">{{ $result['type'] }}</span></a>@endforeach</div></section>
    @endif
</div>
