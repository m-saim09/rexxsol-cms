<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    </head>
    <body x-data="{ sidebarOpen: false, sidebarCollapsed: false, init() { this.sidebarCollapsed = localStorage.getItem('rexxsol-cms-sidebar-collapsed') === 'true' } }" x-init="init()" class="min-h-screen bg-[#f7f9fc] text-[#172033] antialiased">
        <div class="rex-admin-shell min-h-screen bg-[#f7f9fc]">
            <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-[#172033]/40 lg:hidden" @click="sidebarOpen = false"></div>

            <x-admin.header :title="$title ?? 'Dashboard'" />

            <div class="rex-admin-body flex min-h-[calc(100vh-64px)]">
                <x-admin.sidebar />

                <main class="rex-admin-main min-w-0 flex-1 transition-[margin] duration-200 ease-out" :class="sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-[244px]'">
                    <div class="min-h-[calc(100vh-64px)] px-4 py-6 sm:px-6 lg:px-10 lg:py-8 {{ request()->routeIs('admin.roles.*') ? 'contain-paint' : '' }}">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group position="bottom end" x-on:toast-show.document="$el.showToast($event.detail); $nextTick(() => { $el.querySelectorAll('ui-close button').forEach((button) => button.setAttribute('aria-label', 'Dismiss notification')); $el.querySelectorAll('[data-flux-toast-dialog]').forEach((toast) => toast.setAttribute('role', toast.dataset.variant === 'danger' || toast.dataset.variant === 'warning' ? 'alert' : 'status')); })">
                <flux:toast position="bottom end" class="rex-cms-toast" />
            </flux:toast.group>
        @endpersist

        @fluxScripts

        <script>
            document.addEventListener('toast-show', () => {
                setTimeout(() => {
                    document.querySelectorAll('ui-toast-group ui-close button').forEach((button) => button.setAttribute('aria-label', 'Dismiss notification'));
                    document.querySelectorAll('[data-flux-toast-dialog]').forEach((toast) => toast.setAttribute('role', toast.dataset.variant === 'danger' ? 'alert' : 'status'));
                }, 50);
            });
        </script>

        @if ($cmsToast = session('cms_toast'))
            <div id="rex-cms-toast-flash" hidden data-duration="{{ $cmsToast['duration'] }}" data-heading="{{ e($cmsToast['heading']) }}" data-text="{{ e($cmsToast['text']) }}" data-variant="{{ e($cmsToast['variant']) }}"></div>
            <script>
                document.addEventListener('livewire:initialized', () => {
                    const flash = document.getElementById('rex-cms-toast-flash');
                    if (! flash) return;

                    window.dispatchEvent(new CustomEvent('toast-show', {
                        detail: {
                            duration: Number(flash.dataset.duration),
                            slots: { heading: flash.dataset.heading, text: flash.dataset.text },
                            dataset: { variant: flash.dataset.variant },
                        },
                    }));
                });
            </script>
        @endif
    </body>
</html>
