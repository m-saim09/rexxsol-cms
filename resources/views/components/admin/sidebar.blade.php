<aside
    x-cloak
    x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed left-0 top-[64px] z-50 flex h-[calc(100vh-64px)] w-[244px] -translate-x-full flex-col overflow-x-hidden overflow-y-auto border-r border-[#e5eaf2] bg-white text-[#334155] transition-[width,transform] duration-200 ease-out lg:translate-x-0"
    :class="sidebarCollapsed ? 'lg:w-[72px] rex-sidebar-collapsed' : 'lg:w-[244px]'"
>
    {{-- Mobile Close --}}
    <div class="flex items-center justify-end border-b border-[#eef1f6] px-3 py-3 lg:hidden">
        <button
            type="button"
            class="text-[#64748b]"
            @click="sidebarOpen = false"
            aria-label="Close navigation"
        >
            <flux:icon name="x-mark" class="size-5" />
        </button>
    </div>

    <nav class="flex-1 space-y-6 px-3 py-5">

        {{-- =========================================================
            MAIN
        ========================================================== --}}
        <div>
            <p
                x-show="!sidebarCollapsed"
                class="px-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#94a3b8]"
            >
                Main
            </p>

            <div class="mt-3 space-y-1">
                <a
                    href="{{ route('admin.dashboard') }}"
                    wire:navigate
                    data-tooltip="Dashboard"
                    class="relative flex items-center rounded-lg py-2.5 text-sm font-semibold transition
                    {{ request()->routeIs('admin.dashboard')
                        ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                        : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                    }}"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                    aria-label="Dashboard"
                >
                    <flux:icon
                        name="squares-2x2"
                        class="size-5 shrink-0"
                    />

                    <span
                        x-show="!sidebarCollapsed"
                        class="rex-nav-label"
                    >
                        Dashboard
                    </span>
                </a>
            </div>
        </div>


        {{-- =========================================================
            WEBSITE
        ========================================================== --}}
        <div>
            <p
                x-show="!sidebarCollapsed"
                class="px-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#94a3b8]"
            >
                Website
            </p>

            <div class="mt-3 space-y-1">

                @can('homepage_builder.view')
                    <a
                        href="{{ route('admin.homepage') }}"
                        wire:navigate
                        data-tooltip="Homepage Builder"
                        class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('admin.homepage*')
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="Homepage Builder"
                    >
                        <flux:icon name="squares-plus" class="size-5 shrink-0" />
                        <span x-show="!sidebarCollapsed" class="rex-nav-label">Homepage Builder</span>
                    </a>
                @endcan

                {{-- Pages --}}
                <a
                    href="{{ route('admin.pages.index') }}"
                    wire:navigate
                    data-tooltip="Pages"
                    class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                    {{ request()->routeIs('admin.pages.*')
                        ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                        : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                    }}"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                    aria-label="Pages"
                >
                    <flux:icon
                        name="document-text"
                        class="size-5 shrink-0"
                    />

                    <span
                        x-show="!sidebarCollapsed"
                        class="rex-nav-label"
                    >
                        Pages
                    </span>
                </a>


                {{-- =====================================================
                    BLOG DROPDOWN
                ====================================================== --}}
                @php
                    /*
                    |--------------------------------------------------------------------------
                    | Blog Dropdown
                    |--------------------------------------------------------------------------
                    | A child is shown only when its named route actually exists.
                    | This prevents broken / 404 sidebar links.
                    */

                    $possibleBlogChildren = [
                        [
                            'label' => 'Posts',
                            'route' => 'admin.blog.index',
                            'icon' => 'document-text',
                            'active' => [
                                'admin.blog.index',
                                'admin.blog.create',
                                'admin.blog.edit',
                                'admin.blog.show',
                                'admin.blog.detail',
                            ],
                        ],
                        [
                            'label' => 'Categories',
                            'route' => 'admin.blog.categories.index',
                            'icon' => 'folder',
                            'active' => [
                                'admin.blog.categories.*',
                            ],
                        ],
                        [
                            'label' => 'Tags',
                            'route' => 'admin.blog.tags.index',
                            'icon' => 'tag',
                            'active' => [
                                'admin.blog.tags.*',
                            ],
                        ],
                        [
                            'label' => 'Reports',
                            'route' => 'admin.blog.reports.index',
                            'icon' => 'chart-bar',
                            'active' => [
                                'admin.blog.reports.*',
                            ],
                        ],
                    ];

                    $blogChildren = collect($possibleBlogChildren)
                        ->filter(
                            fn ($child) =>
                                \Illuminate\Support\Facades\Route::has($child['route'])
                        )
                        ->values()
                        ->all();

                    $blogIsActive = request()->routeIs('admin.blog.*');
                @endphp


                <div
                    x-data="{
                        blogMenu: {
                            open: {{ $blogIsActive ? 'true' : 'false' }},
                            flyoutOpen: false
                        }
                    }"
                    class="relative"
                >

                    {{-- ===============================
                        EXPANDED BLOG PARENT
                    ================================ --}}
                    <button
                        type="button"
                        x-show="!sidebarCollapsed"
                        @click="blogMenu.open = !blogMenu.open"
                        class="relative flex w-full items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ $blogIsActive
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="Blog"
                        aria-controls="sidebar-blog-submenu"
                        :aria-expanded="blogMenu.open.toString()"
                    >
                        <flux:icon
                            name="newspaper"
                            class="size-5 shrink-0"
                        />

                        <span
                            x-show="!sidebarCollapsed"
                            class="rex-nav-label"
                        >
                            Blog
                        </span>


                        {{-- Closed = Down --}}
                        <span
                            x-show="!blogMenu.open"
                            class="ml-auto flex size-4 shrink-0 items-center justify-center"
                        >
                            <flux:icon
                                name="chevron-down"
                                class="size-4"
                            />
                        </span>


                        {{-- Open = Up --}}
                        <span
                            x-cloak
                            x-show="blogMenu.open"
                            class="ml-auto flex size-4 shrink-0 items-center justify-center"
                        >
                            <flux:icon
                                name="chevron-up"
                                class="size-4"
                            />
                        </span>
                    </button>


                    {{-- ===============================
                        COLLAPSED BLOG BUTTON
                    ================================ --}}
                    <button
                        x-ref="blogCollapsedButton"
                        type="button"
                        x-show="sidebarCollapsed"
                        @click="blogMenu.flyoutOpen = !blogMenu.flyoutOpen"
                        @mouseenter="blogMenu.flyoutOpen = true"
                        @focus="blogMenu.flyoutOpen = true"
                        class="mx-auto flex h-10 w-10 items-center justify-center rounded-lg transition
                        {{ $blogIsActive
                            ? 'bg-[#eef4ff] text-[#000080]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        aria-label="Blog"
                        :aria-expanded="blogMenu.flyoutOpen.toString()"
                    >
                        <flux:icon
                            name="newspaper"
                            class="size-5"
                        />
                    </button>


                    {{-- ===============================
                        EXPANDED SUBMENU
                    ================================ --}}
                    <div
                        id="sidebar-blog-submenu"
                        x-cloak
                        x-show="!sidebarCollapsed && blogMenu.open"
                        x-transition:enter="transition ease-out duration-[180ms]"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-[180ms]"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="rex-submenu"
                    >
                        @forelse ($blogChildren as $child)

                            @php
                                $isChildActive = request()->routeIs(
                                    ...$child['active']
                                );
                            @endphp

                            <x-admin.submenu-link href="{{ route($child['route']) }}" :active="$isChildActive">{{ $child['label'] }}</x-admin.submenu-link>

                        @empty

                            <div class="px-3 py-2 text-xs text-[#94a3b8]">
                                No Blog modules available.
                            </div>

                        @endforelse
                    </div>


                    {{-- ===============================
                        COLLAPSED FLYOUT
                    ================================ --}}
                    <div
                        x-cloak
                        x-show="sidebarCollapsed && blogMenu.flyoutOpen"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-x-1"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-1"
                        @mouseenter="blogMenu.flyoutOpen = true"
                        @mouseleave="blogMenu.flyoutOpen = false"
                        @click.outside="blogMenu.flyoutOpen = false"
                        class="fixed z-[80] w-[190px] rounded-xl border border-[#e5eaf2] bg-white p-1.5 shadow-[0_12px_28px_rgba(15,23,42,0.10)]"
                        :style="`
                            left: 80px;
                            top: ${$refs.blogCollapsedButton
                                ? $refs.blogCollapsedButton.getBoundingClientRect().top
                                : 120}px;
                        `"
                    >
                        <div class="px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-[#94a3b8]">
                            Blog
                        </div>

                        <div class="mt-1 space-y-1">
                            @forelse ($blogChildren as $child)

                                @php
                                    $isChildActive = request()->routeIs(
                                        ...$child['active']
                                    );
                                @endphp

                                <x-admin.submenu-link href="{{ route($child['route']) }}" :active="$isChildActive" :flyout="true" @click="blogMenu.flyoutOpen = false">{{ $child['label'] }}</x-admin.submenu-link>

                            @empty

                                <div class="px-3 py-2 text-xs text-[#94a3b8]">
                                    No Blog modules available.
                                </div>

                            @endforelse
                        </div>
                    </div>

                </div>


                {{-- Services --}}
                <a
                    href="{{ route('admin.services.index') }}"
                    wire:navigate
                    data-tooltip="Services"
                    class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                    {{ request()->routeIs('admin.services.*')
                        ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                        : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                    }}"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                    aria-label="Services"
                >
                    <flux:icon
                        name="briefcase"
                        class="size-5 shrink-0"
                    />

                    <span
                        x-show="!sidebarCollapsed"
                        class="rex-nav-label"
                    >
                        Services
                    </span>
                </a>


                {{-- Projects --}}
                <a
                    href="{{ route('admin.projects.index') }}"
                    wire:navigate
                    data-tooltip="Projects"
                    class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                    {{ request()->routeIs('admin.projects.*')
                        ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                        : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                    }}"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                    aria-label="Projects"
                >
                    <flux:icon
                        name="rectangle-stack"
                        class="size-5 shrink-0"
                    />

                    <span
                        x-show="!sidebarCollapsed"
                        class="rex-nav-label"
                    >
                        Projects
                    </span>
                </a>


                {{-- Team --}}
                <a
                    href="{{ route('admin.team.index') }}"
                    wire:navigate
                    data-tooltip="Team"
                    class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                    {{ request()->routeIs('admin.team.*')
                        ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                        : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                    }}"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                    aria-label="Team"
                >
                    <flux:icon
                        name="users"
                        class="size-5 shrink-0"
                    />

                    <span
                        x-show="!sidebarCollapsed"
                        class="rex-nav-label"
                    >
                        Team
                    </span>
                </a>


                {{-- Testimonials --}}
                <a
                    href="{{ route('admin.testimonials.index') }}"
                    wire:navigate
                    data-tooltip="Testimonials"
                    class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                    {{ request()->routeIs('admin.testimonials.*')
                        ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                        : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                    }}"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                    aria-label="Testimonials"
                >
                    <flux:icon
                        name="chat-bubble-left-right"
                        class="size-5 shrink-0"
                    />

                    <span
                        x-show="!sidebarCollapsed"
                        class="rex-nav-label"
                    >
                        Testimonials
                    </span>
                </a>


                {{-- FAQs --}}
                <a
                    href="{{ route('admin.faqs.index') }}"
                    wire:navigate
                    data-tooltip="FAQs"
                    class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                    {{ request()->routeIs('admin.faqs.*')
                        ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                        : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                    }}"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                    aria-label="FAQs"
                >
                    <flux:icon
                        name="question-mark-circle"
                        class="size-5 shrink-0"
                    />

                    <span
                        x-show="!sidebarCollapsed"
                        class="rex-nav-label"
                    >
                        FAQs
                    </span>
                </a>


            </div>
        </div>


        {{-- =========================================================
            APPEARANCE, PLUGINS, AND TOOLS
        ========================================================== --}}
        @php
            $appearanceChildren = [
                ['label' => 'Themes', 'route' => 'admin.appearance.themes', 'active' => 'admin.appearance.themes'],
                ['label' => 'Menus', 'route' => 'admin.appearance.menus', 'active' => 'admin.appearance.menus'],
                ['label' => 'Footer Settings', 'route' => 'admin.appearance.footer', 'active' => 'admin.appearance.footer'],
                ['label' => 'Nova Footer', 'route' => 'admin.appearance.nova-footer', 'active' => 'admin.appearance.nova-footer'],
                ['label' => 'Theme Options', 'route' => 'admin.appearance.theme-options', 'active' => 'admin.appearance.theme-options'],
                ['label' => 'Custom CSS', 'route' => 'admin.appearance.custom-css', 'active' => 'admin.appearance.custom-css'],
                ['label' => 'Custom HTML', 'route' => 'admin.appearance.custom-html', 'active' => 'admin.appearance.custom-html'],
                ['label' => 'Robots.txt Editor', 'route' => 'admin.appearance.robots-txt', 'active' => 'admin.appearance.robots-txt'],
            ];
            $appearanceActive = request()->routeIs('admin.appearance.*');
            $pluginActive = request()->routeIs('admin.plugins.*');
            $toolsActive = request()->routeIs('admin.tools.*');
        @endphp
        <div class="space-y-1">
            <div x-data="{ open: {{ $appearanceActive ? 'true' : 'false' }} }">
                <button type="button" class="relative flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ $appearanceActive ? 'bg-[#eef4ff] text-[#000080]' : 'text-[#334155] hover:bg-[#f3f6fb]' }}" @click="open = !open" aria-controls="sidebar-appearance-submenu" :aria-expanded="open.toString()"><flux:icon name="paint-brush" class="size-5 shrink-0" /><span x-show="!sidebarCollapsed" class="rex-nav-label">Appearance</span><flux:icon name="chevron-down" class="ml-auto size-4" x-show="!open && !sidebarCollapsed" /><flux:icon name="chevron-up" class="ml-auto size-4" x-show="open && !sidebarCollapsed" /></button>
                <div id="sidebar-appearance-submenu" x-cloak x-transition.opacity.duration.180ms x-show="open && !sidebarCollapsed" class="rex-submenu">
                    @foreach ($appearanceChildren as $child)<x-admin.submenu-link href="{{ route($child['route']) }}" :active="request()->routeIs($child['active'], $child['active'].'.*')">{{ $child['label'] }}</x-admin.submenu-link>@endforeach
                </div>
            </div>
            <div x-data="{ open: {{ $pluginActive ? 'true' : 'false' }} }"><button type="button" class="relative flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ $pluginActive ? 'bg-[#eef4ff] text-[#000080]' : 'text-[#334155] hover:bg-[#f3f6fb]' }}" @click="open = !open" aria-controls="sidebar-plugins-submenu" :aria-expanded="open.toString()"><flux:icon name="puzzle-piece" class="size-5 shrink-0" /><span x-show="!sidebarCollapsed" class="rex-nav-label">Plugins</span><flux:icon name="chevron-down" class="ml-auto size-4" x-show="!open && !sidebarCollapsed" /><flux:icon name="chevron-up" class="ml-auto size-4" x-show="open && !sidebarCollapsed" /></button><div id="sidebar-plugins-submenu" x-cloak x-transition.opacity.duration.180ms x-show="open && !sidebarCollapsed" class="rex-submenu"><x-admin.submenu-link href="{{ route('admin.plugins.index') }}" :active="request()->routeIs('admin.plugins.index', 'admin.plugins.edit', 'admin.plugins.show', 'admin.plugins.detail')">Installed Plugins</x-admin.submenu-link><x-admin.submenu-link href="{{ route('admin.plugins.create') }}" :active="request()->routeIs('admin.plugins.create')">Add New Plugin</x-admin.submenu-link></div></div>
            <div x-data="{ open: {{ $toolsActive ? 'true' : 'false' }} }"><button type="button" class="relative flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ $toolsActive ? 'bg-[#eef4ff] text-[#000080]' : 'text-[#334155] hover:bg-[#f3f6fb]' }}" @click="open = !open" aria-controls="sidebar-tools-submenu" :aria-expanded="open.toString()"><flux:icon name="wrench-screwdriver" class="size-5 shrink-0" /><span x-show="!sidebarCollapsed" class="rex-nav-label">Tools</span><flux:icon name="chevron-down" class="ml-auto size-4" x-show="!open && !sidebarCollapsed" /><flux:icon name="chevron-up" class="ml-auto size-4" x-show="open && !sidebarCollapsed" /></button><div id="sidebar-tools-submenu" x-cloak x-transition.opacity.duration.180ms x-show="open && !sidebarCollapsed" class="rex-submenu"><x-admin.submenu-link href="{{ route('admin.tools.data') }}" :active="request()->routeIs('admin.tools.data', 'admin.tools.data.*')">Export/Import Data</x-admin.submenu-link></div></div>
        </div>

        {{-- =========================================================
            CONTENT
        ========================================================== --}}
        <div>
            <p
                x-show="!sidebarCollapsed"
                class="px-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#94a3b8]"
            >
                Content
            </p>

            <div class="mt-3 space-y-1">

                {{-- Media Library --}}
                <a
                    href="{{ route('admin.media.index') }}"
                    wire:navigate
                    data-tooltip="Media Library"
                    class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                    {{ request()->routeIs('admin.media.*')
                        ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                        : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                    }}"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                    aria-label="Media Library"
                >
                    <flux:icon
                        name="photo"
                        class="size-5 shrink-0"
                    />

                    <span
                        x-show="!sidebarCollapsed"
                        class="rex-nav-label"
                    >
                        Media Library
                    </span>
                </a>


                {{-- Contact Messages --}}
                @can('contact_messages.view')
                    <a
                        href="{{ route('admin.contact-messages') }}"
                        wire:navigate
                        data-tooltip="Contact Messages"
                        class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('admin.contact-messages')
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="Contact Messages"
                    >
                        <flux:icon
                            name="inbox"
                            class="size-5 shrink-0"
                        />

                        <span
                            x-show="!sidebarCollapsed"
                            class="rex-nav-label"
                        >
                            Contact Messages
                        </span>

                        <span
                            x-show="!sidebarCollapsed"
                            class="ml-auto rounded-full bg-[#eef4ff] px-2 py-0.5 text-xs font-medium text-[#000080]"
                        >
                            {{ App\Models\ContactMessage::where('status', 'new')->count() }}
                        </span>
                    </a>
                @endcan

            </div>
        </div>


        {{-- =========================================================
            MARKETING
        ========================================================== --}}
        @can('seo.view')
            <div>
                <p
                    x-show="!sidebarCollapsed"
                    class="px-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#94a3b8]"
                >
                    Marketing
                </p>

                <div class="mt-3 space-y-1">
                    <a
                        href="{{ route('admin.seo') }}"
                        wire:navigate
                        data-tooltip="SEO"
                        class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('admin.seo')
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="SEO"
                    >
                        <flux:icon
                            name="magnifying-glass"
                            class="size-5 shrink-0"
                        />

                        <span
                            x-show="!sidebarCollapsed"
                            class="rex-nav-label"
                        >
                            SEO
                        </span>
                    </a>
                </div>
            </div>
        @endcan


        {{-- =========================================================
            SYSTEM
        ========================================================== --}}
        @canany(['manage-users', 'roles.view', 'users.view', 'activity_logs.view', 'settings.view'])
            <div>
                <p
                    x-show="!sidebarCollapsed"
                    class="px-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#94a3b8]"
                >
                    System
                </p>

                <div class="mt-3 space-y-1">

                    {{-- Users --}}
                    @can('users.view')
                    <a
                        href="{{ route('admin.users.index') }}"
                        wire:navigate
                        data-tooltip="Users"
                        class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('admin.users.*')
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="Users"
                    >
                        <flux:icon
                            name="shield-check"
                            class="size-5 shrink-0"
                        />

                        <span
                            x-show="!sidebarCollapsed"
                            class="rex-nav-label"
                        >
                            Users
                        </span>
                    </a>
                    @endcan

                    @can('roles.view')
                    <a
                        href="{{ route('admin.roles.index') }}"
                        wire:navigate
                        data-tooltip="Roles & Permissions"
                        class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('admin.roles.*')
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="Roles & Permissions"
                    >
                        <flux:icon name="key" class="size-5 shrink-0" />
                        <span x-show="!sidebarCollapsed" class="rex-nav-label">Roles &amp; Permissions</span>
                    </a>
                    @endcan


                    {{-- Activity Logs --}}
                    @can('activity_logs.view')
                    <a
                        href="{{ route('admin.activity-logs') }}"
                        wire:navigate
                        data-tooltip="Activity Logs"
                        class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('admin.activity-logs')
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="Activity Logs"
                    >
                        <flux:icon
                            name="clock"
                            class="size-5 shrink-0"
                        />

                        <span
                            x-show="!sidebarCollapsed"
                            class="rex-nav-label"
                        >
                            Activity Logs
                        </span>
                    </a>
                    @endcan


                    {{-- Settings --}}
                    @can('settings.view')
                    <a
                        href="{{ route('admin.site-settings') }}"
                        wire:navigate
                        data-tooltip="Settings"
                        class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('admin.site-settings')
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="Settings"
                    >
                        <flux:icon
                            name="cog-6-tooth"
                            class="size-5 shrink-0"
                        />

                        <span
                            x-show="!sidebarCollapsed"
                            class="rex-nav-label"
                        >
                            Settings
                        </span>
                    </a>
                    @endcan

                    @can('manage-users')
                    <a
                        href="{{ route('admin.platform') }}"
                        wire:navigate
                        data-tooltip="Platform Administration"
                        class="relative flex items-center rounded-lg py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('admin.platform')
                            ? 'bg-[#eef4ff] text-[#000080] before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-[#2563eb]'
                            : 'text-[#334155] hover:bg-[#f3f6fb] hover:text-[#0f172a]'
                        }}"
                        :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                        aria-label="Platform Administration"
                    >
                        <flux:icon name="user-group" class="size-5 shrink-0" />
                        <span x-show="!sidebarCollapsed" class="rex-nav-label">Platform Administration</span>
                    </a>
                    @endcan

                </div>
            </div>
        @endcan

    </nav>


    {{-- =============================================================
        PROMO CARD
    ============================================================== --}}
    <div
        x-show="!sidebarCollapsed"
        class="mx-3 mb-3 rounded-xl border border-[#dcecff] bg-[#eef6ff] p-3"
    >
        <div
            class="flex size-8 items-center justify-center rounded-lg bg-white text-[#0b57d0] shadow-sm"
        >
            <flux:icon
                name="sparkles"
                class="size-4"
            />
        </div>

        <p
            class="mt-3 text-xs font-bold leading-4 text-[#172033]"
        >
            Build a Better<br>
            Digital Presence
        </p>

        <p
            class="mt-1 text-[10px] leading-4 text-[#64748b]"
        >
            Manage your website content with ease.
        </p>

        <a
            href="{{ route('home') }}"
            class="mt-3 inline-flex items-center gap-1 rounded-md bg-[#0b57d0] px-2.5 py-1.5 text-[10px] font-semibold text-white transition hover:bg-[#0848ad]"
        >
            View Website

            <flux:icon
                name="arrow-top-right-on-square"
                class="size-3"
            />
        </a>
    </div>


    {{-- =============================================================
        FOOTER
    ============================================================== --}}
    <div
        x-show="!sidebarCollapsed"
        class="px-4 pb-3 text-[10px] leading-4 text-[#94a3b8]"
    >
        <p>
            © {{ now()->year }} Rexxsol CMS
        </p>

        <p>
            All rights reserved · v1.0.0
        </p>
    </div>


    {{-- =============================================================
        USER CARD
    ============================================================== --}}
    <div class="border-t border-[#eef1f6] p-3">
        <div
            class="flex items-center rounded-lg bg-[#f7f9fc] p-3"
            :class="sidebarCollapsed ? 'justify-center' : 'gap-3'"
        >
            <flux:avatar
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
            />

            <div
                x-show="!sidebarCollapsed"
                class="min-w-0 flex-1"
            >
                <p
                    class="truncate text-sm font-semibold text-[#172033]"
                >
                    {{ auth()->user()->name }}
                </p>

                <p
                    class="truncate text-xs text-[#64748b]"
                >
                    {{ auth()->user()->role_label }}
                </p>
            </div>

            <form
                x-show="!sidebarCollapsed"
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="text-[#64748b] transition hover:text-[#000080]"
                    aria-label="Log out"
                >
                    <flux:icon
                        name="arrow-right-start-on-rectangle"
                        class="size-5"
                    />
                </button>
            </form>
        </div>
    </div>
</aside>