@props(['title' => 'Dashboard'])

@php
    $notificationRoute = auth()->user() && auth()->user()->can('contact_messages.view') ? route('admin.contact-messages') : route('admin.dashboard');
    $notificationCount = App\Models\ContactMessage::where('status', 'new')->count();
@endphp

<header class="sticky top-0 z-50 h-[64px] border-b border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.025)]">
    <div class="flex h-full items-center gap-2 px-4 sm:gap-3 sm:px-6 lg:px-8">
        <button type="button" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#64748b] transition hover:bg-[#f3f6fb] hover:text-[#172033] lg:hidden" @click="sidebarOpen = true" aria-label="Open navigation">
            <flux:icon name="bars-3" class="size-5" />
        </button>

        <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex shrink-0 items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="Rexxsol CMS" class="h-10 w-auto max-w-[180px] object-contain max-[399px]:max-w-[92px]" />
        </a>

        <button type="button" x-on:click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('rexxsol-cms-sidebar-collapsed', sidebarCollapsed)" class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#64748b] transition hover:bg-[#f3f6fb] hover:text-[#172033] lg:flex" :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'" :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
            <flux:icon name="chevron-double-left" class="size-5" x-show="!sidebarCollapsed" />
            <flux:icon name="chevron-double-right" class="size-5" x-show="sidebarCollapsed" x-cloak />
        </button>

        <div class="flex min-w-0 flex-1 items-center justify-center px-1 sm:px-3 max-[399px]:hidden">
            <form action="{{ route('admin.search') }}" method="GET" role="search" class="w-full max-w-[420px]">
                <label class="relative flex h-10 items-center gap-2 rounded-[9px] border border-[#e5eaf2] bg-[#f7f9fc] px-3 text-[#64748b] transition focus-within:border-[#2563eb] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#dbeafe]">
                    <flux:icon name="magnifying-glass" class="size-4 shrink-0" />
                    <input
                        type="search"
                        data-global-search
                        aria-label="Search"
                        name="q"
                        placeholder="Search anything..."
                        class="w-full border-0 bg-transparent text-sm text-[#172033] placeholder:text-[#64748b] focus:outline-none"
                    />
                    <span class="hidden items-center gap-1 rounded-md border border-[#e5eaf2] bg-white px-1.5 py-0.5 text-[10px] font-medium text-[#64748b] sm:inline-flex">
                        <span>Alt</span>
                        <span class="rounded border border-[#e5eaf2] bg-[#f7f9fc] px-1 py-0.5 text-[10px] font-semibold text-[#475569]">K</span>
                    </span>
                </label>
            </form>
        </div>

        <div class="flex shrink-0 items-center justify-end gap-1 sm:gap-3">
            <a href="{{ route('home') }}" wire:navigate class="hidden items-center gap-2 rounded-lg border border-[#e5eaf2] bg-white px-3 py-2 text-sm font-medium text-[#172033] transition hover:bg-[#f7f9fc] sm:inline-flex">
                <flux:icon name="arrow-top-right-on-square" class="size-4 text-[#64748b]" />
                <span class="hidden md:inline">View Website</span>
            </a>
            <a
                href="{{ $notificationRoute }}"
                wire:navigate
                class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-[#e5eaf2] bg-white text-[#64748b] transition hover:border-[#dfe8f5] hover:bg-[#f3f6fb] hover:text-[#172033]"
                aria-label="Notifications"
            >
                <flux:icon name="bell" class="size-5" />
                @if ($notificationCount > 0)
                    <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#ef4444] px-1 text-[10px] font-semibold text-white">
                        {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                    </span>
                @endif
            </a>

            <button
                type="button"
                aria-label="Toggle theme"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#e5eaf2] bg-white text-[#64748b] transition hover:border-[#dfe8f5] hover:bg-[#f3f6fb] hover:text-[#172033]"
            >
                <flux:icon name="sun" class="size-5" />
            </button>

            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button
                    type="button"
                    x-on:click="open = !open"
                    :aria-expanded="open"
                    aria-label="Open user menu"
                    class="flex items-center gap-2 rounded-xl border border-transparent px-1 py-1.5 text-left transition hover:bg-[#f7f9fc] focus:outline-none focus:ring-2 focus:ring-[#bfdbfe] sm:gap-3 sm:px-2"
                >
                    <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" class="size-9" />

                    <span class="hidden min-w-0 text-left sm:block">
                        <span class="block truncate text-sm font-semibold text-[#172033]">{{ auth()->user()->name }}</span>
                        <span class="block truncate text-[11px] text-[#64748b]">{{ auth()->user()->role_label }}</span>
                    </span>

                    <flux:icon name="chevron-down" class="hidden size-4 text-[#64748b] sm:block" />
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    x-cloak
                    class="absolute right-0 top-[calc(100%+12px)] z-50 w-[220px] rounded-2xl border border-[#e5eaf2] bg-white p-2 shadow-[0_12px_30px_rgba(15,23,42,0.10)]"
                >
                    <div class="px-3 py-2">
                        <p class="text-sm font-semibold text-[#172033]">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#64748b]">{{ auth()->user()->email }}</p>
                        <p class="mt-1 text-[11px] font-medium uppercase tracking-[0.08em] text-[#64748b]">{{ auth()->user()->role_label }}</p>
                    </div>

                    <div class="my-1 h-px bg-[#e5eaf2]"></div>

                    <div class="space-y-1">
                        <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-[#172033] transition hover:bg-[#f7f9fc]">
                            <flux:icon name="user-circle" class="size-4 text-[#64748b]" />
                            My Profile
                        </a>

                        <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-[#172033] transition hover:bg-[#f7f9fc]">
                            <flux:icon name="cog-6-tooth" class="size-4 text-[#64748b]" />
                            Account Settings
                        </a>

                        <div class="my-1 h-px bg-[#e5eaf2]"></div>

                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-[#172033] transition hover:bg-[#f7f9fc]">
                            <flux:icon name="arrow-top-right-on-square" class="size-4 text-[#64748b]" />
                            View Website
                        </a>

                        <div class="my-1 h-px bg-[#e5eaf2]"></div>

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-[#dc2626] transition hover:bg-[#fef2f2]" aria-label="Sign out">
                                <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
