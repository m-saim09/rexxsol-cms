<div class="mx-auto w-full max-w-[1440px] space-y-4">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#206bc4]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
        <span class="text-[#94a3b8]">/</span>
        <a href="{{ route('admin.platform') }}" wire:navigate class="hover:underline">Administration</a>
        <span class="text-[#94a3b8]">/</span>
        <span class="text-[#64748b]">Roles &amp; Permissions</span>
    </nav>

    <header>
        <h1 class="text-xl font-bold tracking-tight text-[#172033]">Roles &amp; Permissions</h1>
        <p class="mt-1 text-sm text-[#64748b]">Define roles and control permissions.</p>
    </header>

    <section class="overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]" wire:loading.class="opacity-70" wire:target="search,perPage,reload,delete">
        <div class="flex flex-col gap-3 border-b border-[#eef1f6] p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-sm">
                <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[#94a3b8]" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search roles..." aria-label="Search roles"
                    class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white pl-9 pr-3 text-sm text-[#172033] outline-none transition placeholder:text-[#94a3b8] focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" />
            </div>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="reload" wire:loading.attr="disabled" wire:target="reload"
                    class="inline-flex h-10 items-center gap-2 rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm font-medium text-[#172033] hover:bg-[#f7f9fc] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/30 disabled:opacity-60">
                    <flux:icon name="arrow-path" class="size-4" /> Reload
                </button>
                @can('roles.create')
                <a href="{{ route('admin.roles.create') }}" wire:navigate
                    class="inline-flex h-10 items-center gap-2 rounded-[6px] bg-[#206bc4] px-3.5 text-sm font-semibold text-white transition hover:bg-[#1a569d] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/40">
                    <flux:icon name="plus" class="size-4" /> Create role
                </a>
                @endcan
            </div>
        </div>

        @error('delete')
            <div role="alert" class="mx-4 mt-4 rounded-[6px] border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead class="bg-[#f7f9fc] text-[11px] font-bold uppercase tracking-[0.1em] text-[#64748b]">
                    <tr>
                        <th scope="col" class="px-4 py-3">ID</th>
                        <th scope="col" class="px-4 py-3">Role</th>
                        <th scope="col" class="px-4 py-3">Description</th>
                        <th scope="col" class="px-4 py-3">Users</th>
                        <th scope="col" class="px-4 py-3">Updated at</th>
                        <th scope="col" class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eef1f6]">
                    @forelse ($roles as $role)
                        <tr wire:key="role-{{ $role->id }}" class="transition hover:bg-[#fbfcfe]">
                            <td class="px-4 py-3 text-[#64748b]">{{ $role->id }}</td>
                            <th scope="row" class="px-4 py-3 font-semibold text-[#172033]">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span>{{ $role->name }}</span>
                                    @if ($role->is_system)
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">System role</span>
                                    @endif
                                </div>
                                <span class="mt-0.5 block text-xs font-normal text-[#94a3b8]">{{ $role->slug }} · {{ $role->permissions_count }} permissions</span>
                            </th>
                            <td class="max-w-[360px] px-4 py-3 text-[#64748b]">{{ $role->description ?: '—' }}</td>
                            <td class="px-4 py-3 text-[#172033]">{{ $role->users_count }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-[#64748b]">{{ $role->updated_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @can('roles.update')
                                    <a href="{{ route('admin.roles.edit', $role) }}" wire:navigate aria-label="Edit {{ $role->name }}"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-[5px] px-2.5 text-xs font-semibold text-[#206bc4] hover:bg-[#eef4ff] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/30">
                                        <flux:icon name="pencil-square" class="size-4" /> Edit
                                    </a>
                                    @endcan
                                    @can('roles.delete')
                                    @unless ($role->is_system || $role->slug === \App\Models\Role::SUPER_ADMIN)
                                        <button type="button" wire:click="delete({{ $role->id }})" wire:confirm="Delete the {{ $role->name }} role?"
                                            aria-label="Delete {{ $role->name }}" wire:loading.attr="disabled" wire:target="delete"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-[5px] px-2.5 text-xs font-semibold text-red-600 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 disabled:opacity-50">
                                            <flux:icon name="trash" class="size-4" /> Delete
                                        </button>
                                    @endunless
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <flux:icon name="shield-check" class="mx-auto size-8 text-[#94a3b8]" />
                                <p class="mt-3 text-sm font-semibold text-[#172033]">{{ $search !== '' ? 'No roles match your search' : 'No roles configured' }}</p>
                                @if ($search === '')
                                    <p class="mt-1 text-xs text-[#64748b]">Create a role to define access for CMS users.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="flex flex-col gap-3 border-t border-[#eef1f6] bg-[#fbfcfe] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-[#64748b]">
                @if ($roles->total())
                    Showing {{ $roles->firstItem() }}–{{ $roles->lastItem() }} of {{ $roles->total() }} roles
                @else
                    Showing 0 roles
                @endif
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <label for="role-page-size" class="text-xs text-[#64748b]">Rows</label>
                <select id="role-page-size" wire:model.live="perPage" class="h-8 rounded-[5px] border border-[#dbe2ed] bg-white px-2 text-xs text-[#172033] focus:border-[#206bc4] focus:outline-none focus:ring-2 focus:ring-[#206bc4]/10">
                    <option value="10">10</option><option value="25">25</option><option value="50">50</option>
                </select>
                {{ $roles->onEachSide(1)->links() }}
            </div>
        </footer>
    </section>
</div>
