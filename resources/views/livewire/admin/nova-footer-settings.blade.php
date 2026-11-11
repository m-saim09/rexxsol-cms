<div class="mx-auto w-full max-w-[1440px] space-y-5 pb-8">
    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs text-[#64748b]">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-[#2563eb]">Dashboard</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.appearance.themes') }}" wire:navigate class="hover:text-[#2563eb]">Appearance</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page" class="font-medium text-[#172033]">Rexxsol Nova Footer</span>
    </nav>

    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#172033]">Rexxsol Nova CTA & Footer</h1>
            <p class="mt-1 text-sm text-[#64748b]">Theme-specific controls for the Nova public website. Menus use Appearance → Menus and remain independent from other themes.</p>
        </div>
        <a href="{{ route('admin.appearance.menus', ['menu' => 'footer']) }}" wire:navigate class="rounded-md border border-[#dbe2ed] bg-white px-3 py-2 text-sm font-semibold text-[#334155] hover:bg-[#f8fafc]">Manage footer menus</a>
    </header>

    <form wire:submit="save" class="space-y-5">
        <section class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-sm">
            <header class="border-b border-[#e5eaf2] px-5 py-4">
                <h2 class="text-base font-semibold text-[#172033]">1. CTA Settings</h2>
                <p class="mt-1 text-sm text-[#64748b]">Shown above the Nova footer on public pages.</p>
            </header>
            <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                <label class="flex items-center gap-2 text-sm font-medium text-[#334155]">
                    <input type="checkbox" wire:model="settings.cta.enabled" class="size-4 rounded border-[#cbd5e1] text-[#2563eb]">
                    Show CTA section
                </label>
                <label class="flex items-center gap-2 text-sm font-medium text-[#334155]">
                    <input type="checkbox" wire:model="settings.cta.button_visible" class="size-4 rounded border-[#cbd5e1] text-[#2563eb]">
                    Show CTA button
                </label>
                <label class="flex items-center gap-2 text-sm font-medium text-[#334155]">
                    <input type="checkbox" wire:model="settings.cta.decorations_visible" class="size-4 rounded border-[#cbd5e1] text-[#2563eb]">
                    Show decorative circles
                </label>
                <label class="flex items-center gap-2 text-sm font-medium text-[#334155]">
                    <input type="checkbox" wire:model="settings.cta.secondary_visible" class="size-4 rounded border-[#cbd5e1] text-[#2563eb]">
                    Show secondary conversation link
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155] md:col-span-2 xl:col-span-1">
                    Eyebrow
                    <input wire:model="settings.cta.eyebrow" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.cta.eyebrow')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Main headline
                    <input wire:model="settings.cta.heading" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.cta.heading')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Gradient-highlighted text
                    <input wire:model="settings.cta.highlight" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.cta.highlight')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155] md:col-span-2 xl:col-span-3">
                    Description
                    <textarea wire:model="settings.cta.description" rows="2" class="rounded-md border-[#dbe2ed] text-sm"></textarea>
                    @error('settings.cta.description')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    CTA button label
                    <input wire:model="settings.cta.button_label" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.cta.button_label')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    CTA button arrow
                    <select wire:model="settings.cta.button_icon" class="rounded-md border-[#dbe2ed] text-sm">
                        <option value="arrow-up-right">Arrow up-right</option>
                        <option value="arrow-right">Arrow right</option>
                        <option value="arrow-up">Arrow up</option>
                    </select>
                    @error('settings.cta.button_icon')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    CTA button destination (site path or HTTPS URL)
                    <input wire:model="settings.cta.button_url" placeholder="/contact" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.cta.button_url')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Secondary link text
                    <input wire:model="settings.cta.secondary_label" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.cta.secondary_label')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Secondary link destination (site path or HTTPS URL)
                    <input wire:model="settings.cta.secondary_url" placeholder="/contact" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.cta.secondary_url')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    CTA contact email
                    <input type="email" wire:model="settings.cta.email" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.cta.email')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                @foreach ([
                    'background_start' => 'CTA background start',
                    'background_end' => 'CTA background end',
                    'highlight_start' => 'Headline gradient start',
                    'highlight_end' => 'Headline gradient end',
                    'button_start' => 'Button gradient start',
                    'button_end' => 'Button gradient end',
                ] as $key => $label)
                    <label class="flex items-center gap-3 text-sm font-medium text-[#334155]">
                        <input type="color" wire:model="settings.cta.{{ $key }}" class="size-9 cursor-pointer rounded border border-[#dbe2ed] bg-white p-1">
                        <span>{{ $label }} <span class="block text-xs font-normal text-[#64748b]">{{ $settings['cta'][$key] }}</span></span>
                    </label>
                    @error('settings.cta.'.$key)<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-sm">
            <header class="border-b border-[#e5eaf2] px-5 py-4">
                <h2 class="text-base font-semibold text-[#172033]">2. Brand & Logo</h2>
                <p class="mt-1 text-sm text-[#64748b]">Select an image already uploaded to the Media Library. The site name is inherited from global business settings.</p>
            </header>
            <div class="grid gap-4 p-5 md:grid-cols-2">
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Footer logo
                    <select wire:model="settings.brand.logo_media_id" class="rounded-md border-[#dbe2ed] text-sm">
                        <option value="">Use shared site logo</option>
                        @foreach ($mediaItems as $media)
                            <option value="{{ $media->id }}">{{ $media->title ?: $media->original_name }} (ID {{ $media->id }})</option>
                        @endforeach
                    </select>
                    @error('settings.brand.logo_media_id')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    <a href="{{ route('admin.media.index') }}" wire:navigate class="text-xs font-semibold text-[#206bc4] hover:underline">Open Media Library</a>
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Company description
                    <textarea wire:model="settings.brand.description" rows="3" class="rounded-md border-[#dbe2ed] text-sm"></textarea>
                    @error('settings.brand.description')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-sm">
            <header class="border-b border-[#e5eaf2] px-5 py-4">
                <h2 class="text-base font-semibold text-[#172033]">3–4. Footer Columns & Menus</h2>
                <p class="mt-1 text-sm text-[#64748b]">Edit link items and ordering in Appearance → Menus. Assign any registered menu to Nova’s Explore, Resources, and legal locations here.</p>
            </header>
            <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach (['footer_company' => 'Explore', 'footer_resources' => 'Resources', 'footer_legal' => 'Bottom legal menu'] as $location => $heading)
                    <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                        {{ $heading }} menu
                        <select wire:model="locationAssignments.{{ $location }}" class="rounded-md border-[#dbe2ed] text-sm">
                            <option value="">Use current Nova footer assignment</option>
                            @if ($footerColumns->isNotEmpty())
                                <optgroup label="Footer columns">
                                    @foreach ($footerColumns as $column)
                                        <option value="column:{{ $column->id }}">{{ $column->label }} column</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @foreach ($menus as $menu)
                                <option value="{{ $menu }}">{{ ucfirst(str_replace(['-', '_'], ' ', $menu)) }}</option>
                            @endforeach
                        </select>
                    </label>
                @endforeach
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Explore heading
                    <input wire:model="settings.menus.footer_company_heading" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.menus.footer_company_heading')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Resources heading
                    <input wire:model="settings.menus.footer_resources_heading" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.menus.footer_resources_heading')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Privacy Policy page
                    <select wire:model="settings.legal.privacy_page_id" class="rounded-md border-[#dbe2ed] text-sm">
                        <option value="">Use bottom legal menu</option>
                        @foreach ($pages as $page)<option value="{{ $page->id }}">{{ $page->title }}</option>@endforeach
                    </select>
                    @error('settings.legal.privacy_page_id')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Terms of Service page
                    <select wire:model="settings.legal.terms_page_id" class="rounded-md border-[#dbe2ed] text-sm">
                        <option value="">Use bottom legal menu</option>
                        @foreach ($pages as $page)<option value="{{ $page->id }}">{{ $page->title }}</option>@endforeach
                    </select>
                    @error('settings.legal.terms_page_id')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-sm">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e5eaf2] px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-[#172033]">5. Contact Information</h2>
                    <p class="mt-1 text-sm text-[#64748b]">Email and phone links are generated with mailto: and tel:. WhatsApp uses a wa.me conversation link.</p>
                </div>
                <button type="button" wire:click="addContact" class="rounded-md border border-[#dbe2ed] px-3 py-2 text-sm font-semibold text-[#334155] hover:bg-[#f8fafc]">Add contact method</button>
            </header>
            <div class="grid gap-4 p-5 md:grid-cols-2">
                @foreach (['email' => 'Email address', 'phone' => 'Phone number', 'whatsapp' => 'WhatsApp number', 'address' => 'Office address'] as $key => $label)
                    <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                        {{ $label }}
                        <input wire:model="settings.contact.{{ $key }}" class="rounded-md border-[#dbe2ed] text-sm">
                        @error('settings.contact.'.$key)<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                @endforeach
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">
                    Contact column heading
                    <input wire:model="settings.contact.heading" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.contact.heading')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
            </div>
            @if (count($settings['contact']['additional']))
                <div class="space-y-3 border-t border-[#eef1f6] p-5">
                    <h3 class="text-sm font-semibold text-[#172033]">Additional contact methods</h3>
                    @foreach ($settings['contact']['additional'] as $index => $contact)
                        <div class="grid items-end gap-3 rounded-md bg-[#f8fafc] p-3 sm:grid-cols-[150px_1fr_1.5fr_auto_auto]">
                            <label class="grid gap-1 text-xs font-medium text-[#475569]">Type
                                <select wire:model="settings.contact.additional.{{ $index }}.type" class="rounded-md border-[#dbe2ed] text-sm">
                                    @foreach (['email' => 'Email', 'phone' => 'Phone', 'whatsapp' => 'WhatsApp', 'address' => 'Address', 'url' => 'Custom URL'] as $type => $name)<option value="{{ $type }}">{{ $name }}</option>@endforeach
                                </select>
                            </label>
                            <label class="grid gap-1 text-xs font-medium text-[#475569]">Label
                                <input wire:model="settings.contact.additional.{{ $index }}.label" class="rounded-md border-[#dbe2ed] text-sm">
                            </label>
                            <label class="grid gap-1 text-xs font-medium text-[#475569]">Value
                                <input wire:model="settings.contact.additional.{{ $index }}.value" class="rounded-md border-[#dbe2ed] text-sm">
                                @error('settings.contact.additional.'.$index.'.value')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>
                            <label class="flex items-center gap-2 pb-2 text-xs"><input type="checkbox" wire:model="settings.contact.additional.{{ $index }}.enabled">Enabled</label>
                            <div class="flex gap-1 pb-1">
                                <button type="button" wire:click="moveContact({{ $index }}, -1)" aria-label="Move contact up" class="rounded border px-2 py-1">↑</button>
                                <button type="button" wire:click="moveContact({{ $index }}, 1)" aria-label="Move contact down" class="rounded border px-2 py-1">↓</button>
                                <button type="button" wire:click="removeContact({{ $index }})" aria-label="Remove contact" class="rounded border px-2 py-1 text-red-700">×</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-sm">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e5eaf2] px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-[#172033]">6. Social Media</h2>
                    <p class="mt-1 text-sm text-[#64748b]">Enabled social links show their matching network icon. Reorder them with the arrows.</p>
                </div>
                <button type="button" wire:click="addSocial" class="rounded-md border border-[#dbe2ed] px-3 py-2 text-sm font-semibold text-[#334155] hover:bg-[#f8fafc]">Add social link</button>
            </header>
            <div class="space-y-3 p-5">
                @foreach ($settings['socials'] as $index => $social)
                    <div class="grid items-end gap-3 rounded-md bg-[#f8fafc] p-3 sm:grid-cols-[150px_1fr_1.5fr_auto_auto]">
                        <label class="grid gap-1 text-xs font-medium text-[#475569]">Platform
                            <select wire:model="settings.socials.{{ $index }}.platform" class="rounded-md border-[#dbe2ed] text-sm">
                                @foreach (['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'twitter' => 'X / Twitter', 'github' => 'GitHub', 'tiktok' => 'TikTok', 'threads' => 'Threads', 'other' => 'Other'] as $platform => $label)<option value="{{ $platform }}">{{ $label }}</option>@endforeach
                            </select>
                        </label>
                        <label class="grid gap-1 text-xs font-medium text-[#475569]">Label
                            <input wire:model="settings.socials.{{ $index }}.label" class="rounded-md border-[#dbe2ed] text-sm">
                            @error('settings.socials.'.$index.'.label')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="grid gap-1 text-xs font-medium text-[#475569]">HTTPS URL
                            <input wire:model="settings.socials.{{ $index }}.url" class="rounded-md border-[#dbe2ed] text-sm">
                            @error('settings.socials.'.$index.'.url')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="flex items-center gap-2 pb-2 text-xs"><input type="checkbox" wire:model="settings.socials.{{ $index }}.enabled">Enabled</label>
                        <div class="flex gap-1 pb-1">
                            <button type="button" wire:click="moveSocial({{ $index }}, -1)" aria-label="Move social link up" class="rounded border px-2 py-1">↑</button>
                            <button type="button" wire:click="moveSocial({{ $index }}, 1)" aria-label="Move social link down" class="rounded border px-2 py-1">↓</button>
                            <button type="button" wire:click="removeSocial({{ $index }})" aria-label="Remove social link" class="rounded border px-2 py-1 text-red-700">×</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-sm">
            <header class="border-b border-[#e5eaf2] px-5 py-4">
                <h2 class="text-base font-semibold text-[#172033]">7. Footer Appearance</h2>
                <p class="mt-1 text-sm text-[#64748b]">Nova-specific controls for the public footer.</p>
            </header>
            <div class="grid gap-4 p-5 md:grid-cols-2">
                <label class="flex items-center gap-2 text-sm font-medium text-[#334155]"><input type="checkbox" wire:model="settings.footer.visible" class="size-4 rounded border-[#cbd5e1] text-[#2563eb]">Show footer</label>
                <label class="flex items-center gap-2 text-sm font-medium text-[#334155]"><input type="checkbox" wire:model="settings.appearance.back_to_top" class="size-4 rounded border-[#cbd5e1] text-[#2563eb]">Show back-to-top button</label>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-[#e5eaf2] bg-white shadow-sm">
            <header class="border-b border-[#e5eaf2] px-5 py-4">
                <h2 class="text-base font-semibold text-[#172033]">8. Copyright & Legal</h2>
                <p class="mt-1 text-sm text-[#64748b]">Use {year} and {company} in optional text; otherwise the current year is generated automatically.</p>
            </header>
            <div class="grid gap-4 p-5 md:grid-cols-2">
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">Copyright company
                    <input wire:model="settings.copyright.company" class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.copyright.company')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-sm font-medium text-[#334155]">Optional copyright text
                    <input wire:model="settings.copyright.text" placeholder="© {year} {company}. All rights reserved." class="rounded-md border-[#dbe2ed] text-sm">
                    @error('settings.copyright.text')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
            </div>
        </section>

        <div class="sticky bottom-3 flex justify-end rounded-lg border border-[#e5eaf2] bg-white/95 p-3 shadow-lg backdrop-blur">
            @can('appearance.manage')
                <button type="submit" wire:loading.attr="disabled" class="inline-flex min-h-10 items-center justify-center rounded-md bg-[#206bc4] px-5 text-sm font-semibold text-white hover:bg-[#1b5da8] disabled:opacity-60">
                    <span wire:loading.remove>Save Nova settings</span><span wire:loading>Saving…</span>
                </button>
            @else
                <span class="text-sm text-[#64748b]">View-only access</span>
            @endcan
        </div>
    </form>
</div>
