<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin')] #[Title('Platform Administration')] class extends Component {
    public function mount(): void
    {
        Gate::authorize('manage-users');
    }

    public function render()
    {
        return view('pages.admin.platform-administration');
    }
};
?>

@php
    /*
     * Match these route names to your application's actual named routes.
     * Missing destinations are displayed as unavailable, never linked
     * back to this page or to an unrelated module.
     *
     * Destination pages must enforce their own authorization.
     */
    $sections = [
        [
            'title' => 'Access & Monitoring',
            'description' => 'Manage access and review platform activity.',
            'items' => [
                [
                    'label' => 'Users',
                    'description' => 'Manage accounts and user access.',
                    'icon' => 'users',
                    'route' => 'admin.users.index',
                ],
                [
                    'label' => 'Roles & Permissions',
                    'description' => 'Define roles and control permissions.',
                    'icon' => 'key',
                    'route' => 'admin.roles.index',
                ],
                [
                    'label' => 'Activity Logs',
                    'description' => 'Track changes and administrative actions.',
                    'icon' => 'clock',
                    'route' => 'admin.activity-logs',
                ],
            ],
        ],
    ];
@endphp

<div class="rex-admin-hub mx-auto w-full max-w-[1440px] space-y-4">
    <style>
        .rex-admin-hub {
            --hub-primary: #206bc4;
            --hub-text: #172033;
            --hub-muted: #64748b;
            --hub-border: #e5eaf2;

            width: 100%;
            min-width: 0;
            color: var(--hub-text);
        }

        .rex-admin-hub *,
        .rex-admin-hub *::before,
        .rex-admin-hub *::after {
            box-sizing: border-box;
        }

        .rex-admin-hub .hub-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            line-height: 1.5;
            text-transform: uppercase;
            color: var(--hub-primary);
        }

        .rex-admin-hub .hub-breadcrumb a {
            color: var(--hub-muted);
            text-decoration: none;
        }

        .rex-admin-hub .hub-breadcrumb a:hover {
            color: var(--hub-primary);
        }

        .rex-admin-hub .hub-breadcrumb [aria-current] {
            color: var(--hub-muted);
        }

        .rex-admin-hub .hub-heading {
            margin: 0 0 4px;
        }

        .rex-admin-hub .hub-heading h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.025em;
            line-height: 1.4;
        }

        .rex-admin-hub .hub-heading p {
            margin: 4px 0 0;
            color: var(--hub-muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .rex-admin-hub .hub-section {
            margin-bottom: 0;
            overflow: hidden;
            border: 1px solid var(--hub-border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 3px rgb(15 23 42 / 4%);
        }

        .rex-admin-hub .hub-section-header {
            padding: 16px 18px;
            border-bottom: 1px solid #eef1f6;
        }

        .rex-admin-hub .hub-section-header h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 650;
            line-height: 1.5;
        }

        .rex-admin-hub .hub-section-header p {
            margin: 4px 0 0;
            color: var(--hub-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .rex-admin-hub .hub-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            padding: 12px;
        }

        .rex-admin-hub .hub-card {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            min-width: 0;
            padding: 14px 12px;
            border: 1px solid #e5eaf2;
            border-radius: 6px;
            color: inherit;
            text-decoration: none;
            transition:
                background-color 160ms ease,
                border-color 160ms ease;
        }

        .rex-admin-hub a.hub-card:hover {
            border-color: #dbe2ed;
            background: #f7f9fd;
        }

        .rex-admin-hub a:focus-visible {
            outline: 2px solid var(--hub-primary);
            outline-offset: 2px;
        }

        .rex-admin-hub .hub-card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            height: 42px;
            border: 1px solid #e8edf5;
            border-radius: 11px;
            background: #f7f9fc;
            color: #64748b;
            transition:
                color 160ms ease,
                background-color 160ms ease;
        }

        .rex-admin-hub a.hub-card:hover .hub-card-icon {
            color: var(--hub-primary);
            background: #eef4ff;
        }

        .rex-admin-hub .hub-card-body {
            flex: 1;
            min-width: 0;
            padding-top: 1px;
        }

        .rex-admin-hub .hub-card-title {
            display: block;
            color: var(--hub-text);
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
        }

        .rex-admin-hub a.hub-card:hover .hub-card-title {
            color: var(--hub-primary);
        }

        .rex-admin-hub .hub-card-description {
            display: block;
            margin-top: 5px;
            color: var(--hub-muted);
            font-size: 12.5px;
            line-height: 1.65;
            overflow-wrap: anywhere;
        }

        .rex-admin-hub .hub-card-arrow {
            flex: 0 0 16px;
            margin-top: 4px;
            color: #94a3b8;
        }

        .rex-admin-hub a.hub-card:hover .hub-card-arrow {
            color: var(--hub-primary);
        }

        .rex-admin-hub .hub-unavailable {
            display: inline-block;
            margin-top: 8px;
            padding: 2px 7px;
            border: 1px solid #e5eaf2;
            border-radius: 5px;
            color: #64748b;
            background: #f8fafc;
            font-size: 10px;
            font-weight: 500;
            line-height: 1.5;
        }

        @media (max-width: 1100px) {
            .rex-admin-hub .hub-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .rex-admin-hub .hub-heading {
                margin-bottom: 2px;
            }

            .rex-admin-hub .hub-heading p {
                font-size: 13px;
            }

            .rex-admin-hub .hub-section-header {
                padding: 15px 16px;
            }

            .rex-admin-hub .hub-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 8px;
                padding: 10px;
            }

            .rex-admin-hub .hub-card {
                padding: 12px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .rex-admin-hub * {
                transition: none !important;
            }
        }
    </style>

    <nav aria-label="Breadcrumb">
        <ol class="hub-breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}" wire:navigate>
                    Dashboard
                </a>
            </li>
            <li aria-hidden="true">/</li>
            <li aria-current="page">Platform Administration</li>
        </ol>
    </nav>

    <header class="hub-heading">
        <div>
            <h1>Platform Administration</h1>
            <p>Manage access, security, and system maintenance.</p>
        </div>
    </header>

    @foreach ($sections as $section)
        <section
            class="hub-section"
            aria-labelledby="hub-section-{{ $loop->index }}"
        >
            <header class="hub-section-header">
                <h2 id="hub-section-{{ $loop->index }}">
                    {{ $section['title'] }}
                </h2>

                <p>{{ $section['description'] }}</p>
            </header>

            <div class="hub-grid">
                @foreach ($section['items'] as $item)
                    @php
                        $available = Route::has($item['route']);
                    @endphp

                    @if ($available)
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            class="hub-card"
                        >
                    @else
                        <div class="hub-card" aria-disabled="true">
                    @endif

                        <span class="hub-card-icon" aria-hidden="true">
                            <flux:icon
                                :name="$item['icon']"
                                class="size-5"
                            />
                        </span>

                        <span class="hub-card-body">
                            <span class="hub-card-title">
                                {{ $item['label'] }}
                            </span>

                            <span class="hub-card-description">
                                {{ $item['description'] }}
                            </span>

                            @unless ($available)
                                <span class="hub-unavailable">
                                    Not available yet
                                </span>
                            @endunless
                        </span>

                        @if ($available)
                            <span class="hub-card-arrow" aria-hidden="true">
                                <flux:icon
                                    name="chevron-right"
                                    class="size-4"
                                />
                            </span>
                        @endif

                    @if ($available)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach
</div>