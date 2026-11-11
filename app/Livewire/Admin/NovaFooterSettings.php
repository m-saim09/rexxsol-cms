<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Support\FooterService;
use App\Support\NavigationService;
use App\Support\SiteSettings;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class NovaFooterSettings extends Component
{
    private const PREFIX = 'theme.rexxsol-nova.footer.';

    public array $settings = [];

    public array $locationAssignments = [];

    public function mount(): void
    {
        Gate::authorize('appearance.view');

        $siteSettings = app(SiteSettings::class);
        $publicContact = app(\App\Support\PublicSiteSettings::class)->contactDetails();
        $defaults = [
            'footer.visible' => true,
            'cta.enabled' => true,
            'cta.eyebrow' => 'HAVE A PROJECT IN MIND?',
            'cta.heading' => 'Let’s talk.',
            'cta.highlight' => '',
            'cta.description' => 'Tell us what you’re building. We’ll help you take it forward.',
            'cta.button_visible' => true,
            'cta.button_label' => 'Start a conversation',
            'cta.button_url' => route('website.contact', [], false),
            'cta.email' => $publicContact['email'],
            'cta.decorations_visible' => false,
            'cta.button_icon' => 'arrow-up-right',
            'cta.secondary_visible' => true,
            'cta.secondary_label' => 'Start a conversation',
            'cta.secondary_url' => route('website.contact', [], false),
            'cta.background_start' => '#effaff',
            'cta.background_end' => '#f4fffd',
            'cta.highlight_start' => '#0878d1',
            'cta.highlight_end' => '#22b47e',
            'cta.button_start' => '#0878d1',
            'cta.button_end' => '#008f8d',
            'brand.logo_media_id' => '',
            'brand.description' => (string) ($siteSettings->get('footer.company_description') ?: $siteSettings->get('site.description') ?: 'Thoughtful digital solutions. Built around your business.'),
            'contact.email' => $publicContact['email'],
            'contact.heading' => 'Let’s talk',
            'contact.phone' => $publicContact['phone'],
            'contact.whatsapp' => $publicContact['whatsapp'] ?? '',
            'contact.address' => $publicContact['address'],
            'contact.additional' => [],
            'socials' => $this->sharedSocials($siteSettings),
            'menus.footer_company_heading' => 'Explore',
            'menus.footer_resources_heading' => 'Resources',
            'legal.privacy_page_id' => '',
            'legal.terms_page_id' => '',
            'copyright.company' => (string) ($siteSettings->get('site.company') ?: $siteSettings->get('site.name') ?: 'Rexxsol'),
            'copyright.text' => '',
            'appearance.back_to_top' => true,
        ];

        foreach ($defaults as $key => $default) {
            data_set($this->settings, $key, $siteSettings->get(self::PREFIX.$key, $default));
        }

        $assignments = $siteSettings->get('theme.menu_locations', []);
        $this->locationAssignments = is_array($assignments) && is_array($assignments['rexxsol-nova'] ?? null)
            ? $assignments['rexxsol-nova']
            : [];
    }

    public function addSocial(): void
    {
        Gate::authorize('appearance.manage');
        $this->settings['socials'][] = ['platform' => 'other', 'label' => '', 'url' => '', 'enabled' => true];
    }

    public function removeSocial(int $index): void
    {
        Gate::authorize('appearance.manage');
        unset($this->settings['socials'][$index]);
        $this->settings['socials'] = array_values($this->settings['socials']);
    }

    public function moveSocial(int $index, int $direction): void
    {
        Gate::authorize('appearance.manage');
        $target = $index + $direction;
        if (! isset($this->settings['socials'][$index], $this->settings['socials'][$target])) {
            return;
        }
        [$this->settings['socials'][$index], $this->settings['socials'][$target]] = [$this->settings['socials'][$target], $this->settings['socials'][$index]];
    }

    public function addContact(): void
    {
        Gate::authorize('appearance.manage');
        $this->settings['contact.additional'][] = ['type' => 'email', 'label' => '', 'value' => '', 'enabled' => true];
    }

    public function removeContact(int $index): void
    {
        Gate::authorize('appearance.manage');
        unset($this->settings['contact.additional'][$index]);
        $this->settings['contact.additional'] = array_values($this->settings['contact.additional']);
    }

    public function moveContact(int $index, int $direction): void
    {
        Gate::authorize('appearance.manage');
        $target = $index + $direction;
        if (! isset($this->settings['contact.additional'][$index], $this->settings['contact.additional'][$target])) {
            return;
        }
        [$this->settings['contact.additional'][$index], $this->settings['contact.additional'][$target]] = [$this->settings['contact.additional'][$target], $this->settings['contact.additional'][$index]];
    }

    public function save(): void
    {
        Gate::authorize('appearance.manage');
        $this->validate([
            'settings.footer.visible' => ['required', 'boolean'],
            'settings.cta.enabled' => ['required', 'boolean'],
            'settings.cta.eyebrow' => ['nullable', 'string', 'max:255'],
            'settings.cta.heading' => ['required', 'string', 'max:255'],
            'settings.cta.highlight' => ['nullable', 'string', 'max:255'],
            'settings.cta.description' => ['nullable', 'string', 'max:1000'],
            'settings.cta.button_visible' => ['required', 'boolean'],
            'settings.cta.button_label' => ['nullable', 'string', 'max:100'],
            'settings.cta.button_icon' => ['required', Rule::in(['arrow-up-right', 'arrow-right', 'arrow-up'])],
            'settings.cta.secondary_visible' => ['required', 'boolean'],
            'settings.cta.secondary_label' => ['nullable', 'string', 'max:100'],
            'settings.cta.secondary_url' => ['required', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                if (
                    ! is_string($value)
                    || Str::startsWith($value, '//')
                    || str_contains($value, '\\')
                    || (! Str::startsWith($value, '/') && ! filter_var($value, FILTER_VALIDATE_URL))
                ) {
                    $fail('Enter a site path beginning with / or a complete http(s) URL.');
                    return;
                }

                if (filter_var($value, FILTER_VALIDATE_URL) && ! in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
                    $fail('CTA links must use HTTP or HTTPS.');
                }
            }],
            'settings.cta.button_url' => ['required', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                if (
                    ! is_string($value)
                    || Str::startsWith($value, '//')
                    || str_contains($value, '\\')
                    || (! Str::startsWith($value, '/') && ! filter_var($value, FILTER_VALIDATE_URL))
                ) {
                    $fail('Enter a site path beginning with / or a complete http(s) URL.');
                    return;
                }

                if (filter_var($value, FILTER_VALIDATE_URL) && ! in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
                    $fail('CTA links must use HTTP or HTTPS.');
                }
            }],
            'settings.cta.email' => ['nullable', 'email', 'max:255'],
            'settings.cta.decorations_visible' => ['required', 'boolean'],
            'settings.cta.background_start' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.cta.background_end' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.cta.highlight_start' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.cta.highlight_end' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.cta.button_start' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.cta.button_end' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.brand.logo_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])],
            'settings.brand.description' => ['nullable', 'string', 'max:2000'],
            'settings.contact.email' => ['nullable', 'email', 'max:255'],
            'settings.contact.heading' => ['required', 'string', 'max:100'],
            'settings.contact.phone' => ['nullable', 'string', 'max:60'],
            'settings.contact.whatsapp' => ['nullable', 'string', 'max:60'],
            'settings.contact.address' => ['nullable', 'string', 'max:1000'],
            'settings.contact.additional' => ['array', 'max:20'],
            'settings.contact.additional.*.type' => ['required', Rule::in(['email', 'phone', 'whatsapp', 'address', 'url'])],
            'settings.contact.additional.*.label' => ['required', 'string', 'max:100'],
            'settings.contact.additional.*.value' => ['required', 'string', 'max:500'],
            'settings.contact.additional.*.enabled' => ['required', 'boolean'],
            'settings.socials' => ['array', 'max:20'],
            'settings.socials.*.platform' => ['required', Rule::in(['linkedin', 'facebook', 'instagram', 'youtube', 'twitter', 'github', 'tiktok', 'threads', 'other'])],
            'settings.socials.*.label' => ['required', 'string', 'max:100'],
            'settings.socials.*.url' => ['nullable', 'url:http,https', 'max:500'],
            'settings.socials.*.enabled' => ['required', 'boolean'],
            'settings.menus.footer_company_heading' => ['required', 'string', 'max:100'],
            'settings.menus.footer_resources_heading' => ['required', 'string', 'max:100'],
            'settings.legal.privacy_page_id' => ['nullable', 'integer', Rule::exists('pages', 'id')->where('status', 'published')],
            'settings.legal.terms_page_id' => ['nullable', 'integer', Rule::exists('pages', 'id')->where('status', 'published')],
            'settings.copyright.company' => ['required', 'string', 'max:255'],
            'settings.copyright.text' => ['nullable', 'string', 'max:500'],
            'settings.appearance.back_to_top' => ['required', 'boolean'],
            'locationAssignments' => ['array'],
            'locationAssignments.footer_company' => ['nullable', 'string', 'max:100'],
            'locationAssignments.footer_resources' => ['nullable', 'string', 'max:100'],
            'locationAssignments.footer_legal' => ['nullable', 'string', 'max:100'],
        ]);

        foreach ($this->settings['contact']['additional'] as $index => $contact) {
            if (($contact['type'] ?? '') === 'email' && ! filter_var($contact['value'], FILTER_VALIDATE_EMAIL)) {
                $this->addError("settings.contact.additional.$index.value", 'Enter a valid email address.');
            }
            if (($contact['type'] ?? '') === 'url' && (! filter_var($contact['value'], FILTER_VALIDATE_URL) || ! in_array(parse_url($contact['value'], PHP_URL_SCHEME), ['http', 'https'], true))) {
                $this->addError("settings.contact.additional.$index.value", 'Enter a complete HTTP or HTTPS URL.');
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $values = [];
        foreach ($this->settings as $section => $fields) {
            foreach ($fields as $field => $value) {
                $values[self::PREFIX.$section.'.'.$field] = $value;
            }
        }

        $siteSettings = app(SiteSettings::class);
        $assignments = $siteSettings->get('theme.menu_locations', []);
        $assignments = is_array($assignments) ? $assignments : [];
        $assignments['rexxsol-nova'] = array_replace(
            is_array($assignments['rexxsol-nova'] ?? null) ? $assignments['rexxsol-nova'] : [],
            $this->locationAssignments,
        );
        $values['theme.menu_locations'] = $assignments;
        $siteSettings->put($values, [self::PREFIX.'brand.logo_media_id']);
        NavigationService::forget();
        FooterService::forget();
        Flux::toast(variant: 'success', text: 'Rexxsol Nova CTA and footer settings saved.');
        $this->dispatch('nova-footer-settings-saved');
    }

    public function render()
    {
        $media = Media::query()->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])->latest()->limit(500)->get();
        $pages = Page::query()->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('title')->get();

        return view('livewire.admin.nova-footer-settings', [
            'mediaItems' => $media,
            'pages' => $pages,
            'menus' => NavigationItem::allMenuKeys(),
            'footerColumns' => NavigationItem::query()
                ->where('menu', 'footer')
                ->whereNull('parent_id')
                ->withCount(['children' => fn ($query) => $query->where('enabled', true)])
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (NavigationItem $item): bool => $item->type === 'column' || $item->children_count > 0),
        ])->layout('layouts.admin')->title('Rexxsol Nova Footer Settings');
    }

    private function sharedSocials(SiteSettings $settings): array
    {
        $platforms = ['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'instagram' => 'Instagram'];

        return collect($platforms)->map(fn (string $label, string $platform): array => [
            'platform' => $platform,
            'label' => $label,
            'url' => (string) $settings->get('social.'.$platform, ''),
            'enabled' => true,
        ])->values()->all();
    }
}
