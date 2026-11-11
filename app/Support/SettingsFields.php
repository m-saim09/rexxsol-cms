<?php

namespace App\Support;

use Illuminate\Validation\Rule;

final class SettingsFields
{
    public static function groups(string $area): array
    {
        if ($area === 'seo') {
            return [
                'General SEO' => ['site_name' => ['site.name', 'Site title', 'text', 'Rexxsol'], 'seo_title' => ['seo.title', 'Default meta title', 'text'], 'seo_description' => ['seo.description', 'Default meta description', 'textarea'], 'seo_keywords' => ['seo.keywords', 'Default keywords', 'text'], 'canonical_base' => ['seo.canonical_base', 'Canonical base URL', 'url']],
                'Social Sharing' => ['social_image' => ['branding.social_image', 'Default social image', 'media'], 'twitter_handle' => ['seo.twitter_handle', 'Twitter handle', 'text'], 'og_title' => ['seo.og_title', 'Open Graph title', 'text'], 'og_description' => ['seo.og_description', 'Open Graph description', 'textarea']],
                'Sitemap & Robots' => ['indexing' => ['seo.indexing', 'Allow public indexing', 'checkbox', false]],
                'Verification' => ['google_verification' => ['seo.google_verification', 'Google verification token', 'text'], 'bing_verification' => ['seo.bing_verification', 'Bing verification token', 'text']],
            ];
        }

        return [
            'General' => ['site_name' => ['site.name', 'Site name', 'text', 'Rexxsol'], 'company_name' => ['site.company', 'Company name', 'text', 'Rexxsol'], 'tagline' => ['site.tagline', 'Site tagline', 'text'], 'company_description' => ['site.description', 'Company description', 'textarea'], 'timezone' => ['site.timezone', 'Timezone', 'timezone', config('app.timezone')], 'language' => ['site.language', 'Default language', 'text', 'en']],
            'Branding' => ['logo' => ['branding.logo', 'Logo', 'media'], 'dark_logo' => ['branding.dark_logo', 'Dark logo', 'media'], 'favicon' => ['branding.favicon', 'Favicon', 'media'], 'social_image' => ['branding.social_image', 'Default social image', 'media']],
            'Contact' => [
                'business_email' => ['contact.business_email', 'Business email', 'email'],
                'support_email' => ['contact.support_email', 'Support email', 'email'],
                'phone' => ['contact.phone', 'Phone', 'text'],
                'whatsapp' => ['contact.whatsapp', 'WhatsApp', 'text'],
                'address' => ['contact.address', 'Address', 'textarea'],
                'office_map_embed_url' => ['contact.office_map_embed_url', 'Google Maps embed URL', 'url'],
                'office_map_directions_url' => ['contact.office_map_directions_url', 'Google Maps directions URL', 'url'],
            ],
            'Social' => collect(['facebook', 'linkedin', 'instagram', 'twitter', 'youtube', 'github'])->mapWithKeys(fn ($name) => [$name => ['social.'.$name, $name === 'twitter' ? 'X / Twitter' : ucfirst($name), 'url']])->all(),
            'Website' => ['footer_text' => ['website.footer_text', 'Footer text', 'textarea'], 'cta_label' => ['website.cta_label', 'Primary CTA label', 'text'], 'cta_url' => ['website.cta_url', 'Primary CTA URL', 'url']],
            'Maintenance' => ['maintenance' => ['site.maintenance_enabled', 'Public site maintenance enabled', 'checkbox', false], 'maintenance_title' => ['site.maintenance_title', 'Maintenance title', 'text'], 'maintenance_message' => ['site.maintenance_message', 'Maintenance message', 'textarea']],
            'Header' => ['header_sticky' => ['header.sticky', 'Sticky header', 'checkbox', false], 'header_cta_label' => ['header.cta_label', 'Header CTA label', 'text'], 'header_cta_url' => ['header.cta_url', 'Header CTA URL', 'url']],
            'Footer' => [
                'footer_description' => ['footer.company_description', 'Company short description', 'textarea'],
                'footer_logo' => ['branding.footer_logo', 'Footer logo', 'media'],
                'footer_cta_label' => ['footer.cta_label', 'Footer CTA label', 'text'],
                'footer_cta_url' => ['footer.cta_url', 'Footer CTA URL', 'url'],
                'footer_cta_enabled' => ['footer.cta_enabled', 'Footer CTA enabled', 'checkbox', true],
                'footer_cta_eyebrow' => ['footer.cta_eyebrow', 'Footer CTA eyebrow', 'text'],
                'footer_cta_heading' => ['footer.cta_heading', 'Footer CTA heading', 'text'],
                'footer_cta_description' => ['footer.cta_description', 'Footer CTA description', 'textarea'],
            ],
        ];
    }

    public static function fields(string $area): array
    {
        return array_merge(...array_values(self::groups($area)));
    }

    public static function rules(string $area): array
    {
        $rules = [];
        foreach (self::fields($area) as $name => $field) {
            $rules['values.'.$name] = match ($field[2]) {
                'checkbox' => ['required', 'boolean'],
                'media' => ['nullable', 'integer', Rule::exists('media', 'id')->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])],
                'url' => ['nullable', 'url:http,https', 'max:500'],
                'email' => ['nullable', 'email', 'max:255'],
                'timezone' => ['required', 'timezone:all'],
                'textarea' => ['nullable', 'string', 'max:2000'],
                default => ['nullable', 'string', 'max:255'],
            };
        }
        $rules['values.site_name'] = ['required', 'string', 'max:255'];
        if ($area === 'seo') {
            $rules['values.canonical_base'] = ['required_if:values.indexing,true', 'nullable', 'url:http,https', 'max:500'];
            $rules['values.seo_description'] = ['nullable', 'string', 'max:255'];
            $rules['values.og_description'] = ['nullable', 'string', 'max:255'];
            foreach (['google_verification', 'bing_verification'] as $name) {
                $rules['values.'.$name] = ['nullable', 'string', 'max:255', 'regex:/\A[a-zA-Z0-9_=-]+\z/'];
            }
            $rules['values.twitter_handle'] = ['nullable', 'regex:/\A@?[a-zA-Z0-9_]{1,15}\z/'];
        } else {
            $rules['values.language'] = ['required', 'regex:/\A[a-z]{2,3}(-[A-Z]{2})?\z/'];
            $rules['values.office_map_embed_url'] = [
                'nullable',
                'url:https',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && $value !== '' && GoogleMapsUrl::embed($value) === null) {
                        $fail('Enter a secure Google Maps embed URL with a valid map embed path.');
                    }
                },
            ];
            $rules['values.office_map_directions_url'] = [
                'nullable',
                'url:https',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && $value !== '' && GoogleMapsUrl::directions($value) === null) {
                        $fail('Enter a secure Google Maps directions URL with a destination.');
                    }
                },
            ];
            foreach (['phone', 'whatsapp'] as $name) {
                $rules['values.'.$name] = ['nullable', 'string', 'max:60'];
            }
        }

        return $rules;
    }
}
