<div class="rex-settings-editor mx-auto w-full max-w-[1440px] space-y-4">
    {{-- Page Header --}}
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#206bc4]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="transition hover:underline">Dashboard</a>
        <span class="text-[#94a3b8]">/</span>
        <a href="{{ route('admin.platform') }}" wire:navigate class="transition hover:underline">System</a>
        <span class="text-[#94a3b8]">/</span>
        <span class="text-[#64748b]">{{ $area === 'seo' ? 'SEO' : 'Settings' }}</span>
    </nav>

    <div class="mt-2 mb-4">
        <h1 class="text-2xl font-bold tracking-tight text-[#172033]">
            {{ $area === 'seo' ? 'SEO Management' : 'Global Settings' }}
        </h1>
        <p class="mt-1 text-sm text-[#64748b]">
            {{ $area === 'seo' ? 'Shape how your published content appears in search engines and social sharing.' : 'Manage your website identity, branding, and preferences.' }}
        </p>
    </div>

    {{-- Main Settings Panel --}}
    <form wire:submit="save" class="w-full">
        <section class="overflow-hidden rounded-[8px] border border-[#e5eaf2] bg-white shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
            
            {{-- Horizontal Tab Navigation Bar --}}
            <div class="rex-settings-tabs-scroll overflow-x-auto border-b border-[#e5eaf2] bg-white">
                <nav role="tablist"
                     aria-label="Settings sections"
                     class="flex min-w-max items-center gap-1 px-4 pt-1"
                     x-data="{
                        navigateTabs(event) {
                            const tabs = [...event.currentTarget.querySelectorAll('[role=tab]')];
                            const activeIndex = tabs.indexOf(event.target.closest('[role=tab]'));
                            if (activeIndex < 0) return;
                            let nextIndex;
                            if (event.key === 'ArrowRight') nextIndex = (activeIndex + 1) % tabs.length;
                            else if (event.key === 'ArrowLeft') nextIndex = (activeIndex - 1 + tabs.length) % tabs.length;
                            else if (event.key === 'Home') nextIndex = 0;
                            else if (event.key === 'End') nextIndex = tabs.length - 1;
                            else return;
                            event.preventDefault();
                            tabs[nextIndex].focus();
                            tabs[nextIndex].click();
                        }
                     }"
                     x-on:keydown="navigateTabs($event)">
                    @foreach ($groups as $name => $fields)
                        @php
                            $isActive = $section === $name;
                            $hasError = $errors->hasAny(collect($fields)->keys()->map(fn($k) => "values.$k")->all());
                            $tabIcon = match($name) {
                                'General', 'General SEO' => 'fa-solid fa-sliders',
                                'Branding' => 'fa-solid fa-palette',
                                'Contact' => 'fa-solid fa-address-book',
                                'Social', 'Social Sharing' => 'fa-solid fa-share-nodes',
                                'Website' => 'fa-solid fa-globe',
                                'Maintenance' => 'fa-solid fa-screwdriver-wrench',
                                'Header' => 'fa-solid fa-heading',
                                'Footer' => 'fa-solid fa-shoe-prints',
                                'Sitemap & Robots' => 'fa-solid fa-sitemap',
                                'Verification' => 'fa-solid fa-shield-halved',
                                default => 'fa-solid fa-gear',
                            };
                        @endphp
                        <button type="button" 
                                role="tab"
                                id="tab-{{ Str::slug($name) }}"
                                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                                aria-controls="settings-panel"
                                tabindex="{{ $isActive ? '0' : '-1' }}"
                                wire:click="$set('section', '{{ $name }}')" 
                                class="relative flex items-center gap-2 border-b-2 px-4 py-3.5 text-sm font-semibold transition-all outline-none
                                    {{ $isActive 
                                        ? 'border-[#206bc4] text-[#206bc4] bg-white' 
                                        : 'border-transparent text-[#64748b] hover:text-[#172033] hover:bg-[#f8fafc]' }}">
                            <i class="{{ $tabIcon }} text-xs {{ $isActive ? 'text-[#206bc4]' : 'text-[#94a3b8]' }}" aria-hidden="true"></i>
                            <span>{{ $name }}</span>
                            @if ($hasError)
                                <span class="size-2 rounded-full bg-red-500" title="This section contains errors" aria-label="Has errors"></span>
                            @endif
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- Validation Error Alert --}}
            @if ($errors->any())
                <div class="border-b border-red-100 bg-red-50/70 px-6 py-3 text-xs text-red-700" role="alert">
                    <div class="flex items-center gap-2 font-semibold">
                        <i class="fa-solid fa-triangle-exclamation text-red-500" aria-hidden="true"></i>
                        <span>Please correct the errors below to save changes:</span>
                    </div>
                </div>
            @endif

            {{-- Section Header --}}
            @php
                $sectionDescription = match($section) {
                    'General' => 'Configure the core site name, company identity, timezone, and language preferences.',
                    'Branding' => 'Manage your site logos, favicon, and default social share assets.',
                    'Contact' => 'Set official corporate contact info, support channels, and office location coordinates.',
                    'Social' => 'Link your verified public social media profiles and channels.',
                    'Website' => 'Manage global website copy, copyright notice, and primary call-to-action.',
                    'Maintenance' => 'Control public website maintenance mode and visitor announcements.',
                    'Header' => 'Configure navigation header behavior, sticky state, and primary header action.',
                    'Footer' => 'Configure company footer description, branding, and global call-to-action banner.',
                    'General SEO' => 'Shape how your website appears in search engines with default metadata.',
                    'Social Sharing' => 'Customize Open Graph metadata and preview cards for social media platforms.',
                    'Sitemap & Robots' => 'Control search engine crawling, robots directives, and XML sitemap generation.',
                    'Verification' => 'Add domain ownership verification tokens for Google and Bing search consoles.',
                    default => 'Manage ' . strtolower($section) . ' preferences.',
                };
            @endphp
            <div class="border-b border-[#eef1f6] bg-white px-6 py-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#172033]">{{ $section }}</h2>
                        <p class="mt-0.5 text-xs text-[#64748b]">{{ $sectionDescription }}</p>
                    </div>
                </div>
            </div>

            {{-- Tab Content Panel --}}
            <div class="p-6" role="tabpanel" id="settings-panel" aria-labelledby="tab-{{ Str::slug($section) }}" tabindex="0">
                
                {{-- GENERAL TAB --}}
                @if ($section === 'General')
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        {{-- Site name --}}
                        <div>
                            <label for="setting-site_name" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                Site name <span class="text-red-500">*</span>
                            </label>
                            <input id="setting-site_name" 
                                   type="text" 
                                   wire:model="values.site_name" 
                                   class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                   placeholder="Rexxsol" />
                            @error('values.site_name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Company name --}}
                        <div>
                            <label for="setting-company_name" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                Company name
                            </label>
                            <input id="setting-company_name" 
                                   type="text" 
                                   wire:model="values.company_name" 
                                   class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                   placeholder="Rexxsol Software Solutions" />
                            @error('values.company_name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Site tagline (full width) --}}
                        <div class="sm:col-span-2">
                            <label for="setting-tagline" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                Site tagline
                            </label>
                            <input id="setting-tagline" 
                                   type="text" 
                                   wire:model="values.tagline" 
                                   class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                   placeholder="Innovative Software & IT Consulting" />
                            <p class="mt-1 text-xs text-[#64748b]">Brief motto displayed alongside the company name in search results and titles.</p>
                            @error('values.tagline')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Company description (full width, 120-150px) --}}
                        <div class="sm:col-span-2">
                            <label for="setting-company_description" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                Company description
                            </label>
                            <textarea id="setting-company_description" 
                                      wire:model="values.company_description" 
                                      rows="4" 
                                      class="min-h-[130px] w-full rounded-[6px] border border-[#dbe2ed] bg-white p-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                      placeholder="Rexxsol is a premier software development firm providing bespoke engineering solutions..."></textarea>
                            <p class="mt-1 text-xs text-[#64748b]">A comprehensive corporate summary used for default metadata and about sections.</p>
                            @error('values.company_description')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Timezone --}}
                        <div>
                            <label for="setting-timezone" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                Timezone <span class="text-red-500">*</span>
                            </label>
                            <select id="setting-timezone" 
                                    wire:model="values.timezone" 
                                    class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10">
                                @foreach (timezone_identifiers_list() as $zone)
                                    <option value="{{ $zone }}">{{ $zone }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-[#64748b]">Used for system timestamps, publication dates, and scheduled tasks.</p>
                            @error('values.timezone')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Default language --}}
                        <div>
                            <label for="setting-language" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                Default language <span class="text-red-500">*</span>
                            </label>
                            <input id="setting-language" 
                                   type="text" 
                                   wire:model="values.language" 
                                   class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                   placeholder="en" />
                            <p class="mt-1 text-xs text-[#64748b]">Standard ISO language code (e.g., en, en-US, ur).</p>
                            @error('values.language')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                {{-- BRANDING TAB --}}
                @elseif ($section === 'Branding')
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @php
                            $brandingFields = [
                                'logo' => ['title' => 'Primary Logo', 'desc' => 'Main logo displayed on light headers, navigation bar, and email templates.'],
                                'dark_logo' => ['title' => 'Dark Mode Logo', 'desc' => 'Alternative white or light logo for dark headers and inverted themes.'],
                                'favicon' => ['title' => 'Browser Favicon', 'desc' => '32×32 or 64×64 icon displayed in browser tabs and bookmarks.'],
                                'social_image' => ['title' => 'Default Social Share Image', 'desc' => 'Recommended 1200×630 image displayed when pages are shared on social media.'],
                            ];
                        @endphp

                        @foreach ($brandingFields as $mediaKey => $meta)
                            <div class="rounded-[8px] border border-[#e5eaf2] bg-[#fbfcfe] p-4 transition hover:border-[#cbd5e1]">
                                <div class="mb-3">
                                    <h3 class="text-sm font-bold text-[#172033]">{{ $meta['title'] }}</h3>
                                    <p class="mt-0.5 text-xs text-[#64748b]">{{ $meta['desc'] }}</p>
                                </div>

                                @if ($item = $selectedMedia->get($values[$mediaKey] ?? null))
                                    <div class="space-y-3">
                                        <div class="flex h-36 w-full items-center justify-center rounded-[6px] border border-[#e5eaf2] bg-white p-3 shadow-inner">
                                            <img src="{{ Storage::disk($item->disk)->url($item->path) }}" 
                                                 alt="{{ $meta['title'] }} preview" 
                                                 class="max-h-full max-w-full object-contain" />
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="truncate font-medium text-[#64748b] max-w-[180px]">{{ $item->original_name }}</span>
                                            <div class="flex items-center gap-2">
                                                <button type="button" 
                                                        wire:click="chooseMedia('{{ $mediaKey }}')" 
                                                        class="rounded-[4px] border border-[#dbe2ed] bg-white px-2.5 py-1 font-semibold text-[#206bc4] hover:bg-[#f7f9fc]">
                                                    Replace
                                                </button>
                                                <button type="button" 
                                                        wire:click="$set('values.{{ $mediaKey }}', null)" 
                                                        class="rounded-[4px] border border-red-200 bg-white px-2.5 py-1 font-semibold text-red-600 hover:bg-red-50">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <button type="button" 
                                            wire:click="chooseMedia('{{ $mediaKey }}')" 
                                            class="flex h-36 w-full flex-col items-center justify-center gap-2 rounded-[6px] border-2 border-dashed border-[#dbe2ed] bg-white p-4 transition hover:border-[#206bc4] hover:bg-[#f0f4ff]">
                                        <flux:icon name="photo" class="size-6 text-[#94a3b8]" />
                                        <span class="text-xs font-semibold text-[#206bc4]">Choose from Media Library</span>
                                        <span class="text-[11px] text-[#94a3b8]">JPG, PNG, WebP or SVG</span>
                                    </button>
                                @endif
                                @error('values.' . $mediaKey)<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                {{-- CONTACT TAB --}}
                @elseif ($section === 'Contact')
                    <div class="space-y-6">
                        {{-- Subsection: Email & Phone --}}
                        <div>
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.08em] text-[#206bc4]">
                                Direct Contact Channels
                            </h3>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="setting-business_email" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Business email
                                    </label>
                                    <input id="setting-business_email" 
                                           type="email" 
                                           wire:model="values.business_email" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="contact@rexxsol.com" />
                                    @error('values.business_email')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-support_email" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Support email
                                    </label>
                                    <input id="setting-support_email" 
                                           type="email" 
                                           wire:model="values.support_email" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="support@rexxsol.com" />
                                    @error('values.support_email')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-phone" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Phone number
                                    </label>
                                    <input id="setting-phone" 
                                           type="text" 
                                           wire:model="values.phone" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="+92 333 2465888" />
                                    @error('values.phone')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-whatsapp" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        WhatsApp number
                                    </label>
                                    <input id="setting-whatsapp" 
                                           type="text" 
                                           wire:model="values.whatsapp" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="+92 333 2465888" />
                                    @error('values.whatsapp')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-[#eef1f6]" />

                        {{-- Subsection: Location & Google Maps --}}
                        <div>
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.08em] text-[#206bc4]">
                                Office Address &amp; Maps
                            </h3>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="setting-address" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Physical office address
                                    </label>
                                    <textarea id="setting-address" 
                                              wire:model="values.address" 
                                              rows="3" 
                                              class="w-full rounded-[6px] border border-[#dbe2ed] bg-white p-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                              placeholder="Pearl City, Canal Rd, Rahim Yar Khan, 64200, Pakistan"></textarea>
                                    @error('values.address')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-office_map_embed_url" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Google Maps embed URL
                                    </label>
                                    <input id="setting-office_map_embed_url" 
                                           type="url" 
                                           wire:model="values.office_map_embed_url" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="https://www.google.com/maps/embed?..." />
                                    <p class="mt-1 text-xs text-[#64748b]">Must be a valid HTTPS Google Maps embed link.</p>
                                    @error('values.office_map_embed_url')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-office_map_directions_url" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Google Maps directions URL
                                    </label>
                                    <input id="setting-office_map_directions_url" 
                                           type="url" 
                                           wire:model="values.office_map_directions_url" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="https://maps.google.com/?q=..." />
                                    <p class="mt-1 text-xs text-[#64748b]">Direct link for visitors seeking travel directions.</p>
                                    @error('values.office_map_directions_url')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                {{-- SOCIAL TAB --}}
                @elseif ($section === 'Social')
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @php
                            $socialPlatforms = [
                                'facebook' => ['name' => 'Facebook', 'icon' => 'fa-brands fa-facebook text-[#1877f2]', 'placeholder' => 'https://facebook.com/rexxsol'],
                                'linkedin' => ['name' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin text-[#0a66c2]', 'placeholder' => 'https://linkedin.com/company/rexxsol'],
                                'instagram' => ['name' => 'Instagram', 'icon' => 'fa-brands fa-instagram text-[#e4405f]', 'placeholder' => 'https://instagram.com/rexxsol'],
                                'twitter' => ['name' => 'X / Twitter', 'icon' => 'fa-brands fa-x-twitter text-[#172033]', 'placeholder' => 'https://x.com/rexxsol'],
                                'youtube' => ['name' => 'YouTube', 'icon' => 'fa-brands fa-youtube text-[#ff0000]', 'placeholder' => 'https://youtube.com/@rexxsol'],
                                'github' => ['name' => 'GitHub', 'icon' => 'fa-brands fa-github text-[#172033]', 'placeholder' => 'https://github.com/rexxsol'],
                            ];
                        @endphp

                        @foreach ($socialPlatforms as $socKey => $soc)
                            <div>
                                <label for="setting-{{ $socKey }}" class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                    <i class="{{ $soc['icon'] }} text-sm" aria-hidden="true"></i>
                                    <span>{{ $soc['name'] }}</span>
                                </label>
                                <input id="setting-{{ $socKey }}" 
                                       type="url" 
                                       wire:model="values.{{ $socKey }}" 
                                       class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                       placeholder="{{ $soc['placeholder'] }}" />
                                @error('values.' . $socKey)<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                {{-- WEBSITE TAB --}}
                @elseif ($section === 'Website')
                    <div class="space-y-6">
                        <div>
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.08em] text-[#206bc4]">
                                Global Call to Action
                            </h3>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="setting-cta_label" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Primary CTA button label
                                    </label>
                                    <input id="setting-cta_label" 
                                           type="text" 
                                           wire:model="values.cta_label" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="Get in touch" />
                                    @error('values.cta_label')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-cta_url" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Primary CTA button URL
                                    </label>
                                    <input id="setting-cta_url" 
                                           type="url" 
                                           wire:model="values.cta_url" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="https://rexxsol.com/contact" />
                                    @error('values.cta_url')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-[#eef1f6]" />

                        <div>
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.08em] text-[#206bc4]">
                                Copyright &amp; Legal Notices
                            </h3>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="setting-copyright" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Copyright statement
                                    </label>
                                    <input id="setting-copyright" 
                                           type="text" 
                                           wire:model="values.copyright" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="© {{ date('Y') }} Rexxsol. All rights reserved." />
                                    @error('values.copyright')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="setting-footer_text" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Footer notice text
                                    </label>
                                    <textarea id="setting-footer_text" 
                                              wire:model="values.footer_text" 
                                              rows="3" 
                                              class="w-full rounded-[6px] border border-[#dbe2ed] bg-white p-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                              placeholder="Thoughtful digital solutions. Built around your business."></textarea>
                                    @error('values.footer_text')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                {{-- MAINTENANCE TAB --}}
                @elseif ($section === 'Maintenance')
                    <div class="space-y-6">
                        {{-- Toggle --}}
                        <div class="flex items-start justify-between gap-4 rounded-[8px] border border-[#e5eaf2] bg-[#fbfcfe] p-4">
                            <div>
                                <h3 class="text-sm font-bold text-[#172033]">Public Website Maintenance Mode</h3>
                                <p class="mt-1 text-xs text-[#64748b]">
                                    When enabled, public site visitors will see a maintenance notice instead of normal pages. Admin CMS pages remain accessible to authenticated staff.
                                </p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center shrink-0">
                                <input type="checkbox" wire:model="values.maintenance" aria-label="Enable public website maintenance mode" class="peer sr-only" />
                                <div class="h-6 w-11 rounded-full bg-[#cbd5e1] peer-checked:bg-[#206bc4] peer-focus-visible:ring-2 peer-focus-visible:ring-[#206bc4] peer-focus-visible:ring-offset-2 after:absolute after:top-[2px] after:left-[2px] after:size-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>
                        @error('values.maintenance')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror

                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label for="setting-maintenance_title" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                    Maintenance title
                                </label>
                                <input id="setting-maintenance_title" 
                                       type="text" 
                                       wire:model="values.maintenance_title" 
                                       class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                       placeholder="We’ll be right back" />
                                @error('values.maintenance_title')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="setting-maintenance_message" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                    Maintenance message
                                </label>
                                <textarea id="setting-maintenance_message" 
                                          wire:model="values.maintenance_message" 
                                          rows="4" 
                                          class="w-full rounded-[6px] border border-[#dbe2ed] bg-white p-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                          placeholder="Our website is currently undergoing scheduled maintenance. Please check back shortly."></textarea>
                                @error('values.maintenance_message')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                {{-- HEADER TAB --}}
                @elseif ($section === 'Header')
                    <div class="space-y-6">
                        {{-- Sticky Header Toggle --}}
                        <div class="flex items-start justify-between gap-4 rounded-[8px] border border-[#e5eaf2] bg-[#fbfcfe] p-4">
                            <div>
                                <h3 class="text-sm font-bold text-[#172033]">Sticky Header Navigation</h3>
                                <p class="mt-1 text-xs text-[#64748b]">
                                    Keeps the top website navbar fixed in view as visitors scroll down long pages.
                                </p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center shrink-0">
                                <input type="checkbox" wire:model="values.header_sticky" aria-label="Keep header navigation sticky" class="peer sr-only" />
                                <div class="h-6 w-11 rounded-full bg-[#cbd5e1] peer-checked:bg-[#206bc4] peer-focus-visible:ring-2 peer-focus-visible:ring-[#206bc4] peer-focus-visible:ring-offset-2 after:absolute after:top-[2px] after:left-[2px] after:size-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>
                        @error('values.header_sticky')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror

                        <div>
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.08em] text-[#206bc4]">
                                Header Call to Action Button
                            </h3>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="setting-header_cta_label" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Header CTA label
                                    </label>
                                    <input id="setting-header_cta_label" 
                                           type="text" 
                                           wire:model="values.header_cta_label" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="Contact Us" />
                                    @error('values.header_cta_label')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-header_cta_url" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Header CTA URL
                                    </label>
                                    <input id="setting-header_cta_url" 
                                           type="url" 
                                           wire:model="values.header_cta_url" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="https://rexxsol.com/contact" />
                                    @error('values.header_cta_url')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[6px] border border-blue-100 bg-blue-50/60 p-4 text-xs text-[#206bc4]">
                            <i class="fa-solid fa-circle-info mr-1.5" aria-hidden="true"></i>
                            Header links and menu items are managed directly in the 
                            <a href="{{ route('admin.navigation') }}" wire:navigate class="font-bold underline hover:text-[#1a569d]">Navigation Manager</a>.
                        </div>
                    </div>

                {{-- FOOTER TAB --}}
                @elseif ($section === 'Footer')
                    <div class="space-y-6">
                        {{-- Subsection: Footer Brand & About --}}
                        <div>
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.08em] text-[#206bc4]">
                                Footer Brand &amp; Bio
                            </h3>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Footer logo
                                    </label>
                                    @if ($item = $selectedMedia->get($values['footer_logo'] ?? null))
                                        <div class="flex items-center gap-3 rounded-[6px] border border-[#e5eaf2] bg-[#fbfcfe] p-3">
                                            <img src="{{ Storage::disk($item->disk)->url($item->path) }}" alt="Footer logo preview" class="h-10 max-w-[120px] object-contain" />
                                            <div class="flex flex-1 items-center justify-end gap-2 text-xs">
                                                <button type="button" wire:click="chooseMedia('footer_logo')" class="font-semibold text-[#206bc4] hover:underline">Change</button>
                                                <button type="button" wire:click="$set('values.footer_logo', null)" class="font-semibold text-red-600 hover:underline">Remove</button>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button" wire:click="chooseMedia('footer_logo')" class="flex h-[42px] w-full items-center justify-center gap-2 rounded-[6px] border border-dashed border-[#dbe2ed] bg-white text-xs font-semibold text-[#206bc4] hover:bg-[#f0f4ff]">
                                            <flux:icon name="photo" class="size-4" />
                                            Select footer logo
                                        </button>
                                    @endif
                                    @error('values.footer_logo')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="setting-footer_description" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Company short bio for footer
                                    </label>
                                    <textarea id="setting-footer_description" 
                                              wire:model="values.footer_description" 
                                              rows="3" 
                                              class="w-full rounded-[6px] border border-[#dbe2ed] bg-white p-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                              placeholder="Thoughtful digital solutions. Built around your business."></textarea>
                                    @error('values.footer_description')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-[#eef1f6]" />

                        {{-- Subsection: Global CTA Banner --}}
                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-xs font-bold uppercase tracking-[0.08em] text-[#206bc4]">
                                    Footer Call-to-Action Banner
                                </h3>
                                <label class="flex items-center gap-2 text-xs font-medium text-[#172033]">
                                    <input type="checkbox" wire:model="values.footer_cta_enabled" class="size-4 rounded border-[#dbe2ed] accent-[#206bc4]" />
                                    <span>Enable CTA banner</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="setting-footer_cta_eyebrow" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Footer CTA eyebrow
                                    </label>
                                    <input id="setting-footer_cta_eyebrow" 
                                           type="text" 
                                           wire:model="values.footer_cta_eyebrow" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="HAVE A PROJECT IN MIND?" />
                                    @error('values.footer_cta_eyebrow')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-footer_cta_heading" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Footer CTA heading
                                    </label>
                                    <input id="setting-footer_cta_heading" 
                                           type="text" 
                                           wire:model="values.footer_cta_heading" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="Let’s talk." />
                                    @error('values.footer_cta_heading')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="setting-footer_cta_description" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Footer CTA description
                                    </label>
                                    <textarea id="setting-footer_cta_description" 
                                              wire:model="values.footer_cta_description" 
                                              rows="2" 
                                              class="w-full rounded-[6px] border border-[#dbe2ed] bg-white p-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                              placeholder="Tell us what you’re building. We’ll help you take it forward."></textarea>
                                    @error('values.footer_cta_description')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-footer_cta_label" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Footer CTA label
                                    </label>
                                    <input id="setting-footer_cta_label" 
                                           type="text" 
                                           wire:model="values.footer_cta_label" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="Start a conversation" />
                                    @error('values.footer_cta_label')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="setting-footer_cta_url" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                        Footer CTA URL
                                    </label>
                                    <input id="setting-footer_cta_url" 
                                           type="url" 
                                           wire:model="values.footer_cta_url" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" 
                                           placeholder="https://rexxsol.com/contact" />
                                    @error('values.footer_cta_url')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[6px] border border-blue-100 bg-blue-50/60 p-4 text-xs text-[#206bc4]">
                            <i class="fa-solid fa-circle-info mr-1.5" aria-hidden="true"></i>
                            Footer columns, links, and widget areas are managed in 
                            <a href="{{ route('admin.navigation') }}" wire:navigate class="font-bold underline hover:text-[#1a569d]">Navigation</a> and 
                            <a href="{{ route('admin.appearance.footer') }}" wire:navigate class="font-bold underline hover:text-[#1a569d]">Footer Appearance</a>.
                        </div>
                    </div>

                {{-- SEO SECTIONS (When $area === 'seo') --}}
                @else
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @foreach ($groups[$section] as $name => $field)
                            <div wire:key="field-{{ $name }}" class="{{ $field[2] === 'textarea' ? 'sm:col-span-2' : '' }}">
                                <label for="setting-{{ $name }}" class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5">
                                    {{ $field[1] }}
                                </label>

                                @if ($field[2] === 'media')
                                    @if ($item = $selectedMedia->get($values[$name] ?? null))
                                        <div class="flex items-center gap-3 rounded-[6px] border border-[#e5eaf2] bg-[#fbfcfe] p-3">
                                            <img src="{{ Storage::disk($item->disk)->url($item->path) }}" alt="{{ $field[1] }} preview" class="h-14 max-w-[140px] rounded object-cover" />
                                            <div class="flex flex-1 items-center justify-between text-xs">
                                                <span class="truncate font-medium text-[#64748b] max-w-[180px]">{{ $item->original_name }}</span>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" wire:click="chooseMedia('{{ $name }}')" class="font-semibold text-[#206bc4] hover:underline">Change</button>
                                                    <button type="button" wire:click="$set('values.{{ $name }}', null)" class="font-semibold text-red-600 hover:underline">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button" wire:click="chooseMedia('{{ $name }}')" class="flex h-[42px] w-full items-center justify-center gap-2 rounded-[6px] border border-dashed border-[#dbe2ed] bg-white text-xs font-semibold text-[#206bc4] hover:bg-[#f0f4ff]">
                                            <flux:icon name="photo" class="size-4" />
                                            Select image
                                        </button>
                                    @endif

                                @elseif ($field[2] === 'checkbox')
                                    <div class="flex items-center gap-3 rounded-[6px] border border-[#e5eaf2] bg-[#fbfcfe] p-3.5">
                                        <input id="setting-{{ $name }}" type="checkbox" wire:model="values.{{ $name }}" class="size-4 rounded border-[#dbe2ed] accent-[#206bc4]" />
                                        <label for="setting-{{ $name }}" class="text-sm font-medium text-[#172033] cursor-pointer">
                                            {{ $field[1] }}
                                        </label>
                                    </div>

                                @elseif ($field[2] === 'textarea')
                                    <textarea id="setting-{{ $name }}" 
                                              rows="4" 
                                              wire:model.live.debounce.400ms="values.{{ $name }}" 
                                              class="min-h-[120px] w-full rounded-[6px] border border-[#dbe2ed] bg-white p-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10"></textarea>

                                @elseif ($field[2] === 'timezone')
                                    <select id="setting-{{ $name }}" 
                                            wire:model="values.{{ $name }}" 
                                            class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10">
                                        @foreach (timezone_identifiers_list() as $zone)
                                            <option value="{{ $zone }}">{{ $zone }}</option>
                                        @endforeach
                                    </select>

                                @else
                                    <input id="setting-{{ $name }}" 
                                           type="{{ $field[2] }}" 
                                           wire:model.live.debounce.400ms="values.{{ $name }}" 
                                           class="h-[42px] w-full rounded-[6px] border border-[#dbe2ed] bg-white px-3.5 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" />
                                @endif

                                @if (in_array($name, ['seo_title', 'seo_description']))
                                    <p class="mt-1 text-xs text-[#64748b]">
                                        {{ mb_strlen($values[$name] ?? '') }} characters · Recommended {{ $name === 'seo_title' ? '50–60' : '150–160' }}; up to 255 allowed.
                                    </p>
                                @endif

                                @error('values.'.$name)<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                    @if ($section === 'Sitemap & Robots')
                        <div class="mt-6 flex flex-wrap gap-4 border-t border-[#eef1f6] pt-4 text-xs font-semibold text-[#206bc4]">
                            <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 hover:underline">
                                <flux:icon name="arrow-top-right-on-square" class="size-4" />
                                View sitemap.xml
                            </a>
                            <a href="{{ route('robots') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 hover:underline">
                                <flux:icon name="arrow-top-right-on-square" class="size-4" />
                                View robots.txt
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Optional SEO Preview Box (only on SEO Management) --}}
            @if ($area === 'seo')
                <div class="border-t border-[#eef1f6] bg-[#fbfcfe] px-6 py-4">
                    <h3 class="text-xs font-bold uppercase tracking-[0.08em] text-[#64748b] mb-2">Search Result Preview</h3>
                    <div class="rounded-[8px] border border-[#e5eaf2] bg-white p-4 space-y-1">
                        <p class="truncate text-xs text-[#64748b]">{{ $values['canonical_base'] ?: config('app.url') }}</p>
                        <p class="truncate text-base font-semibold text-[#1a0dab]">{{ $values['seo_title'] ?: ($values['site_name'] ?? 'Rexxsol') }}</p>
                        <p class="line-clamp-2 text-xs text-[#4d5156]">{{ $values['seo_description'] ?: 'No meta description configured yet.' }}</p>
                    </div>
                </div>
            @endif

            {{-- Footer Action Bar --}}
            <div class="flex items-center justify-end border-t border-[#eef1f6] bg-[#fcfdff] px-6 py-4">
                @can($area === 'seo' ? 'seo.update' : 'settings.update')
                <button type="submit" 
                        wire:loading.attr="disabled" 
                        wire:target="save" 
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-[6px] bg-[#206bc4] px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a569d] disabled:cursor-wait disabled:opacity-75">
                    <span wire:loading.remove wire:target="save">Save changes</span>
                    <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                        <span class="size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <span>Saving...</span>
                    </span>
                </button>
                @endcan
            </div>
        </section>
    </form>

    {{-- Media Library Picker Modal --}}
    <flux:modal name="settings-media" class="md:w-[44rem]">
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-bold text-[#172033]">Select from Media Library</h2>
                <p class="mt-0.5 text-xs text-[#64748b]">Select an image asset from your library to associate with this setting.</p>
            </div>

            <div class="rounded-lg border border-dashed border-[#b8c4d6] bg-[#f8fafc] p-4">
                <label for="settings-media-upload" class="block text-sm font-semibold text-[#172033]">Upload an image</label>
                <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                    <input id="settings-media-upload" wire:model="mediaUpload" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="min-w-0 flex-1 text-sm" />
                    <button type="button" wire:click="uploadMedia" wire:loading.attr="disabled" wire:target="uploadMedia,mediaUpload" class="h-10 rounded-md bg-[#206bc4] px-4 text-sm font-semibold text-white disabled:opacity-50">Upload and select</button>
                </div>
                @error('mediaUpload')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-2 text-xs text-[#64748b]">JPG, PNG, WebP or GIF · Maximum 5 MB</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-[0.05em] text-[#475569] mb-1.5" for="media-search">
                    Search images
                </label>
                <div class="relative">
                    <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-[#94a3b8]" />
                    <input id="media-search" 
                           wire:model.live.debounce.300ms="mediaSearch" 
                           placeholder="Search by filename..." 
                           class="h-10 w-full rounded-[6px] border border-[#dbe2ed] bg-white pl-9 pr-3 text-sm text-[#172033] outline-none transition focus:border-[#206bc4] focus:ring-2 focus:ring-[#206bc4]/10" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 max-h-[360px] overflow-y-auto p-1">
                @foreach ($mediaItems ?? [] as $item)
                    <button type="button" 
                            wire:click="selectMedia({{ $item->id }})" 
                            class="group relative flex flex-col overflow-hidden rounded-[6px] border border-[#e5eaf2] bg-white p-2 text-left transition hover:border-[#206bc4] hover:shadow-sm">
                        <img src="{{ Storage::disk($item->disk)->url($item->path) }}" 
                             alt="{{ $item->original_name }}" 
                             class="aspect-square w-full rounded object-cover" />
                        <span class="mt-2 block truncate text-xs font-medium text-[#172033] group-hover:text-[#206bc4]">
                            {{ $item->title ?: 'Untitled image' }}
                        </span>
                    </button>
                @endforeach
            </div>

            @if ($mediaItems && $mediaItems->isEmpty())
                <p class="py-6 text-center text-xs text-[#64748b]">No matching images found. Upload media in Media Library first.</p>
            @endif

            @if ($mediaItems)
                <div class="border-t border-[#eef1f6] pt-3">{{ $mediaItems->links() }}</div>
            @endif
        </div>
    </flux:modal>
</div>
