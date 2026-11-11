<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class PublicSiteSettings
{
    public const CACHE_KEY = 'cms.public-settings.v2';

    private const VERIFIED_CONTACT_DETAILS = [
        'email' => 'contact@rexxsol.com',
        'phone' => '+92 333 2465888',
        'address' => 'Pearl City, Canal Rd, Rahim Yar Khan, 64200, Pakistan',
    ];

    private const VERIFIED_OFFICE_MAP_EMBED_URL = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3509.653683949486!2d70.32835107421269!3d28.399526194625874!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39375de324b503a5%3A0xcc942cc9d96036a1!2sRexxsol%20software%20solutions%20and%20trainings%20institute!5e0!3m2!1sen!2s!4v1791459311736!5m2!1sen!2s';

    private const KEYS = [
        'site.name', 'site.company', 'site.tagline', 'site.description', 'branding.logo', 'branding.dark_logo',
        'branding.favicon', 'branding.footer_logo', 'contact.business_email', 'contact.support_email', 'contact.phone', 'contact.whatsapp',
        'contact.address', 'contact.office_map_embed_url', 'contact.office_map_directions_url',
        'social.facebook', 'social.linkedin', 'social.instagram', 'social.twitter', 'social.youtube',
        'social.github', 'website.footer_text', 'website.copyright', 'website.cta_label', 'website.cta_url',
        'header.sticky', 'header.cta_label', 'header.cta_url', 'footer.company_description', 'footer.cta_label', 'footer.cta_url',
        'widgets.footer_text',
        'appearance.brand_color', 'appearance.heading_font', 'appearance.body_font',
    ];

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function (): array {
            $settings = app(SiteSettings::class);
            $storedSettings = $settings->all();

            return collect(self::KEYS)->mapWithKeys(function (string $key) use ($storedSettings): array {
                $value = $storedSettings[$key] ?? null;
                if ($key === 'contact.office_map_embed_url' && ! array_key_exists($key, $storedSettings)) {
                    $value = self::VERIFIED_OFFICE_MAP_EMBED_URL;
                }
                if (in_array($key, ['branding.logo', 'branding.dark_logo', 'branding.favicon', 'branding.footer_logo'], true) && $value) {
                    $media = Media::whereKey($value)->where('disk', 'public')->first();
                    $value = $media ? Storage::disk($media->disk)->url($media->path) : null;
                }

                return [$key => $value];
            })->all();
        });
    }

    public function contactDetails(): array
    {
        $settings = $this->all();
        $email = $settings['contact.business_email'] ?? $settings['contact.support_email'] ?? null;
        $phone = $settings['contact.phone'] ?? null;
        $whatsapp = $settings['contact.whatsapp'] ?? null;
        $address = $settings['contact.address'] ?? null;
        $phoneDigits = preg_replace('/\D+/', '', (string) $phone);
        $isDemoContactData = str_contains(mb_strtolower((string) ($settings['site.name'] ?? '')), 'demo')
            || str_ends_with(mb_strtolower((string) $email), '.local')
            || preg_match('/55501\d{2}$/', (string) $phoneDigits);

        if ($isDemoContactData) {
            $email = self::VERIFIED_CONTACT_DETAILS['email'];
            $phone = self::VERIFIED_CONTACT_DETAILS['phone'];
            $address = self::VERIFIED_CONTACT_DETAILS['address'];
            $whatsapp = null;
        }

        return [
            'email' => $email ?: self::VERIFIED_CONTACT_DETAILS['email'],
            'phone' => $phone ?: self::VERIFIED_CONTACT_DETAILS['phone'],
            'whatsapp' => $whatsapp,
            'address' => $address ?: self::VERIFIED_CONTACT_DETAILS['address'],
        ];
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
