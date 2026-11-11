<div class="mx-auto w-full max-w-[1520px] space-y-5 pb-5">
    @php
        $hasFilters = filled($search) || filled($status) || filled($date);
        $filterCount = (int) filled($search) + (int) filled($status) + (int) filled($date);
        $statusStyles = [
            'new' => 'border-blue-200 bg-blue-50 text-blue-700',
            'read' => 'border-slate-200 bg-slate-100 text-slate-600',
            'replied' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'archived' => 'border-slate-200 bg-slate-100 text-slate-600',
        ];
    @endphp

    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs text-[#64748b]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:text-[#2563eb] hover:underline">Dashboard</a>
        <span aria-hidden="true">/</span>
        <span>Content</span>
        <span aria-hidden="true">/</span>
        <span class="font-medium text-[#172033]" aria-current="page">Contact Messages</span>
    </nav>

    <header>
        <h1 class="text-2xl font-bold tracking-tight text-[#172033]">Contact Messages</h1>
        <p class="mt-1 text-sm text-[#64748b]">Review website enquiries and manage follow-ups.</p>
    </header>

    <section aria-label="Contact message filters" class="overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center">
                <label class="relative block w-full sm:w-[min(380px,45vw)]">
                    <span class="sr-only">Search name, email or subject</span>
                    <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#94a3b8]" />
                    <input id="contact-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search name, email or subject…" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white pl-9 pr-3 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10" />
                </label>
                <button type="button" wire:click="$toggle('filtersOpen')" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}" aria-controls="contact-filters" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/30 {{ $hasFilters ? 'border-[#2563eb] text-[#2563eb]' : '' }}">
                    <flux:icon name="funnel" class="size-4 text-[#64748b]" />
                    <span>Filters</span>
                    @if ($filterCount)<span class="flex size-5 items-center justify-center rounded-full bg-blue-50 text-[11px] font-bold text-blue-700">{{ $filterCount }}</span>@endif
                </button>
            </div>
            <button type="button" wire:click="$refresh" wire:loading.attr="disabled" wire:target="$refresh" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc] disabled:cursor-wait disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/30">
                <flux:icon name="arrow-path" class="size-4 text-[#64748b]" wire:loading.class="animate-spin" wire:target="$refresh" />
                <span>Refresh</span>
            </button>
        </div>
        @if ($filtersOpen)
            <div id="contact-filters" class="grid gap-3 border-t border-[#eef1f6] bg-[#fcfdff] p-4 sm:grid-cols-2 sm:items-end">
                <label for="contact-status" class="block">
                    <span class="mb-1 block text-xs font-semibold text-[#475569]">Status</span>
                    <select id="contact-status" wire:model.live="status" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10">
                        <option value="">All statuses</option>
                        @foreach (App\Models\ContactMessage::STATUSES as $state)<option value="{{ $state }}">{{ ucfirst($state) }}</option>@endforeach
                    </select>
                </label>
                <label for="contact-date" class="block">
                    <span class="mb-1 block text-xs font-semibold text-[#475569]">Received date</span>
                    <input id="contact-date" type="date" wire:model.live="date" class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/10" />
                </label>
                @if ($hasFilters)
                    <div class="flex justify-end sm:col-span-2">
                        <button type="button" wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters" class="h-9 rounded-[6px] px-3 text-sm font-semibold text-[#2563eb] transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/30">Clear filters</button>
                    </div>
                @endif
            </div>
        @endif
    </section>

    <section aria-label="Contact message inbox" class="overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
        <div class="flex flex-col gap-1 border-b border-[#eef1f6] px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <h2 class="text-sm font-semibold text-[#172033]">Inbox</h2>
                <p class="mt-0.5 text-xs text-[#64748b]">{{ $items->total() }} {{ $items->total() === 1 ? 'enquiry' : 'enquiries' }}</p>
            </div>
            <div wire:loading.flex wire:target="search, status, date, clearFilters, $refresh, gotoPage, nextPage, previousPage" class="items-center gap-2 text-xs text-[#64748b]" role="status" aria-live="polite">
                <span class="size-3 animate-spin rounded-full border-2 border-[#2563eb] border-t-transparent" aria-hidden="true"></span>Updating inbox…
            </div>
        </div>

        @if ($items->count())
            <div class="grid gap-4 p-4 sm:p-5 lg:grid-cols-2">
                @foreach ($items as $item)
                    @php
                        $initials = collect(preg_split('/\s+/', trim($item->name)) ?: [])->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: '?';
                        $receivedAt = $item->created_at->copy()->timezone(config('app.timezone'));
                    @endphp
                    <article wire:key="contact-message-{{ $item->id }}" class="rounded-[8px] border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] transition hover:border-blue-200">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span aria-hidden="true" class="relative flex size-10 shrink-0 items-center justify-center rounded-full bg-[#eff4fb] text-xs font-bold text-[#475569]">{{ $initials }}
                                    @if ($item->status === 'new')<span class="absolute -left-1 top-1 size-2.5 rounded-full border-2 border-white bg-[#2563eb]"></span>@endif
                                </span>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm {{ $item->status === 'new' ? 'font-bold' : 'font-semibold' }} text-[#172033]">{{ $item->name }}</h3>
                                    <a href="mailto:{{ $item->email }}" class="block truncate text-xs text-[#2563eb] hover:underline">{{ $item->email }}</a>
                                </div>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusStyles[$item->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">{{ ucfirst($item->status) }}</span>
                        </div>
                        <div class="mt-4 border-t border-[#eef1f6] pt-3">
                            <p class="text-sm font-semibold text-[#172033]">{{ $item->subject }}</p>
                            <p class="mt-1 line-clamp-3 whitespace-pre-line break-words text-xs leading-5 text-[#64748b]">{{ $item->message }}</p>
                        </div>
                        <footer class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-[#eef1f6] pt-3">
                            <time datetime="{{ $receivedAt->toIso8601String() }}" class="text-xs text-[#64748b]">{{ $receivedAt->format('M j, Y · g:i A') }}</time>
                            <button type="button" wire:click="open({{ $item->id }})" wire:loading.attr="disabled" wire:target="open({{ $item->id }})" aria-label="View enquiry from {{ $item->name }}" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-[5px] px-2.5 text-xs font-semibold text-[#2563eb] transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/40">
                                <flux:icon name="eye" class="size-4" /><span>View details</span>
                            </button>
                        </footer>
                    </article>
                @endforeach
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <span aria-hidden="true" class="mx-auto flex size-10 items-center justify-center rounded-full bg-blue-50 text-[#2563eb]"><flux:icon name="{{ $hasFilters ? 'magnifying-glass' : 'envelope' }}" class="size-5" /></span>
                <h3 class="mt-3 text-sm font-semibold text-[#172033]">{{ $hasFilters ? 'No matching enquiries' : 'Your inbox is empty' }}</h3>
                <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-[#64748b]">{{ $hasFilters ? 'No enquiries match your search or selected filters.' : 'Website enquiries will appear here when visitors contact you.' }}</p>
                @if ($hasFilters)<button type="button" wire:click="clearFilters" class="mt-3 text-xs font-semibold text-[#2563eb] hover:underline">Clear filters</button>@endif
            </div>
        @endif

        <footer class="flex flex-col gap-3 border-t border-[#eef1f6] bg-[#fcfdff] px-4 py-3 text-xs text-[#64748b] sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <p>Showing <span class="font-medium text-[#172033]">{{ $items->firstItem() ?? 0 }}</span> to <span class="font-medium text-[#172033]">{{ $items->lastItem() ?? 0 }}</span> of <span class="font-semibold text-[#172033]">{{ $items->total() }}</span> enquiries</p>
            @if ($items->hasPages())<div>{{ $items->onEachSide(1)->links() }}</div>@endif
        </footer>
    </section>

    @if ($selected)
        <div class="fixed inset-0 z-50 bg-[#0f172a]/45" role="presentation" x-data x-on:keydown.escape.window.prevent="$wire.close()" wire:click.self="close">
            <aside class="absolute inset-y-0 right-0 flex w-full max-w-[520px] flex-col overflow-y-auto bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="contact-details-title" x-trap.inert.noscroll="true" x-init="$nextTick(() => $refs.contactDrawerClose.focus())" wire:click.stop>
                <header class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-[#e5eaf2] bg-white px-5 py-4">
                    <div><h2 id="contact-details-title" class="text-lg font-bold text-[#172033]">Enquiry details</h2><p class="mt-1 text-xs text-[#64748b]">Review the sender’s message and follow-up status.</p></div>
                    <button x-ref="contactDrawerClose" type="button" wire:click="close" aria-label="Close enquiry details" class="inline-flex size-9 shrink-0 items-center justify-center rounded-[6px] text-[#64748b] transition hover:bg-[#f1f5f9] hover:text-[#172033] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/40"><flux:icon name="x-mark" class="size-5" /></button>
                </header>
                <div class="flex-1 space-y-6 p-5 sm:p-6">
                    <section aria-label="Sender details" class="rounded-[8px] border border-[#e5eaf2] bg-[#fcfdff] p-4">
                        <div class="flex items-center gap-3">
                            @php($selectedInitials = collect(preg_split('/\s+/', trim($selected->name)) ?: [])->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: '?')
                            <span aria-hidden="true" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#eff4fb] text-sm font-bold text-[#475569]">{{ $selectedInitials }}</span>
                            <div class="min-w-0"><p class="truncate text-sm font-semibold text-[#172033]">{{ $selected->name }}</p><a href="mailto:{{ $selected->email }}" class="block break-all text-xs text-[#2563eb] hover:underline">{{ $selected->email }}</a></div>
                        </div>
                        @if ($selected->phone || $selected->company)
                            <dl class="mt-4 grid gap-x-4 border-t border-[#e5eaf2] pt-3 {{ $selected->phone && $selected->company ? 'sm:grid-cols-2' : '' }}">
                                @if ($selected->phone)<div class="py-1"><dt class="text-[11px] font-semibold text-[#64748b]">Phone</dt><dd class="mt-0.5 break-words text-sm text-[#172033]"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $selected->phone) }}" class="hover:text-[#2563eb]">{{ $selected->phone }}</a></dd></div>@endif
                                @if ($selected->company)<div class="py-1"><dt class="text-[11px] font-semibold text-[#64748b]">Company</dt><dd class="mt-0.5 break-words text-sm text-[#172033]">{{ $selected->company }}</dd></div>@endif
                            </dl>
                        @endif
                    </section>

                    <section>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#64748b]">Subject</p>
                        <h3 class="mt-1 break-words text-base font-semibold text-[#172033]">{{ $selected->subject }}</h3>
                        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-[#64748b]">
                            @php($selectedReceivedAt = $selected->created_at->copy()->timezone(config('app.timezone')))
                            <time datetime="{{ $selectedReceivedAt->toIso8601String() }}">{{ $selectedReceivedAt->format('M j, Y, g:i A') }} · {{ config('app.timezone') }}</time>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 font-semibold {{ $statusStyles[$selected->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">{{ ucfirst($selected->status) }}</span>
                        </div>
                    </section>

                    <section aria-labelledby="contact-message-heading">
                        <h3 id="contact-message-heading" class="text-sm font-semibold text-[#172033]">Message</h3>
                        <div class="mt-2 min-h-28 whitespace-pre-wrap break-words rounded-[8px] border border-[#e5eaf2] bg-[#f7f9fc] p-4 text-sm leading-6 text-[#334155]">{{ $selected->message }}</div>
                    </section>

                    @if ($selected->email)
                        <a href="mailto:{{ $selected->email }}?subject={{ rawurlencode('Re: '.str_replace(["\r", "\n"], ' ', $selected->subject)) }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm font-semibold text-[#2563eb] transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2563eb]/40">
                            <flux:icon name="arrow-uturn-left" class="size-4" />Reply via email
                        </a>
                    @endif

                    @can('contact_messages.update')
                        <section aria-labelledby="contact-follow-up-heading" class="border-t border-[#e5eaf2] pt-5">
                            <h3 id="contact-follow-up-heading" class="text-sm font-semibold text-[#172033]">Follow-up status</h3>
                            <p class="mt-1 text-xs leading-5 text-[#64748b]">Status changes track follow-up only; they do not send an email.</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($selected->status !== 'new')<button type="button" wire:click="changeStatus('new')" wire:loading.attr="disabled" wire:target="changeStatus" class="cms-secondary">Mark unread</button>@endif
                                @if ($selected->status !== 'read')<button type="button" wire:click="changeStatus('read')" wire:loading.attr="disabled" wire:target="changeStatus" class="cms-secondary">Mark read</button>@endif
                                @if ($selected->status !== 'replied')<button type="button" wire:click="changeStatus('replied')" wire:loading.attr="disabled" wire:target="changeStatus" class="cms-primary">Mark replied</button>@endif
                                @if ($selected->status !== 'archived')<button type="button" wire:click="changeStatus('archived')" wire:confirm="Archive this message? You can change its status later." wire:loading.attr="disabled" wire:target="changeStatus" class="cms-secondary text-slate-600">Archive</button>@endif
                            </div>
                        </section>
                    @endcan
                </div>
            </aside>
        </div>
    @endif
</div>
