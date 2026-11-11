<div class="mx-auto w-full max-w-[1440px] space-y-4">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#206bc4]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:underline">Dashboard</a>
        <span class="text-[#94a3b8]">/</span>
        <a href="{{ route('admin.platform') }}" wire:navigate class="transition hover:underline">System</a>
        <span class="text-[#94a3b8]">/</span>
        <span class="text-[#64748b]">Users &amp; Roles</span>
    </nav>

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold tracking-tight text-[#172033]">Users &amp; Roles</h1>
    </div>

    <section class="w-full overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
        {{-- Table toolbar --}}
        <div class="flex flex-col gap-3 border-b border-[#eef1f6] p-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Bulk Actions --}}
                @can('users.update')
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" 
                            @click="open = !open" 
                            @disabled(empty($selected))
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc] disabled:cursor-not-allowed disabled:opacity-50">
                        <span>Bulk Actions{{ count($selected) > 0 ? ' (' . count($selected) . ')' : '' }}</span>
                        <flux:icon name="chevron-down" class="size-4 text-[#64748b]" />
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 top-[calc(100%+6px)] z-30 min-w-[170px] rounded-[6px] border border-[#e5eaf2] bg-white py-1.5 shadow-lg" 
                         x-cloak>
                        <button type="button" 
                                wire:click="applyBulkAction('activate')" 
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-3.5 py-2 text-left text-sm text-[#172033] transition hover:bg-[#f7f9fc]">
                            <flux:icon name="check-circle" class="size-4 text-emerald-600" />
                            <span>Activate</span>
                        </button>
                        <button type="button" 
                                wire:click="applyBulkAction('deactivate')" 
                                wire:confirm="Are you sure you want to deactivate the selected users? Inactive users cannot log in to the CMS."
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-3.5 py-2 text-left text-sm text-[#172033] transition hover:bg-[#f7f9fc]">
                            <flux:icon name="no-symbol" class="size-4 text-amber-600" />
                            <span>Deactivate</span>
                        </button>
                    </div>
                </div>
                @endcan

                {{-- Filters --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" 
                            @click="open = !open" 
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc] {{ ($role !== '' || $status !== '') ? 'border-[#206bc4] text-[#206bc4] ring-1 ring-[#206bc4]/20' : '' }}">
                        <flux:icon name="funnel" class="size-4 text-[#64748b]" />
                        <span>Filters{{ ($role !== '' || $status !== '') ? ' (' . collect([$role, $status])->filter()->count() . ')' : '' }}</span>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 top-[calc(100%+6px)] z-30 w-72 rounded-[8px] border border-[#e5eaf2] bg-white p-4 shadow-xl" 
                         x-cloak>
                        <div class="space-y-3.5">
                            <div>
                                <label for="filter-role" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Role</label>
                                <select id="filter-role" wire:model.live="role" class="h-9 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-2.5 text-sm text-[#172033] outline-none focus:border-[#206bc4]">
                                    <option value="">All roles</option>
                                    @foreach ($roles as $option)
                                        <option value="{{ $option->slug }}">{{ $option->name }} ({{ $roleCounts[$option->slug] ?? 0 }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="filter-status" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">Status</label>
                                <select id="filter-status" wire:model.live="status" class="h-9 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-2.5 text-sm text-[#172033] outline-none focus:border-[#206bc4]">
                                    <option value="">All statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-[#eef1f6] pt-3">
                            <button type="button" wire:click="clearFilters" @click="open = false" class="text-xs font-semibold text-[#64748b] transition hover:text-[#206bc4]">
                                Clear filters
                            </button>
                            <button type="button" @click="open = false" class="rounded-[6px] bg-[#206bc4] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#1a569d]">
                                Done
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Search users field --}}
                <div class="relative w-full sm:w-72">
                    <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#94a3b8]" />
                    <input wire:model.live.debounce.300ms="search" 
                           type="search" 
                           placeholder="Search users..." 
                           class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white pl-9 pr-3 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" />
                </div>
            </div>

            {{-- Right action buttons --}}
            <div class="flex items-center gap-2.5">
                @can('users.create')
                <a href="{{ route('admin.users.create') }}" 
                   wire:navigate 
                   class="inline-flex h-10 items-center justify-center gap-1.5 rounded-[6px] bg-[#206bc4] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a569d]">
                    <flux:icon name="plus" class="size-4" />
                    <span>Create user</span>
                </a>
                @endcan
                <button type="button" 
                        wire:click="$refresh" 
                        wire:loading.attr="disabled"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc]">
                    <flux:icon name="arrow-path" class="size-4 text-[#64748b]" wire:loading.class="animate-spin" />
                    <span>Reload</span>
                </button>
            </div>
        </div>

        {{-- Active filters bar --}}
        @if ($search !== '' || $role !== '' || $status !== '')
            <div class="flex flex-wrap items-center gap-2 border-b border-[#eef1f6] bg-[#fbfcfe] px-4 py-2 text-xs text-[#64748b]">
                <span class="font-medium">Active filters:</span>
                @if ($search !== '')
                    <span class="inline-flex items-center gap-1 rounded bg-[#eef4ff] px-2 py-0.5 text-[#206bc4]">
                        Search: "{{ $search }}"
                        <button type="button" wire:click="$set('search', '')" class="hover:text-red-600">&times;</button>
                    </span>
                @endif
                @if ($role !== '')
                    <span class="inline-flex items-center gap-1 rounded bg-[#eef4ff] px-2 py-0.5 text-[#206bc4]">
                        Role: {{ $roles->firstWhere('slug', $role)?->name ?? $role }}
                        <button type="button" wire:click="$set('role', '')" class="hover:text-red-600">&times;</button>
                    </span>
                @endif
                @if ($status !== '')
                    <span class="inline-flex items-center gap-1 rounded bg-[#eef4ff] px-2 py-0.5 text-[#206bc4]">
                        Status: {{ ucfirst($status) }}
                        <button type="button" wire:click="$set('status', '')" class="hover:text-red-600">&times;</button>
                    </span>
                @endif
                <button type="button" wire:click="clearFilters" class="ml-1 text-xs font-semibold text-[#206bc4] hover:underline">
                    Clear all
                </button>
            </div>
        @endif

        {{-- Loading feedback --}}
        <div wire:loading.flex wire:target="search, role, status, perPage, sortBy, clearFilters, applyBulkAction, $refresh" class="items-center gap-2 border-b border-[#eef1f6] bg-[#f7f9fc] px-4 py-2.5 text-xs font-medium text-[#64748b]">
            <span class="size-3 animate-spin rounded-full border-2 border-[#206bc4] border-t-transparent"></span>
            <span>Updating users...</span>
        </div>

        {{-- Table / Empty state --}}
        @if ($items->count())
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[760px] table-auto border-separate border-spacing-0 text-left">
                    <thead class="bg-[#f7f9fc] text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
                        <tr>
                            <th class="w-[44px] border-b border-[#eef1f6] px-3.5 py-3 text-center align-middle">
                                <input type="checkbox" 
                                       wire:click="toggleSelectAll" 
                                       @checked($items->isNotEmpty() && count(array_intersect($items->pluck('id')->all(), $selected)) === $items->count()) 
                                       class="size-4 rounded border-[#c7d1de] accent-[#206bc4] align-middle" 
                                       aria-label="Select all users on current page" />
                            </th>
                            <th class="w-[70px] border-b border-[#eef1f6] px-3.5 py-3 align-middle">
                                <button type="button" wire:click="sortBy('id')" class="group inline-flex items-center gap-1">
                                    <span>ID</span>
                                    @if ($sortField === 'id')
                                        <span class="text-[#206bc4]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-[#cbd5e1] group-hover:text-[#64748b]">↕</span>
                                    @endif
                                </button>
                            </th>
                            <th class="border-b border-[#eef1f6] px-3.5 py-3 align-middle">
                                <button type="button" wire:click="sortBy('name')" class="group inline-flex items-center gap-1">
                                    <span>USER</span>
                                    @if ($sortField === 'name')
                                        <span class="text-[#206bc4]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-[#cbd5e1] group-hover:text-[#64748b]">↕</span>
                                    @endif
                                </button>
                            </th>
                            <th class="w-[140px] border-b border-[#eef1f6] px-3.5 py-3 align-middle">
                                <button type="button" wire:click="sortBy('role')" class="group inline-flex items-center gap-1">
                                    <span>ROLE</span>
                                    @if ($sortField === 'role')
                                        <span class="text-[#206bc4]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-[#cbd5e1] group-hover:text-[#64748b]">↕</span>
                                    @endif
                                </button>
                            </th>
                            <th class="w-[120px] border-b border-[#eef1f6] px-3.5 py-3 align-middle">
                                <button type="button" wire:click="sortBy('is_active')" class="group inline-flex items-center gap-1">
                                    <span>STATUS</span>
                                    @if ($sortField === 'is_active')
                                        <span class="text-[#206bc4]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-[#cbd5e1] group-hover:text-[#64748b]">↕</span>
                                    @endif
                                </button>
                            </th>
                            <th class="w-[150px] border-b border-[#eef1f6] px-3.5 py-3 align-middle">
                                <button type="button" wire:click="sortBy('created_at')" class="group inline-flex items-center gap-1">
                                    <span>CREATED AT</span>
                                    @if ($sortField === 'created_at')
                                        <span class="text-[#206bc4]">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-[#cbd5e1] group-hover:text-[#64748b]">↕</span>
                                    @endif
                                </button>
                            </th>
                            <th class="w-[110px] border-b border-[#eef1f6] px-3.5 py-3 text-right align-middle">
                                <span>OPERATIONS</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eef1f6]">
                        @foreach ($items as $item)
                            <tr wire:key="user-{{ $item->id }}" class="bg-white transition hover:bg-[#f8fbff] even:bg-[#fcfdff]">
                                <td class="border-b border-[#eef1f6] px-3.5 py-3 text-center align-middle">
                                    <input type="checkbox" 
                                           class="size-4 rounded border-[#c7d1de] accent-[#206bc4] align-middle" 
                                           value="{{ $item->id }}" 
                                           @checked(in_array($item->id, $selected, true)) 
                                           wire:click="toggleSelected({{ $item->id }})" 
                                           aria-label="Select {{ $item->name }}" />
                                </td>
                                <td class="border-b border-[#eef1f6] px-3.5 py-3 text-sm font-medium text-[#172033] align-middle">
                                    {{ $item->id }}
                                </td>
                                <td class="border-b border-[#eef1f6] px-3.5 py-3 align-middle">
                                    @can('users.update')
                                    <a href="{{ route('admin.users.edit', $item) }}" 
                                       wire:navigate 
                                       class="text-sm font-semibold text-[#206bc4] transition hover:underline hover:text-[#1a569d]">
                                        {{ $item->name }}
                                    </a>
                                    @else
                                    <span class="text-sm font-semibold text-[#172033]">{{ $item->name }}</span>
                                    @endcan
                                    <p class="mt-0.5 text-xs text-[#64748b]">{{ $item->email }}</p>
                                </td>
                                <td class="border-b border-[#eef1f6] px-3.5 py-3 align-middle">
                                    @php
                                        $roleBadgeStyle = match($item->role) {
                                            \App\Models\Role::SUPER_ADMIN => 'border-[#cbd5e1] bg-[#f1f5f9] text-[#1e293b]',
                                            'admin' => 'border-blue-200 bg-blue-50 text-[#206bc4]',
                                            default => 'border-slate-200 bg-slate-50 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-[4px] border px-2 py-0.5 text-xs font-semibold {{ $roleBadgeStyle }}">
                                        {{ $item->role_label ?: 'No role' }}
                                    </span>
                                </td>
                                <td class="border-b border-[#eef1f6] px-3.5 py-3 align-middle">
                                    @if ($item->is_active)
                                        <span class="inline-flex items-center rounded-[4px] border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-[4px] border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="border-b border-[#eef1f6] px-3.5 py-3 text-sm text-[#64748b] align-middle">
                                    {{ $item->created_at?->format('Y-m-d') }}
                                </td>
                                <td class="border-b border-[#eef1f6] px-3.5 py-3 text-right align-middle">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @can('users.update')
                                        <a href="{{ route('admin.users.edit', $item) }}" 
                                           wire:navigate 
                                           class="inline-flex size-8 items-center justify-center rounded-[4px] bg-[#206bc4] text-white shadow-sm transition hover:bg-[#1a569d]" 
                                           title="Edit user" 
                                           aria-label="Edit {{ $item->name }}">
                                            <flux:icon name="pencil-square" class="size-4" />
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Table footer --}}
            <div class="flex flex-col gap-3 border-t border-[#eef1f6] bg-[#fcfdff] px-4 py-3 text-sm text-[#64748b] sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-1.5 text-xs text-[#64748b] sm:text-sm">
                    <flux:icon name="globe-alt" class="size-4 text-[#64748b]" />
                    <span>
                        Showing <span class="font-medium text-[#172033]">{{ $items->firstItem() ?? 0 }}</span> to <span class="font-medium text-[#172033]">{{ $items->lastItem() ?? 0 }}</span> of <span class="rounded-[5px] bg-[#6c84a4] px-1.5 py-0.5 text-xs font-semibold text-white">{{ $items->total() }}</span> users
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1.5 text-xs text-[#64748b]">
                        <label for="users-per-page">Per page:</label>
                        <select id="users-per-page" wire:model.live="perPage" class="h-8 rounded-[4px] border border-[#dbe2ed] bg-white px-2 text-xs text-[#172033] outline-none focus:border-[#206bc4]">
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div>
                        {{ $items->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        @else
            {{-- Empty states --}}
            <div class="flex flex-col items-center justify-center px-4 py-16 text-center">
                @if ($search !== '' || $role !== '' || $status !== '')
                    <span class="flex size-12 items-center justify-center rounded-full bg-[#f1f5f9] text-[#64748b]">
                        <flux:icon name="magnifying-glass" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-base font-bold text-[#172033]">No users match these filters</h3>
                    <p class="mt-1 max-w-sm text-xs text-[#64748b]">Try adjusting your search keywords, role selection, or status filters.</p>
                    <button type="button" wire:click="clearFilters" class="mt-4 inline-flex items-center justify-center rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 py-2 text-xs font-semibold text-[#206bc4] transition hover:bg-[#f7f9fc]">
                        Clear filters
                    </button>
                @else
                    <span class="flex size-12 items-center justify-center rounded-full bg-[#eef4ff] text-[#206bc4]">
                        <flux:icon name="user-group" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-base font-bold text-[#172033]">No users found</h3>
                    <p class="mt-1 max-w-sm text-xs text-[#64748b]">Get started by creating your first system user account.</p>
                    @can('users.create')
                    <a href="{{ route('admin.users.create') }}" wire:navigate class="mt-4 inline-flex items-center justify-center gap-1.5 rounded-[6px] bg-[#206bc4] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#1a569d]">
                        <flux:icon name="plus" class="size-3.5" />
                        <span>Create user</span>
                    </a>
                    @endcan
                @endif
            </div>
        @endif
    </section>
</div>
