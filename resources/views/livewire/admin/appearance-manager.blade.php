@if ($page === 'custom-css')
    <div
        class="mx-auto w-full max-w-[1440px] space-y-5 pb-6"
        x-data="{ dirty: false }"
        x-on:custom-css-saved.window="dirty = false"
        x-on:livewire:navigate.window="if (dirty && ! window.confirm('You have unsaved Custom CSS changes. Leave this page and discard them?')) $event.preventDefault()"
        x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }"
    >
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs text-[#64748b]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:text-[#2563eb] hover:underline">Dashboard</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('admin.appearance.themes') }}" wire:navigate class="transition hover:text-[#2563eb] hover:underline">Appearance</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="font-medium text-[#172033]">Custom CSS</span>
        </nav>

        <header>
            <h1 class="text-2xl font-bold tracking-tight text-[#172033]">Custom CSS</h1>
            <p class="mt-1 text-sm text-[#64748b]">Customize your website and style your custom HTML.</p>
        </header>

        <form wire:submit="saveCss" class="grid items-start gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(240px,1fr)]">
            <section aria-labelledby="custom-css-heading" class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e5eaf2] px-4 py-3.5 sm:px-5">
                    <div>
                        <h2 id="custom-css-heading" class="text-sm font-semibold text-[#172033]">Custom CSS</h2>
                        <p class="mt-1 text-xs text-[#64748b]">Enter CSS only, without &lt;style&gt; tags. These styles apply to the public website, including matching classes in Custom HTML.</p>
                    </div>
                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-[#334155]">
                        <input type="checkbox" wire:model="customCssEnabled" x-on:change="dirty = true" @can('appearance.manage') @else disabled @endcan class="size-4 rounded border-[#cbd5e1] text-[#2563eb] focus:ring-[#2563eb]">
                        <span>Enabled</span>
                    </label>
                </header>
                <div class="p-4 sm:p-5">
                    <div data-css-editor x-data="customCssEditor(@js($customCss))" x-on:custom-css-reloaded.window="source = $event.detail.content; dirty = false" class="overflow-hidden rounded-md border border-[#dbe2ed] bg-white transition focus-within:border-[#2563eb] focus-within:ring-2 focus-within:ring-[#2563eb]/15">
                        <div class="relative flex h-[420px] min-h-[300px] resize-y overflow-hidden">
                            <div data-css-lines aria-hidden="true" class="z-10 w-11 shrink-0 overflow-hidden border-r border-[#e5eaf2] bg-[#f8fafc] text-right font-mono text-xs leading-6 text-[#94a3b8]">
                                <div class="py-3">
                                    <template x-for="line in lineCount" :key="line">
                                        <div class="h-6 px-2" x-text="line"></div>
                                    </template>
                                </div>
                            </div>
                            <div class="relative min-w-0 flex-1 overflow-hidden">
                                <pre data-css-highlight aria-hidden="true" class="pointer-events-none absolute inset-0 z-10 m-0 overflow-hidden whitespace-pre px-3 py-3 font-mono text-[13px] leading-6"><code x-html="highlighted"></code></pre>
                                <label for="custom-css-editor" class="sr-only">Custom CSS source</label>
                                <textarea
                                    id="custom-css-editor"
                                    wire:model="customCss"
                                    x-on:input="source = $event.target.value; dirty = true; updateCursor($event)"
                                    x-on:scroll="syncScroll($event)"
                                    x-on:keydown="indent($event)"
                                    x-on:keyup="updateCursor($event)"
                                    x-on:click="updateCursor($event)"
                                    @can('appearance.manage') @else readonly @endcan
                                    spellcheck="false"
                                    autocapitalize="off"
                                    autocomplete="off"
                                    wrap="off"
                                    class="absolute inset-0 z-0 h-full min-h-[300px] w-full resize-y overflow-auto border-0 bg-transparent px-3 py-3 font-mono text-[13px] leading-6 text-transparent caret-[#172033] selection:bg-blue-200/70 outline-none focus:ring-0"
                                    aria-describedby="custom-css-help"
                                >{{ $customCss }}</textarea>
                            </div>
                        </div>
                    </div>
                    @error('customCss')
                        <p class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                    <p id="custom-css-help" class="mt-3 text-xs leading-5 text-[#64748b]">Use Tab to indent. Undo and redo work with your browser’s standard keyboard shortcuts. URLs may reference relative site/media paths or HTTPS origins configured for this site.</p>
                </div>
            </section>

            <aside aria-labelledby="custom-css-publish-title" class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <header class="border-b border-[#e5eaf2] px-4 py-3.5">
                    <h2 id="custom-css-publish-title" class="text-sm font-semibold text-[#172033]">Publish</h2>
                </header>
                <div class="space-y-4 p-4">
                    @error('cssConflict')
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900" role="alert">
                            {{ $message }}
                            <button type="button" wire:click="reloadCss" class="mt-2 block font-semibold text-[#1d4ed8] underline">Reload saved version</button>
                        </div>
                    @enderror
                    @can('appearance.manage')
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveCss" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-[#206bc4] px-4 text-sm font-semibold text-white transition hover:bg-[#1b5da8] focus:outline-none focus:ring-2 focus:ring-[#206bc4]/30 disabled:cursor-wait disabled:opacity-70">
                            <span wire:loading.remove wire:target="saveCss"><flux:icon name="archive-box" class="size-4" /></span>
                            <span wire:loading wire:target="saveCss"><flux:icon name="arrow-path" class="size-4 animate-spin" /></span>
                            <span wire:loading.remove wire:target="saveCss">Save changes</span>
                            <span wire:loading wire:target="saveCss">Saving…</span>
                        </button>
                    @else
                        <p class="rounded-md bg-[#f8fafc] p-3 text-xs leading-5 text-[#64748b]">You have view-only access to Custom CSS settings.</p>
                    @endcan
                    <p x-show="dirty" x-cloak class="text-xs font-medium text-amber-700">Unsaved changes</p>
                    @if ($customCssSavedAt)
                        <div class="border-t border-[#eef1f6] pt-3 text-xs leading-5 text-[#64748b]">
                            <p class="font-medium text-[#334155]">Last saved</p>
                            <p>{{ $customCssSavedAt }}</p>
                            @if ($customCssSavedBy)<p>by {{ $customCssSavedBy }}</p>@endif
                        </div>
                    @else
                        <p class="border-t border-[#eef1f6] pt-3 text-xs leading-5 text-[#64748b]">No saved change record is available yet.</p>
                    @endif
                    <p class="text-xs leading-5 text-[#64748b]">Scope: <span class="font-medium text-[#334155]">Global — all public themes</span></p>
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="inline-flex h-10 w-full items-center justify-center rounded-md border border-[#dbe2ed] bg-white px-4 text-sm font-semibold text-[#334155] transition hover:border-[#2563eb] hover:text-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/15">View website</a>
                    <a href="{{ route('admin.appearance.custom-html') }}" wire:navigate class="block text-center text-sm font-semibold text-[#1d4ed8] hover:underline">Custom HTML</a>
                </div>
            </aside>
        </form>
    </div>
@elseif ($page === 'custom-html')
    <div
        class="mx-auto w-full max-w-[1440px] space-y-5 pb-6"
        x-data="{ dirty: false }"
        x-on:custom-html-saved.window="dirty = false"
        x-on:livewire:navigate.window="if (dirty && ! window.confirm('You have unsaved Custom HTML changes. Leave this page and discard them?')) $event.preventDefault()"
        x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }"
    >
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs text-[#64748b]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:text-[#2563eb] hover:underline">Dashboard</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('admin.appearance.themes') }}" wire:navigate class="transition hover:text-[#2563eb] hover:underline">Appearance</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="font-medium text-[#172033]">Custom HTML</span>
        </nav>

        <header>
            <h1 class="text-2xl font-bold tracking-tight text-[#172033]">Custom HTML</h1>
            <p class="mt-1 text-sm text-[#64748b]">Manage HTML snippets across your public website.</p>
        </header>

        <form wire:submit="saveHtml" class="grid items-start gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(240px,1fr)]">
            <section aria-label="Custom HTML editors" class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <div class="space-y-6 p-4 sm:p-5">
                    @foreach ([
                        'header' => ['title' => 'Header HTML', 'property' => 'headerHtml', 'enabled' => 'headerHtmlEnabled', 'hint' => 'Inserted immediately before </head>. This is document metadata, not the visible website navbar.', 'position' => 'Document head'],
                        'body' => ['title' => 'Body HTML', 'property' => 'bodyHtml', 'enabled' => 'bodyHtmlEnabled', 'hint' => 'Inserted immediately after <body> opens, before the public page content.', 'position' => 'After the body opens'],
                        'footer' => ['title' => 'Footer HTML', 'property' => 'footerHtml', 'enabled' => 'footerHtmlEnabled', 'hint' => 'Inserted immediately before </body>, after the public page content.', 'position' => 'Before the body closes'],
                    ] as $section => $editor)
                        <section aria-labelledby="custom-html-{{ $section }}-title" @if ($section !== 'header') class="border-t border-[#e5eaf2] pt-5" @endif>
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 id="custom-html-{{ $section }}-title" class="text-sm font-semibold text-[#172033]">{{ $editor['title'] }}</h2>
                                    <p class="mt-1 text-xs leading-5 text-[#64748b]">{{ $editor['hint'] }}</p>
                                </div>
                                <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-[#334155]">
                                    <input
                                        type="checkbox"
                                        wire:model="{{ $editor['enabled'] }}"
                                        x-on:change="dirty = true"
                                        @can('appearance.manage') @else disabled @endcan
                                        class="size-4 rounded border-[#cbd5e1] text-[#2563eb] focus:ring-[#2563eb]"
                                    >
                                    <span>Enabled</span>
                                </label>
                            </div>

                            <div wire:key="custom-html-{{ $section }}-editor" x-data="{ lineCount: 1 }" x-init="lineCount = $refs.code.value.split('\n').length">
                                <label for="custom-html-{{ $section }}" class="sr-only">{{ $editor['title'] }} code</label>
                                <div class="flex min-h-[300px] overflow-hidden rounded-md border border-[#dbe2ed] bg-white transition focus-within:border-[#2563eb] focus-within:ring-2 focus-within:ring-[#2563eb]/15">
                                    <div aria-hidden="true" class="w-10 shrink-0 overflow-hidden border-r border-[#e5eaf2] bg-[#f8fafc] text-right font-mono text-xs leading-6 text-[#94a3b8]">
                                        <div x-ref="lineNumbers" class="py-2">
                                            <template x-for="line in lineCount" :key="line">
                                                <div class="h-6 px-2" x-text="line"></div>
                                            </template>
                                        </div>
                                    </div>
                                    <textarea
                                        id="custom-html-{{ $section }}"
                                        x-ref="code"
                                        wire:model="{{ $editor['property'] }}"
                                        x-on:input="dirty = true; lineCount = $event.target.value.split('\n').length"
                                        x-on:scroll="$refs.lineNumbers.style.transform = `translateY(-${$event.target.scrollTop}px)`"
                                        @can('appearance.manage') @else readonly @endcan
                                        rows="12"
                                        wrap="off"
                                        spellcheck="false"
                                        autocapitalize="off"
                                        autocomplete="off"
                                        class="min-h-[300px] w-full flex-1 resize-y overflow-auto border-0 bg-transparent px-3 py-2 font-mono text-[13px] leading-6 text-[#172033] outline-none focus:ring-0"
                                        aria-describedby="custom-html-{{ $section }}-help"
                                    >{{ $this->{$editor['property']} }}</textarea>
                                </div>
                                <p id="custom-html-{{ $section }}-help" class="mt-2 text-xs leading-5 text-[#64748b]">
                                    Insertion: {{ $editor['position'] }}. Allowed markup: safe text and semantic HTML; scripts, styles, inline styles, event handlers, unsafe URLs, forms, embeds, PHP, and Blade are rejected.
                                </p>
                                @error($editor['property'])
                                    <p class="mt-2 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        </section>
                    @endforeach
                </div>
            </section>

            <aside aria-labelledby="custom-html-publish-title" class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <header class="border-b border-[#e5eaf2] px-4 py-3.5">
                    <h2 id="custom-html-publish-title" class="text-sm font-semibold text-[#172033]">Publish</h2>
                </header>
                <div class="space-y-4 p-4">
                    @error('htmlConflict')
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900" role="alert">
                            {{ $message }}
                            <button type="button" wire:click="reloadCustomHtml" class="mt-2 block font-semibold text-[#1d4ed8] underline">Reload saved version</button>
                        </div>
                    @enderror

                    @can('appearance.manage')
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveHtml" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-[#206bc4] px-4 text-sm font-semibold text-white transition hover:bg-[#1b5da8] focus:outline-none focus:ring-2 focus:ring-[#206bc4]/30 disabled:cursor-wait disabled:opacity-70">
                            <span wire:loading.remove wire:target="saveHtml"><flux:icon name="archive-box" class="size-4" /></span>
                            <span wire:loading wire:target="saveHtml"><flux:icon name="arrow-path" class="size-4 animate-spin" /></span>
                            <span wire:loading.remove wire:target="saveHtml">Save changes</span>
                            <span wire:loading wire:target="saveHtml">Saving…</span>
                        </button>
                    @else
                        <p class="rounded-md bg-[#f8fafc] p-3 text-xs leading-5 text-[#64748b]">You have view-only access to Custom HTML settings.</p>
                    @endcan

                    <p x-show="dirty" x-cloak class="text-xs font-medium text-amber-700">Unsaved changes</p>
                    @if ($savedAt)
                        <div class="border-t border-[#eef1f6] pt-3 text-xs leading-5 text-[#64748b]">
                            <p class="font-medium text-[#334155]">Last saved</p>
                            <p>{{ $savedAt }}</p>
                            @if ($savedBy)<p>by {{ $savedBy }}</p>@endif
                        </div>
                    @else
                        <p class="border-t border-[#eef1f6] pt-3 text-xs leading-5 text-[#64748b]">No saved change record is available yet.</p>
                    @endif
                    <p class="text-xs leading-5 text-[#64748b]">Scope: <span class="font-medium text-[#334155]">Global — all public themes</span></p>
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="inline-flex h-10 w-full items-center justify-center rounded-md border border-[#dbe2ed] bg-white px-4 text-sm font-semibold text-[#334155] transition hover:border-[#2563eb] hover:text-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/15">View website</a>
                    <a href="{{ route('admin.appearance.custom-css') }}" wire:navigate class="block text-center text-sm font-semibold text-[#1d4ed8] hover:underline">Custom CSS</a>
                </div>
            </aside>
        </form>
    </div>
@else
<div class="space-y-6">
    @if ($page !== 'theme-options')
        <x-admin.page-header :title="$this->title()" description="Manage the active public theme and appearance settings." />
    @endif
    @if ($page === 'themes')
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($themes as $theme)
                <article class="overflow-hidden rounded-xl border border-[#e5eaf2] bg-white shadow-sm">
                    <div class="flex aspect-[16/8] items-center justify-center overflow-hidden bg-[#f7f9fc] text-[#000080]"><img src="{{ $theme['preview'] }}" alt="{{ $theme['name'] }} preview" class="max-h-full max-w-[70%] object-contain"></div>
                    <div class="space-y-3 p-5"><div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-[#172033]">{{ $theme['name'] }}</h2><p class="text-xs text-[#64748b]">Version {{ $theme['version'] }}</p></div>@if ($theme['active'])<span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Active</span>@endif</div><p class="text-sm text-[#64748b]">{{ $theme['description'] }}</p><div style="display: flex; flex-wrap: wrap; gap: 10px; padding-top: 4px;"><a href="{{ app(App\Support\ThemeRegistry::class)->previewUrl($theme['key']) }}" target="_blank" rel="noopener" style="box-sizing: border-box; display: inline-flex; flex: 1 1 140px; min-width: 140px; height: 42px; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border: 1px solid #D8DFEA; border-radius: 8px; background: #ffffff; color: #000080; font-size: 14px; font-weight: 600; line-height: 20px; text-decoration: none;"><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg><span>Preview</span></a>@if ($theme['active'])<button type="button" disabled aria-disabled="true" style="box-sizing: border-box; display: inline-flex; flex: 1 1 140px; min-width: 140px; height: 42px; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border: 1px solid #B7D9C2; border-radius: 8px; background: #ECF8EF; color: #276749; font-size: 14px; font-weight: 600; line-height: 20px; cursor: not-allowed; opacity: 0.9;"><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"></path></svg><span>Active</span></button>@else<button type="button" wire:click="activateTheme('{{ $theme['key'] }}')" wire:loading.attr="disabled" wire:target="activateTheme('{{ $theme['key'] }}')" style="box-sizing: border-box; display: inline-flex; flex: 1 1 140px; min-width: 140px; height: 42px; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border: 1px solid #000080; border-radius: 8px; background: #000080; color: #ffffff; font-size: 14px; font-weight: 600; line-height: 20px; cursor: pointer;"><svg aria-hidden="true" width="16" height="16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"></path></svg><span wire:loading.remove wire:target="activateTheme('{{ $theme['key'] }}')">Activate</span><span wire:loading wire:target="activateTheme('{{ $theme['key'] }}')">Activating...</span></button>@endif</div></div>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-[#cbd5e1] bg-white p-6 text-sm text-[#64748b]">No compatible local themes are available.</p>
            @endforelse
        </div>
    @elseif ($page === 'theme-options')
        @php
            $currentSiteName = app(\App\Support\SiteSettings::class)->get('site.name') ?: app(\App\Support\SiteSettings::class)->get('site.company') ?: 'Rexxsol';
            $fontStacks = [
                'Instrument Sans' => "'Instrument Sans', ui-sans-serif, system-ui, sans-serif",
                'Arial' => 'Arial, Helvetica, sans-serif',
                'Georgia' => 'Georgia, serif',
                'Times New Roman' => "'Times New Roman', Times, serif",
                'Inter' => 'Inter, ui-sans-serif, system-ui, sans-serif',
                'Heebo' => 'Heebo, Arial, sans-serif',
            ];
            $previewCopyright = strtr((string) ($themeOptions['copyright'] ?? ''), ['{year}' => now()->format('Y'), '{site_name}' => $currentSiteName]);
            if ($previewCopyright === '') {
                $previewCopyright = '© '.now()->format('Y').' '.$currentSiteName.'. All rights reserved.';
            }
        @endphp
        <div
            class="mx-auto w-full max-w-[1440px] space-y-5 pb-6"
            x-data="{
                dirty: false,
                brandColor: @js($themeOptions['brand_color'] ?? '#000080'),
                copyright: @js($themeOptions['copyright'] ?? ''),
                siteName: @js($currentSiteName),
                defaults: @js(app(\App\Support\ThemeOptionsConfiguration::class)->defaults($selectedTheme)),
                get copyrightSample() { return (this.copyright || '© {year} {site_name}. All rights reserved.').replaceAll('{year}', new Date().getFullYear()).replaceAll('{site_name}', this.siteName); },
                setValues(values) {
                    this.brandColor = values.brand_color || this.defaults.brand_color || '#000080';
                    this.copyright = values.copyright || '';
                    this.dirty = false;
                }
            }"
            x-on:theme-options-loaded.window="setValues($event.detail.values)"
            x-on:theme-options-defaults-loaded.window="setValues($event.detail.values); dirty = true"
            x-on:theme-options-saved.window="dirty = false"
            x-on:livewire:navigate.window="if (dirty && ! window.confirm('You have unsaved theme option changes. Leave this page and discard them?')) $event.preventDefault()"
            x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }"
        >
            <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs text-[#64748b]">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:text-[#2563eb] hover:underline">Dashboard</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.appearance.themes') }}" wire:navigate class="transition hover:text-[#2563eb] hover:underline">Appearance</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page" class="font-medium text-[#172033]">Theme Options</span>
            </nav>
            <header class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-[#172033]">Theme Options</h1>
                    <p class="mt-1 text-sm text-[#64748b]">Customize your theme’s colors, typography, and footer.</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold text-[#172033]">{{ $selectedTheme['name'] }}</span>
                        @if ($themeSlug === $activeThemeSlug)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Active theme</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">Inactive theme options</span>
                        @endif
                    </div>
                </div>
            </header>

            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(270px,1fr)]">
                <form wire:submit="saveThemeOptions" class="space-y-5">
                    @if ($themeOptionSchema === [])
                        <section class="rounded-lg border border-dashed border-[#cbd5e1] bg-white p-5 text-sm text-[#64748b]">
                            This theme does not declare configurable options. Its saved options are not changed here.
                        </section>
                    @else
                        @if (isset($themeOptionSchema['brand_color']))
                            <section class="space-y-4 rounded-lg border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
                                <div>
                                    <h2 class="text-sm font-semibold text-[#172033]">Colors</h2>
                                    <p class="mt-1 text-xs text-[#64748b]">Brand color affects primary buttons, links, and theme highlights.</p>
                                </div>
                                <div class="grid grid-cols-2 items-start gap-4">
                                    <label class="block text-xs font-semibold text-[#334155]">
                                        Color picker
                                        <input type="color" x-model="brandColor" x-on:input="dirty = true; $wire.set('themeOptions.brand_color', brandColor)" class="mt-1.5 block h-10 w-full cursor-pointer rounded-md border border-[#dbe2ed] bg-white p-1" aria-label="Choose brand color">
                                    </label>
                                    <label class="block text-xs font-semibold text-[#334155]">
                                        Brand color HEX
                                        <input type="text" maxlength="7" x-model="brandColor" x-on:input="dirty = true; $wire.set('themeOptions.brand_color', brandColor)" class="mt-1.5 h-10 w-full rounded-md border border-[#dbe2ed] px-3 font-mono text-xs uppercase text-[#172033] focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/10" placeholder="#000080">
                                        @error('themeOptions.brand_color')<span class="mt-1 block font-normal text-red-600" role="alert">{{ $message }}</span>@enderror
                                        <span class="mt-1 block font-normal text-[#64748b]">Theme default: {{ $themeOptionSchema['brand_color']['default'] }}</span>
                                    </label>
                                </div>
                            </section>
                        @endif

                        <section class="space-y-4 rounded-lg border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
                            <div>
                                <h2 class="text-sm font-semibold text-[#172033]">Typography</h2>
                                <p class="mt-1 text-xs text-[#64748b]">Available families use the app’s local Instrument Sans asset or standard system fonts.</p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach (['heading_font' => 'Heading font', 'body_font' => 'Body font'] as $option => $label)
                                    @if (isset($themeOptionSchema[$option]))
                                        <label class="block text-xs font-semibold text-[#334155]">
                                            {{ $label }}
                                            <select wire:model="themeOptions.{{ $option }}" x-on:change="dirty = true" class="mt-1.5 h-10 w-full rounded-md border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/10">
                                                @foreach ($availableFonts as $font)
                                                    <option value="{{ $font }}">{{ $font }}{{ $font === $themeOptionSchema[$option]['default'] ? ' — theme default' : '' }}</option>
                                                @endforeach
                                            </select>
                                            <span class="mt-2 block rounded-md bg-[#f8fafc] p-3 text-sm font-normal text-[#334155]" style="font-family: {{ $fontStacks[$themeOptions[$option] ?? $themeOptionSchema[$option]['default']] }};">
                                                {{ $option === 'heading_font' ? 'A clear heading preview' : 'A readable body text sample.' }}
                                            </span>
                                        </label>
                                    @endif
                                @endforeach
                                @error('themeOptions.heading_font')<span class="text-xs text-red-600" role="alert">{{ $message }}</span>@enderror
                                @error('themeOptions.body_font')<span class="text-xs text-red-600" role="alert">{{ $message }}</span>@enderror
                            </div>
                        </section>

                        @if (isset($themeOptionSchema['copyright']))
                            <section class="space-y-3 rounded-lg border border-[#e5eaf2] bg-white p-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] sm:p-5">
                                <div>
                                    <h2 class="text-sm font-semibold text-[#172033]">Footer</h2>
                                    <p class="mt-1 text-xs text-[#64748b]">This theme’s copyright override. Empty uses the existing shared footer copyright or the theme fallback. Placeholders: {year}, {site_name}.</p>
                                </div>
                                <label for="theme-copyright" class="block text-xs font-semibold text-[#334155]">Copyright text</label>
                                <input id="theme-copyright" wire:model="themeOptions.copyright" x-model="copyright" x-on:input="dirty = true" maxlength="255" class="h-10 w-full rounded-md border border-[#dbe2ed] px-3 text-sm text-[#172033] focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/10" placeholder="© {year} {site_name}. All rights reserved.">
                                @error('themeOptions.copyright')<span class="block text-xs text-red-600" role="alert">{{ $message }}</span>@enderror
                                <p class="rounded-md bg-[#f8fafc] p-3 text-sm text-[#64748b]"><span class="font-medium text-[#334155]">Preview:</span> <span x-text="copyrightSample">{{ $previewCopyright }}</span></p>
                            </section>
                        @endif

                        <p class="text-xs leading-5 text-[#64748b]">Site name, logo, contact details, social links, and navigation remain in their existing Settings and Navigation modules. Matching Custom CSS rules can override these theme options; use unique class prefixes to reduce selector conflicts.</p>
                    @endif
                    <input type="hidden" wire:model="themeOptionsFingerprint">
                </form>

                <div class="space-y-5">
                    <aside aria-labelledby="theme-options-publish-title" class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                        <header class="border-b border-[#e5eaf2] px-4 py-3.5">
                            <h2 id="theme-options-publish-title" class="text-sm font-semibold text-[#172033]">Save</h2>
                            <p class="mt-1 text-xs text-[#64748b]">{{ $selectedTheme['name'] }}{{ $themeSlug === $activeThemeSlug ? ' — active' : ' — inactive' }}</p>
                        </header>
                        <div class="space-y-4 p-4">
                            @error('themeOptionsConflict')
                                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900" role="alert">
                                    {{ $message }}
                                    <button type="button" wire:click="selectTheme('{{ $themeSlug }}')" class="mt-2 block font-semibold text-[#1d4ed8] underline">Reload saved options</button>
                                </div>
                            @enderror
                            @can('appearance.manage')
                                <button type="button" wire:click="saveThemeOptions" wire:loading.attr="disabled" wire:target="saveThemeOptions" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-[#206bc4] px-4 text-sm font-semibold text-white transition hover:bg-[#1b5da8] focus:outline-none focus:ring-2 focus:ring-[#206bc4]/30 disabled:cursor-wait disabled:opacity-70">
                                    <span wire:loading.remove wire:target="saveThemeOptions"><flux:icon name="archive-box" class="size-4" /></span>
                                    <span wire:loading wire:target="saveThemeOptions"><flux:icon name="arrow-path" class="size-4 animate-spin" /></span>
                                    <span wire:loading.remove wire:target="saveThemeOptions">Save changes</span>
                                    <span wire:loading wire:target="saveThemeOptions">Saving...</span>
                                </button>
                                <button type="button" x-on:click="if (window.confirm('Restore this theme’s declared colors, typography, and footer defaults? Changes stay in this form until you save.')) $wire.restoreThemeDefaults()" class="h-10 w-full rounded-md border border-[#dbe2ed] bg-white px-3 text-sm font-semibold text-[#334155] hover:border-[#2563eb] hover:text-[#1d4ed8]">Restore theme defaults</button>
                            @else
                                <p class="rounded-md bg-[#f8fafc] p-3 text-xs leading-5 text-[#64748b]">You have view-only access to theme options.</p>
                            @endcan
                            <p x-show="dirty" x-cloak class="text-xs font-medium text-amber-700">Unsaved changes</p>
                            @if ($themeOptionsSavedAt)
                                <div class="border-t border-[#eef1f6] pt-3 text-xs leading-5 text-[#64748b]">
                                    <p class="font-medium text-[#334155]">Last saved</p>
                                    <p>{{ $themeOptionsSavedAt }}</p>
                                    @if ($themeOptionsSavedBy)<p>by {{ $themeOptionsSavedBy }}</p>@endif
                                </div>
                            @else
                                <p class="border-t border-[#eef1f6] pt-3 text-xs leading-5 text-[#64748b]">No saved change record is available yet.</p>
                            @endif
                            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="inline-flex h-10 w-full items-center justify-center rounded-md border border-[#dbe2ed] bg-white px-4 text-sm font-semibold text-[#334155] hover:border-[#2563eb] hover:text-[#1d4ed8]">View website</a>
                            <a href="{{ route('admin.site-settings') }}" wire:navigate class="block text-center text-xs font-semibold text-[#1d4ed8] hover:underline">Site identity &amp; contact Settings</a>
                            <a href="{{ route('admin.appearance.footer') }}" wire:navigate class="block text-center text-xs font-semibold text-[#1d4ed8] hover:underline">Footer and Navigation</a>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    @endif
</div>
@endif
