<div class="mx-auto w-full max-w-[1440px] space-y-5">
    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#206bc4] transition hover:underline">Dashboard</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.platform') }}" wire:navigate class="text-[#206bc4] transition hover:underline">System</a>
        <span aria-hidden="true">/</span>
        <span class="text-[#172033]">Activity Logs</span>
    </nav>

    <header>
        <h1 class="text-2xl font-bold tracking-tight text-[#172033]">Activity Logs</h1>
        <p class="mt-1 text-sm text-[#64748b]">Track changes made across your CMS.</p>
    </header>

    <section class="overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]" aria-label="Activity log records">
        <div class="flex flex-col gap-3 border-b border-[#eef1f6] p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 flex-wrap items-center gap-2.5">
                    <div class="relative w-full sm:w-72">
                        <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#94a3b8]" />
                        <input id="log-search"
                               type="search"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search records..."
                               aria-label="Search records"
                               class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white pl-9 pr-3 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" />
                    </div>

                    <button type="button"
                            wire:click="$toggle('filtersOpen')"
                            aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}"
                            aria-controls="activity-log-filters"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/30 {{ $filterCount ? 'border-[#206bc4] text-[#206bc4] ring-1 ring-[#206bc4]/20' : '' }}">
                        <flux:icon name="funnel" class="size-4 text-[#64748b]" />
                        <span>Filters</span>
                        @if ($filterCount)
                            <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-[#eef4ff] px-1.5 py-0.5 text-[11px] font-bold text-[#206bc4]">{{ $filterCount }}</span>
                        @endif
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-3 lg:ml-auto">
                    <p class="text-xs text-[#64748b]">Times shown in {{ config('app.timezone') }}.</p>
                    <button type="button"
                            wire:click="$refresh"
                            wire:loading.attr="disabled"
                            wire:target="$refresh"
                            class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc] disabled:cursor-wait disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/30">
                        <flux:icon name="arrow-path" class="size-4 text-[#64748b]" wire:loading.class="animate-spin" wire:target="$refresh" />
                        <span>Reload</span>
                    </button>
                </div>
            </div>

            @if ($filtersOpen)
                <div id="activity-log-filters" class="grid w-full grid-cols-1 gap-3 border-t border-[#eef1f6] pt-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="log-actor" class="mb-1 block text-xs font-semibold text-[#475569]">User</label>
                        <input id="log-actor"
                               type="search"
                               wire:model.live.debounce.300ms="actor"
                               placeholder="Search user name..."
                               class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" />
                    </div>
                    <div>
                        <label for="log-module" class="mb-1 block text-xs font-semibold text-[#475569]">Module</label>
                        <select id="log-module" wire:model.live="module" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10">
                            <option value="">All modules</option>
                            @foreach ($modules as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="log-action" class="mb-1 block text-xs font-semibold text-[#475569]">Action</label>
                        <select id="log-action" wire:model.live="action" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10">
                            <option value="">All actions</option>
                            @foreach ($actions as $type)
                                <option value="{{ $type }}">{{ str($type)->headline() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="log-date" class="mb-1 block text-xs font-semibold text-[#475569]">Date</label>
                        <input id="log-date" type="date" wire:model.live="date" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" />
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-3 sm:col-span-2 xl:col-span-4">
                        <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-[#206bc4] transition hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/30">
                            Clear filters
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div wire:loading.flex
             wire:target="search, actor, module, action, date, perPage, clearFilters, $refresh, gotoPage, nextPage, previousPage"
             class="items-center gap-2 border-b border-[#eef1f6] bg-[#f7f9fc] px-4 py-2.5 text-xs font-medium text-[#64748b]"
             role="status"
             aria-live="polite">
            <span class="size-3 animate-spin rounded-full border-2 border-[#206bc4] border-t-transparent" aria-hidden="true"></span>
            <span>Loading activity...</span>
        </div>

        @if ($loadError)
            <div class="m-4 rounded-[6px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-semibold">Unable to load activity records.</p>
                <p class="mt-1 text-xs">Please try Reload. No activity data is currently available.</p>
            </div>
        @elseif ($items->count())
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[900px] border-separate border-spacing-0 text-left">
                    <caption class="sr-only">Read-only CMS activity records</caption>
                    <thead class="bg-[#f7f9fc] text-[11px] font-bold uppercase tracking-[0.12em] text-[#64748b]">
                        <tr>
                            <th scope="col" class="border-b border-[#eef1f6] px-4 py-3">User</th>
                            <th scope="col" class="border-b border-[#eef1f6] px-4 py-3">Action</th>
                            <th scope="col" class="border-b border-[#eef1f6] px-4 py-3">Module</th>
                            <th scope="col" class="border-b border-[#eef1f6] px-4 py-3">Record</th>
                            <th scope="col" class="border-b border-[#eef1f6] px-4 py-3">Date &amp; time</th>
                            <th scope="col" class="border-b border-[#eef1f6] px-4 py-3 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $actionStyle = match($item['action']) {
                                    'created' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'updated' => 'border-blue-200 bg-blue-50 text-blue-700',
                                    'published' => 'border-teal-200 bg-teal-50 text-teal-700',
                                    'deleted' => 'border-red-200 bg-red-50 text-red-700',
                                    default => 'border-slate-200 bg-slate-50 text-slate-600',
                                };
                                $initials = $item['actor'] === 'System / removed user'
                                    ? 'SY'
                                    : collect(explode(' ', trim($item['actor'])))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
                            @endphp
                            <tr wire:key="activity-log-{{ $item['id'] }}" class="bg-white transition-colors hover:bg-[#f8fbff]">
                                <td class="border-b border-[#eef1f6] px-4 py-2.5 align-middle">
                                    <div class="flex min-w-0 items-center gap-2.5">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#f1f5f9] text-[11px] font-bold text-[#475569]" aria-hidden="true">{{ $initials }}</span>
                                        <span class="max-w-[180px] truncate text-sm font-semibold text-[#172033]" title="{{ $item['actor'] }}">{{ $item['actor'] }}</span>
                                    </div>
                                </td>
                                <td class="border-b border-[#eef1f6] px-4 py-2.5 align-middle">
                                    <span class="inline-flex items-center rounded-[5px] border px-2 py-1 text-xs font-semibold {{ $actionStyle }}">{{ ucfirst($item['action']) }}</span>
                                </td>
                                <td class="border-b border-[#eef1f6] px-4 py-2.5 align-middle text-sm text-[#475569]">{{ $item['module'] }}</td>
                                <td class="max-w-[280px] border-b border-[#eef1f6] px-4 py-2.5 align-middle">
                                    <span class="block truncate text-sm font-medium text-[#172033]" title="{{ $item['record'] }}">{{ $item['record'] }}</span>
                                </td>
                                <td class="border-b border-[#eef1f6] px-4 py-2.5 align-middle text-sm text-[#64748b]">
                                    <time datetime="{{ $item['created_at']?->toIso8601String() }}">{{ $item['created_at']?->format('M j, Y, g:i A') }}</time>
                                </td>
                                <td class="border-b border-[#eef1f6] px-4 py-2.5 text-right align-middle">
                                    <button type="button"
                                            wire:click="open({{ $item['id'] }})"
                                            aria-label="View details for {{ $item['record'] }}"
                                            class="inline-flex h-8 items-center justify-center gap-1.5 rounded-[5px] px-2.5 text-xs font-semibold text-[#206bc4] transition hover:bg-[#eef4ff] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/30">
                                        <flux:icon name="eye" class="size-4" />
                                        <span>View</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <div class="flex flex-col items-center justify-center px-4 py-14 text-center">
                <span class="flex size-11 items-center justify-center rounded-full {{ $hasFilters ? 'bg-[#f1f5f9] text-[#64748b]' : 'bg-[#eef4ff] text-[#206bc4]' }}">
                    <flux:icon name="{{ $hasFilters ? 'magnifying-glass' : 'clock' }}" class="size-5" />
                </span>
                <h2 class="mt-3 text-sm font-bold text-[#172033]">{{ $hasFilters ? 'No activity matches your filters' : 'No activity recorded yet' }}</h2>
                @if ($hasFilters)
                    <button type="button" wire:click="clearFilters" class="mt-3 inline-flex h-9 items-center justify-center rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-xs font-semibold text-[#206bc4] transition hover:bg-[#f7f9fc]">Clear filters</button>
                @endif
            </div>
        @endif

        @if (! $loadError)
            <footer class="flex flex-col gap-3 border-t border-[#eef1f6] bg-[#fcfdff] px-4 py-3 text-sm text-[#64748b] sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs sm:text-sm">
                Showing <span class="font-medium text-[#172033]">{{ $items->firstItem() ?? 0 }}</span>
                to <span class="font-medium text-[#172033]">{{ $items->lastItem() ?? 0 }}</span>
                of <span class="font-semibold text-[#172033]">{{ $items->total() }}</span> records
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 text-xs">
                    <label for="activity-per-page">Rows</label>
                    <select id="activity-per-page" wire:model.live="perPage" class="h-8 rounded-[5px] border border-[#dbe2ed] bg-white px-2 text-xs text-[#172033] outline-none focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>per page</span>
                </div>
                <div>{{ $items->onEachSide(1)->links() }}</div>
            </div>
            </footer>
        @endif
    </section>

    <flux:modal name="activity-detail" :closable="false" wire:close="clearSelected" x-on:keydown.escape.window.prevent="$wire.close()" class="max-h-[90dvh] overflow-y-auto sm:w-[42rem]">
        @if ($selected)
            <section class="space-y-5 break-words" aria-labelledby="activity-detail-title">
                <header class="flex items-start justify-between gap-4 border-b border-[#eef1f6] pb-4">
                    <div>
                        <h2 id="activity-detail-title" class="text-lg font-bold text-[#172033]">Activity details</h2>
                        <p class="mt-1 text-xs text-[#64748b]">Read-only information recorded for this event.</p>
                    </div>
                    <button type="button" wire:click="close" aria-label="Close activity details" class="inline-flex size-8 shrink-0 items-center justify-center rounded-[5px] text-[#64748b] transition hover:bg-[#f1f5f9] hover:text-[#172033] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/30">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </header>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="min-w-0"><dt class="text-xs font-semibold text-[#64748b]">Actor</dt><dd class="mt-1 text-sm font-medium text-[#172033]">{{ $selected['actor'] }}</dd></div>
                    <div class="min-w-0"><dt class="text-xs font-semibold text-[#64748b]">Action</dt><dd class="mt-1 text-sm font-medium text-[#172033]">{{ ucfirst($selected['action']) }}</dd></div>
                    <div class="min-w-0"><dt class="text-xs font-semibold text-[#64748b]">Module</dt><dd class="mt-1 text-sm font-medium text-[#172033]">{{ $selected['module'] }}</dd></div>
                    <div class="min-w-0"><dt class="text-xs font-semibold text-[#64748b]">Date &amp; time</dt><dd class="mt-1 text-sm font-medium text-[#172033]">{{ $selected['created_at']?->format('M j, Y, g:i:s A') }}</dd></div>
                    <div class="min-w-0 sm:col-span-2"><dt class="text-xs font-semibold text-[#64748b]">Record</dt><dd class="mt-1 break-words text-sm font-medium text-[#172033]">{{ $selected['record'] }}</dd></div>
                </dl>

                <section class="min-w-0 border-t border-[#eef1f6] pt-4">
                    <h3 class="mb-3 text-sm font-bold text-[#172033]">Logged changes</h3>
                    @if (count($selected['changes']))
                        <div class="overflow-x-auto rounded-[6px] border border-[#e5eaf2]">
                            <table class="w-full min-w-[480px] text-left text-sm">
                                <thead class="bg-[#f7f9fc] text-xs font-semibold text-[#64748b]">
                                    <tr>
                                        <th scope="col" class="px-3 py-2.5">Field</th>
                                        <th scope="col" class="px-3 py-2.5">Previous value</th>
                                        <th scope="col" class="px-3 py-2.5">New value</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#eef1f6]">
                                    @foreach ($selected['changes'] as $field => $change)
                                        <tr>
                                            <th scope="row" class="max-w-[130px] px-3 py-2.5 align-top font-semibold text-[#475569]">{{ str($field)->headline() }}</th>
                                            <td class="max-w-[180px] whitespace-pre-wrap break-words px-3 py-2.5 align-top text-[#64748b]">{{ $change['old'] }}</td>
                                            <td class="max-w-[180px] whitespace-pre-wrap break-words px-3 py-2.5 align-top text-[#172033]">{{ $change['new'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="rounded-[6px] border border-dashed border-[#dbe2ed] bg-[#fbfcfe] px-4 py-5 text-center text-sm text-[#64748b]">No field-level changes were recorded for this action.</p>
                    @endif
                </section>
            </section>
        @endif
    </flux:modal>
</div>
