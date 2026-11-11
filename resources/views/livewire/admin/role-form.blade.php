<div class="mx-auto w-full max-w-[1440px] space-y-4">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#206bc4]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:underline">Dashboard</a>
        <span class="text-[#94a3b8]">/</span>
        <a href="{{ route('admin.platform') }}" wire:navigate class="hover:underline">Administration</a>
        <span class="text-[#94a3b8]">/</span>
        <a href="{{ route('admin.roles.index') }}" wire:navigate class="hover:underline">Roles &amp; Permissions</a>
        <span class="text-[#94a3b8]">/</span>
        <span class="text-[#64748b]">{{ $role ? 'Edit role' : 'Create role' }}</span>
    </nav>
    <header>
        <h1 class="text-xl font-bold tracking-tight text-[#172033]">{{ $role ? 'Edit role' : 'Create role' }}</h1>
        <p class="mt-1 text-sm text-[#64748b]">Define roles and control permissions.</p>
    </header>

    <form wire:submit="save" x-data="{ dirty: false, syncSelection(element, selected, visible) { const count = visible.filter(key => selected.includes(key)).length; element.checked = visible.length > 0 && count === visible.length; element.indeterminate = count > 0 && count < visible.length; } }" @input="dirty = true" @change="dirty = true"
          @beforeunload.window="if (dirty) $event.returnValue = 'You have unsaved role changes.'"
          x-on:livewire:navigate.window="if (dirty && !confirm('You have unsaved role changes. Leave this page?')) $event.preventDefault(); else dirty = false"
          x-on:role-saved.window="dirty = false"
          class="grid grid-cols-1 items-start gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">
        <div class="min-w-0 space-y-4">
            <section class="overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <header class="border-b border-[#eef1f6] px-5 py-4">
                    <h2 class="text-sm font-bold text-[#172033]">Role details</h2>
                    <p class="mt-1 text-xs text-[#64748b]">Set a clear name and description for this access profile.</p>
                </header>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="role-name" class="mb-1.5 block text-xs font-semibold text-[#334155]">Role name <span class="text-red-600">*</span></label>
                        <input id="role-name" wire:model="name" required maxlength="100" autocomplete="off"
                            @disabled($role?->slug === \App\Models\Role::SUPER_ADMIN)
                            class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10 disabled:bg-[#f7f9fc]">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="role-description" class="mb-1.5 block text-xs font-semibold text-[#334155]">Description</label>
                        <textarea id="role-description" wire:model="description" rows="3" maxlength="1000" @disabled($role?->slug === \App\Models\Role::SUPER_ADMIN)
                            class="w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 py-2 text-sm text-[#172033] outline-none focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10 disabled:bg-[#f7f9fc]"></textarea>
                        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @if ($role)
                        <div class="sm:col-span-2">
                            <span class="block text-xs font-semibold text-[#334155]">Stable identifier</span>
                            <code class="mt-1 inline-block rounded bg-[#f7f9fc] px-2 py-1 text-xs text-[#64748b]">{{ $role->slug }}</code>
                        </div>
                    @endif
                </div>
            </section>

            <section class="overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <header class="flex flex-col gap-3 border-b border-[#eef1f6] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[#172033]">Permissions</h2>
                        <p class="mt-1 text-xs text-[#64748b]">{{ $permissionCount }} selected · selections outside a module search are retained.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="inline-flex items-center gap-2 rounded-[6px] border border-[#dbe2ed] px-2.5 py-2 text-xs font-medium text-[#334155]">
                            <input type="checkbox"
                                wire:key="visible-permissions-{{ md5(implode('|', $visiblePermissionKeys)) }}"
                                x-data="{ selected: $wire.entangle('selectedPermissions'), visible: @js($visiblePermissionKeys) }"
                                x-effect="syncSelection($el, selected, visible)"
                                @change="$wire.toggleVisible($event.target.checked)"
                                @disabled($role?->slug === \App\Models\Role::SUPER_ADMIN || ! auth()->user()->can('roles.manage_permissions'))
                                class="rounded border-[#cbd5e1] text-[#206bc4] focus:ring-[#206bc4]">
                            Select visible
                        </label>
                        <input type="search" wire:model.live.debounce.200ms="moduleSearch" aria-label="Search permission modules" placeholder="Search permissions..."
                            class="h-9 w-full rounded-[6px] border border-[#dbe2ed] px-3 text-xs text-[#172033] focus:border-[#206bc4] focus:outline-none focus:ring-2 focus:ring-[#206bc4]/10 sm:w-52">
                    </div>
                </header>
                @error('selectedPermissions')<p role="alert" class="mx-5 mt-4 text-xs text-red-600">{{ $message }}</p>@enderror
                <div class="p-4 sm:p-5">
                    @forelse ($modules as $section)
                        <section class="mb-5 last:mb-0" aria-label="{{ $section['group'] }} permissions">
                            <h3 class="mb-2 text-[11px] font-bold uppercase tracking-[0.12em] text-[#64748b]">{{ $section['group'] }}</h3>
                            <div class="overflow-x-auto rounded-[6px] border border-[#e5eaf2]">
                                <table class="w-full min-w-[680px] text-left text-xs">
                                    <thead class="bg-[#f7f9fc] text-[10px] font-bold uppercase tracking-[0.08em] text-[#64748b]">
                                        <tr><th scope="col" class="w-48 px-3 py-2.5">Module</th><th scope="col" class="px-2 py-2.5 text-center">View</th><th scope="col" class="px-2 py-2.5 text-center">Create / Upload</th><th scope="col" class="px-2 py-2.5 text-center">Edit / Manage</th><th scope="col" class="px-2 py-2.5 text-center">Delete</th><th scope="col" class="px-2 py-2.5 text-center">Publish / Other</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#eef1f6]">
                                        @foreach ($section['modules'] as $module)
                                            @php
                                                $moduleSelected = count(array_intersect($module['keys'], $selectedPermissions));
                                                $moduleActions = ['view' => ['view'], 'create' => ['create', 'upload'], 'update' => ['update', 'manage'], 'delete' => ['delete'], 'other' => ['publish', 'assign_roles', 'manage_permissions']];
                                            @endphp
                                            <tr wire:key="permission-module-{{ \Illuminate\Support\Str::slug($module['name']) }}">
                                                <th scope="row" class="px-3 py-2.5 font-semibold text-[#334155]">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span>{{ $module['name'] }}</span>
                                                        <button type="button" wire:click="toggleModule('{{ $module['name'] }}')" aria-label="Toggle all visible {{ $module['name'] }} permissions" @disabled($role?->slug === \App\Models\Role::SUPER_ADMIN || ! auth()->user()->can('roles.manage_permissions')) class="rounded px-1 text-[10px] font-medium text-[#206bc4] hover:bg-[#eef4ff] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/30 disabled:opacity-50">
                                                            {{ $moduleSelected === count($module['keys']) ? 'Clear' : 'All' }}
                                                        </button>
                                                    </div>
                                                </th>
                                                @foreach ($moduleActions as $actionKeys)
                                                    <td class="px-2 py-2.5 text-center">
                                                        @php
                                                            $keys = array_values(array_filter($module['keys'], fn (string $key) => in_array(str($key)->afterLast('.')->toString(), $actionKeys, true)));
                                                        @endphp
                                                        @if ($keys)
                                                            <div class="flex flex-wrap justify-center gap-x-2 gap-y-1">
                                                                @foreach ($keys as $permissionKey)
                                                                    <label class="inline-flex items-center gap-1.5 text-[#475569]" title="{{ $permissionDefinitions[$permissionKey][1] }}">
                                                                        @php
                                                                            $permissionAction = str($permissionKey)->afterLast('.')->toString();
                                                                            $viewKey = str($permissionKey)->beforeLast('.')->append('.view')->toString();
                                                                            $requiresView = in_array($permissionAction, ['create', 'update', 'delete', 'publish', 'upload', 'manage', 'assign_roles', 'manage_permissions'], true) && isset($permissionDefinitions[$viewKey]);
                                                                            $hasDependents = collect($module['keys'])->contains(fn (string $key) => in_array(str($key)->afterLast('.')->toString(), ['create', 'update', 'delete', 'publish', 'upload', 'manage', 'assign_roles', 'manage_permissions'], true) && in_array($key, $selectedPermissions, true));
                                                                        @endphp
                                                                        <input type="checkbox" wire:click="togglePermission('{{ $permissionKey }}')" value="{{ $permissionKey }}" aria-label="{{ $permissionDefinitions[$permissionKey][1] }}"
                                                                            @checked(in_array($permissionKey, $selectedPermissions, true))
                                                                            @disabled($role?->slug === \App\Models\Role::SUPER_ADMIN || ! auth()->user()->can('roles.manage_permissions') || ($requiresView && ! in_array($viewKey, $selectedPermissions, true)) || ($permissionAction === 'view' && $hasDependents))
                                                                            class="rounded border-[#cbd5e1] text-[#206bc4] focus:ring-[#206bc4] disabled:opacity-50">
                                                                        <span class="sr-only">{{ $permissionDefinitions[$permissionKey][1] }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-[#cbd5e1]" aria-label="Not supported">—</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @empty
                        <p class="rounded-[6px] border border-dashed border-[#dbe2ed] px-4 py-8 text-center text-sm text-[#64748b]">No permission modules match your search.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-4 xl:sticky xl:top-20">
            <section class="rounded-[8px] border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <h2 class="text-sm font-bold text-[#172033]">Save changes</h2>
                <div class="mt-4 space-y-2">
                    @unless ($role?->slug === \App\Models\Role::SUPER_ADMIN)
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-[6px] bg-[#206bc4] px-4 text-sm font-semibold text-white hover:bg-[#1a569d] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#206bc4]/40 disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">Save role</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    @else
                        <p class="rounded-[6px] bg-[#f7f9fc] p-3 text-xs text-[#64748b]">Super Admin is protected and has full access. Its name and grants cannot be changed.</p>
                    @endunless
                    <a href="{{ route('admin.roles.index') }}" wire:navigate class="inline-flex h-9 w-full items-center justify-center rounded-[6px] border border-[#dbe2ed] text-sm font-medium text-[#334155] hover:bg-[#f7f9fc]">Cancel</a>
                </div>
            </section>
            <section class="rounded-[8px] border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <h2 class="text-sm font-bold text-[#172033]">Role summary</h2>
                <dl class="mt-3 space-y-3 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-[#64748b]">Selected permissions</dt><dd class="font-semibold text-[#172033]">{{ $permissionCount }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-[#64748b]">Assigned users</dt><dd class="font-semibold text-[#172033]">{{ $userCount }}</dd></div>
                    @if ($role)
                        <div class="flex justify-between gap-3"><dt class="text-[#64748b]">Role type</dt><dd class="font-semibold text-[#172033]">{{ $role->is_system ? 'System role' : 'Custom role' }}</dd></div>
                    @endif
                </dl>
                @if ($role?->is_system && $role->slug !== \App\Models\Role::SUPER_ADMIN)
                    <p class="mt-4 rounded-[6px] border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-800">System role identifiers and deletion are protected so existing user assignments remain valid.</p>
                @endif
            </section>
        </aside>
    </form>
</div>
