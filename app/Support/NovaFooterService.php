<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class NovaFooterService
{
    private const PREFIX = 'theme.rexxsol-nova.footer.';

    public function data(): array
    {
        $settings = app(SiteSettings::class);
        $publicSettings = app(PublicSiteSettings::class);
        $sharedFooter = app(FooterService::class)->data('rexxsol-nova');
        $brand = $sharedFooter['brand'];
        $logoId = $this->value('brand.logo_media_id');
        if (! $logoId) {
            $logoId = $brand['logo'] ?? null;
        }
        if ($logoId) {
            $media = Media::query()->whereKey($logoId)->where('disk', 'public')->first();
            $brand['logo'] = $media
                ? Storage::disk($media->disk)->url($media->path)
                : (is_string($logoId) && (filter_var($logoId, FILTER_VALIDATE_URL) || Str::startsWith($logoId, '/')) ? $logoId : null);
        }
        if (! is_string($brand['logo'] ?? null) || trim($brand['logo']) === '') {
            $brand['logo'] = $publicSettings->all()['branding.logo'] ?? asset('images/logo.png');
        }
        $brand['name'] = $settings->get('site.name') ?: $settings->get('site.company') ?: 'Rexxsol';
        $defaultBrandDescription = $settings->get('footer.company_description')
            ?: $settings->get('site.description')
            ?: 'Thoughtful digital solutions. Built around your business.';
        $brand['description'] = (string) $this->value('brand.description', $defaultBrandDescription);

        $sharedContact = $publicSettings->contactDetails();
        $contact = [
            'heading' => (string) $this->value('contact.heading', 'Let’s talk'),
            'email' => (string) $this->value('contact.email', $sharedContact['email']),
            'phone' => (string) $this->value('contact.phone', $sharedContact['phone']),
            'whatsapp' => (string) $this->value('contact.whatsapp', $sharedContact['whatsapp'] ?? ''),
            'address' => (string) $this->value('contact.address', $sharedContact['address']),
        ];

        $storedSocials = $this->value('socials');
        $socials = is_array($storedSocials) ? $storedSocials : $this->sharedSocials($settings);
        $socials = collect($socials)->filter(fn ($social): bool => is_array($social)
            && ($social['enabled'] ?? true)
            && trim((string) ($social['url'] ?? '')) !== '')
            ->map(fn (array $social): array => [
                'label' => trim((string) ($social['label'] ?? '')) ?: $this->socialLabel((string) ($social['platform'] ?? '')),
                'platform' => Str::lower(trim((string) ($social['platform'] ?? ''))),
                'url' => trim((string) ($social['url'] ?? '')),
                'icon' => $this->socialIcon((string) ($social['platform'] ?? '')),
            ])->values()->all();

        $baseCta = [
            'enabled' => true,
            'eyebrow' => 'HAVE A PROJECT IN MIND?',
            'heading' => 'Let’s talk.',
            'highlight' => '',
            'description' => 'Tell us what you’re building. We’ll help you take it forward.',
            'button_visible' => true,
            'button_label' => 'Start a conversation',
            'button_url' => route('website.contact', [], false),
            'email' => $contact['email'],
            'decorations_visible' => false,
            'button_icon' => 'arrow-up-right',
            'background_start' => '#effaff',
            'background_end' => '#f4fffd',
            'highlight_start' => '#0878d1',
            'highlight_end' => '#22b47e',
            'button_start' => '#0878d1',
            'button_end' => '#008f8d',
        ];
        $cta = [];
        foreach ($baseCta as $key => $default) {
            $cta[$key] = $this->value('cta.'.$key, $default);
        }
        foreach (['background_start', 'background_end', 'highlight_start', 'highlight_end', 'button_start', 'button_end'] as $colorKey) {
            if (! preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $cta[$colorKey])) {
                $cta[$colorKey] = $baseCta[$colorKey];
            }
        }
        $cta['enabled'] = (bool) $cta['enabled'];
        $cta['button_visible'] = (bool) $cta['button_visible'];
        $cta['decorations_visible'] = (bool) $cta['decorations_visible'];
        $cta['button_icon'] = in_array($cta['button_icon'], ['arrow-up-right', 'arrow-right', 'arrow-up'], true)
            ? $cta['button_icon']
            : 'arrow-up-right';
        $cta['button_url'] = $this->linkUrl((string) $cta['button_url']);
        $cta['secondary_visible'] = (bool) $this->value('cta.secondary_visible', true);
        $cta['secondary_label'] = trim((string) $this->value('cta.secondary_label', $cta['button_label']));
        $cta['secondary_url'] = $this->linkUrl((string) $this->value('cta.secondary_url', $cta['button_url']));

        $footerData = $sharedFooter['locations'] ?? [];
        $locations = [];
        foreach ([
            'footer_company' => 'Explore',
            'footer_resources' => 'Resources',
        ] as $location => $heading) {
            $locations[$location] = [
                'heading' => (string) $this->value('menus.'.$location.'_heading', $heading),
                'links' => $footerData[$location]['links'] ?? [],
            ];
        }

        $legal = $footerData['footer_legal']['links'] ?? [];
        foreach (['privacy' => 'Privacy Policy', 'terms' => 'Terms of Service'] as $type => $label) {
            $pageId = $this->value('legal.'.$type.'_page_id');
            if (! $pageId) {
                continue;
            }
            $page = Page::query()->whereKey($pageId)
                ->where('status', 'published')
                ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->first();
            if ($page) {
                $legal = array_values(array_filter($legal, fn (array $link): bool => Str::lower($link['label'] ?? '') !== Str::lower($label)));
                $legal[] = ['label' => $label, 'url' => $page->publicUrl(), 'target' => '_self', 'is_external' => false];
            }
        }
        if (! request()->routeIs('website.about', 'website.contact')) {
            $legal = array_values(array_filter($legal, fn (array $link): bool => ! $this->isPrivacyOrTermsLink($link)));
            foreach ($locations as &$location) {
                $location['links'] = array_values(array_filter(
                    $location['links'],
                    fn (array $link): bool => ! $this->isPrivacyOrTermsLink($link),
                ));
            }
            unset($location);
        }

        $customContacts = $this->value('contact.additional', []);
        $additionalContacts = collect(is_array($customContacts) ? $customContacts : [])
            ->filter(fn ($item): bool => is_array($item) && ($item['enabled'] ?? true) && trim((string) ($item['value'] ?? '')) !== '')
            ->map(fn (array $item): array => [
                'label' => trim((string) ($item['label'] ?? '')),
                'value' => trim((string) ($item['value'] ?? '')),
                'icon' => $this->contactIcon((string) ($item['type'] ?? 'url')),
                'url' => $this->contactUrl((string) ($item['type'] ?? 'url'), (string) ($item['value'] ?? '')),
            ])->values()->all();

        return [
            'visible' => (bool) $this->value('footer.visible', true),
            'brand' => $brand,
            'contact' => $contact,
            'contact_heading' => $contact['heading'],
            'additional_contacts' => $additionalContacts,
            'socials' => $socials,
            'cta' => $cta,
            'locations' => $locations,
            'legal' => $legal,
            'copyright' => $this->copyright($brand['name']),
            'appearance' => [
                'back_to_top' => (bool) $this->value('appearance.back_to_top', true),
            ],
        ];
    }

    private function copyright(string $company): string
    {
        $custom = trim((string) $this->value('copyright.text', ''));
        if ($custom !== '') {
            return str_replace(['{year}', '{company}'], [(string) now()->year, $company], $custom);
        }

        $name = trim((string) $this->value('copyright.company', $company));

        return '© '.now()->year.' '.$name.'. All rights reserved.';
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

    private function value(string $key, mixed $default = null): mixed
    {
        return app(SiteSettings::class)->get(self::PREFIX.$key, $default);
    }

    private function isPrivacyOrTermsLink(array $link): bool
    {
        $label = Str::lower((string) ($link['label'] ?? ''));
        $path = Str::lower((string) parse_url((string) ($link['url'] ?? ''), PHP_URL_PATH));

        return Str::contains($label, ['privacy', 'policy', 'terms', 'condition'])
            || Str::contains($path, ['privacy', 'policy', 'terms', 'condition']);
    }

    private function socialIcon(string $platform): string
    {
        return match (Str::lower($platform)) {
            'linkedin' => 'fa-brands fa-linkedin-in',
            'facebook' => 'fa-brands fa-facebook-f',
            'instagram' => 'fa-brands fa-instagram',
            'youtube' => 'fa-brands fa-youtube',
            'twitter', 'x' => 'fa-brands fa-x-twitter',
            'github' => 'fa-brands fa-github',
            'tiktok' => 'fa-brands fa-tiktok',
            'threads' => 'fa-brands fa-threads',
            default => 'fa-solid fa-share-nodes',
        };
    }

    private function socialLabel(string $platform): string
    {
        return match (Str::lower(trim($platform))) {
            'linkedin' => 'LinkedIn',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'twitter', 'x' => 'X / Twitter',
            'youtube' => 'YouTube',
            'github' => 'GitHub',
            'tiktok' => 'TikTok',
            'threads' => 'Threads',
            default => 'Social link',
        };
    }

    private function contactIcon(string $type): string
    {
        return match ($type) {
            'email' => 'fa-regular fa-envelope',
            'phone' => 'fa-solid fa-phone',
            'whatsapp' => 'fa-brands fa-whatsapp',
            'address' => 'fa-solid fa-location-dot',
            default => 'fa-solid fa-arrow-up-right-from-square',
        };
    }

    private function contactUrl(string $type, string $value): string
    {
        return match ($type) {
            'email' => 'mailto:'.$value,
            'phone' => 'tel:'.preg_replace('/[^+\d]/', '', $value),
            'whatsapp' => 'https://wa.me/'.preg_replace('/\D+/', '', $value),
            'address' => 'https://maps.google.com/?q='.rawurlencode($value),
            default => $this->linkUrl($value),
        };
    }

    private function linkUrl(string $value): string
    {
        return Str::startsWith($value, '/') ? url($value) : $value;
    }
}
