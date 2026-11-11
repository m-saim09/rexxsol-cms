<div class="rex-content-form-root">
    <div class="rex-page-edit-page mx-auto w-full max-w-[1440px] space-y-5">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.users.index') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Users</a>
            <span>/</span>
            <span class="text-[#172033]">{{ $userId ? 'Edit' : 'Create' }} User</span>
        </nav>

        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#2563eb]">Account</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#172033]">{{ $userId ? 'Edit User' : 'Create User' }}</h1>
                <p class="mt-1 text-sm text-[#64748b]">Grant only the access this person needs.</p>
            </div>
            <span class="rex-page-edit-state {{ $is_active ? 'rex-page-edit-state--published' : 'rex-page-edit-state--draft' }}">{{ $is_active ? 'Active' : 'Inactive' }}</span>
        </div>

        <form wire:submit="save" class="rex-page-edit-grid">
            <main class="space-y-4">
                <section class="rex-page-edit-card">
                    <div class="rex-page-edit-card-heading">
                        <div>
                            <h2>Account details</h2>
                            <p>Update the user identity and access level.</p>
                        </div>
                    </div>
                    <div class="rex-page-edit-fields">
                        <div>
                            <label for="user-name">Name <span>*</span></label>
                            <input id="user-name" wire:model="name" type="text" autocomplete="name" value="{{ $name }}" />
                            @error('name')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="user-email">Email <span>*</span></label>
                            <input id="user-email" wire:model="email" type="email" autocomplete="email" value="{{ $email }}" />
                            @error('email')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        @if (! $userId)
                            <div>
                                <label for="user-password">Password <span>*</span></label>
                                <input id="user-password" wire:model="password" type="password" autocomplete="new-password" />
                                @error('password')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="user-password-confirmation">Confirm password <span>*</span></label>
                                <input id="user-password-confirmation" wire:model="password_confirmation" type="password" autocomplete="new-password" />
                                @error('password_confirmation')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                            </div>
                        @else
                            <div>
                                <label for="user-password-note">Password</label>
                                <div class="rounded-lg border border-[#dce4ef] bg-[#f8fafc] px-3 py-2.5 text-sm text-[#64748b]">Passwords are not displayed or changed here. The user can use the existing password reset flow.</div>
                            </div>
                        @endif
                    </div>
                </section>
            </main>

            <aside class="rex-page-edit-sidebar">
                <section class="rex-page-edit-card rex-page-edit-publish">
                    <div class="rex-page-edit-card-heading">
                        <h2>Access</h2>
                    </div>
                    <div class="space-y-3">
                        <button type="submit" wire:loading.attr="disabled" class="rex-page-edit-button rex-page-edit-button--primary">
                            <span wire:loading.remove wire:target="save">Save user</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <a href="{{ route('admin.users.index') }}" wire:navigate class="rex-page-edit-cancel">Cancel</a>
                    </div>
                </section>

                <section class="rex-page-edit-card">
                    <div class="rex-page-edit-card-heading">
                        <h2>Role & status</h2>
                    </div>
                    <div class="rex-page-edit-fields">
                        <div>
                            <label for="user-role">Role</label>
                            @if (auth()->user()->can('users.assign_roles'))
                                <select id="user-role" wire:model="role">
                                    @foreach ($assignableRoles as $option)
                                        <option value="{{ $option->slug }}" @selected($role === $option->slug)>{{ $option->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="rex-page-edit-help">{{ $currentRole?->name ?? 'Role assignment authority is required to create a user.' }}</p>
                            @endif
                            @error('role')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <label class="rex-page-edit-check"><input wire:model="is_active" type="checkbox" @checked($is_active) /> Active — allow CMS access</label>
                        <p class="rex-page-edit-help">Inactive users cannot access CMS pages or perform CMS actions. Super Admin protections remain enforced.</p>
                    </div>
                </section>
            </aside>
        </form>
    </div>
</div>
