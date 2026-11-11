<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ThemeOptionsConfiguration
{
    private const FONT_FAMILIES = [
        'Instrument Sans' => "'Instrument Sans', ui-sans-serif, system-ui, sans-serif",
        'Arial' => 'Arial, Helvetica, sans-serif',
        'Georgia' => 'Georgia, serif',
        'Times New Roman' => "'Times New Roman', Times, serif",
        'Inter' => 'Inter, ui-sans-serif, system-ui, sans-serif',
        'Heebo' => 'Heebo, Arial, sans-serif',
    ];

    public function schema(array $theme): array
    {
        $declared = $theme['theme_options'] ?? [];
        $themeDefaults = match ($theme['slug'] ?? '') {
            ThemeRegistry::BIZCONSULT => ['brand_color' => '#78b77a', 'heading_font' => 'Inter', 'body_font' => 'Heebo'],
            ThemeRegistry::CORPORATE => ['brand_color' => '#000080', 'heading_font' => 'Georgia', 'body_font' => 'Instrument Sans'],
            default => ['brand_color' => '#1168aa', 'heading_font' => 'Instrument Sans', 'body_font' => 'Instrument Sans'],
        };
        $defaults = [
            'brand_color' => ['label' => 'Brand color', 'default' => $themeDefaults['brand_color'], 'description' => 'Primary buttons, links, and brand highlights.'],
            'heading_font' => ['label' => 'Heading font', 'default' => $themeDefaults['heading_font']],
            'body_font' => ['label' => 'Body font', 'default' => $themeDefaults['body_font']],
            'copyright' => ['label' => 'Copyright', 'default' => ''],
        ];

        if (! is_array($declared)) {
            return [];
        }

        return collect($declared)->filter(fn ($definition): bool => $definition !== false)
            ->mapWithKeys(fn ($definition, string $key): array => [
                $key => isset($defaults[$key]) && is_array($definition)
                    ? array_replace($defaults[$key], $definition)
                    : ($defaults[$key] ?? []),
            ])->all();
    }

    public function defaults(array $theme): array
    {
        return collect($this->schema($theme))->mapWithKeys(fn (array $option, string $key): array => [$key => $option['default']])->all();
    }

    public function availableFonts(array $theme): array
    {
        return match ($theme['slug'] ?? '') {
            ThemeRegistry::BIZCONSULT => ['Heebo', 'Inter', 'Instrument Sans', 'Arial', 'Georgia', 'Times New Roman'],
            default => ['Instrument Sans', 'Arial', 'Georgia', 'Times New Roman'],
        };
    }

    public function values(array $theme): array
    {
        $defaults = $this->defaults($theme);
        $slug = $theme['slug'];
        $settings = app(SiteSettings::class);
        $values = [];

        foreach ($defaults as $key => $default) {
            $stored = $settings->get($this->key($slug, $key));
            if ($stored === null) {
                $stored = match ($key) {
                    'brand_color' => $slug === ThemeRegistry::CORPORATE ? $settings->get('appearance.brand_color') : null,
                    'heading_font' => $slug === ThemeRegistry::CORPORATE ? $settings->get('appearance.heading_font') : null,
                    'body_font' => $slug === ThemeRegistry::CORPORATE ? $settings->get('appearance.body_font') : null,
                    'copyright' => $settings->get('theme.copyright') ?: $settings->get('website.copyright'),
                    default => null,
                };
            }
            $values[$key] = $stored ?? $default;
        }

        if (isset($values['brand_color']) && ! preg_match('/\A#[0-9a-fA-F]{6}\z/', (string) $values['brand_color'])) {
            $values['brand_color'] = $defaults['brand_color'];
        }

        return $values;
    }

    public function publicValues(array $theme): array
    {
        $values = $this->values($theme);
        $settings = app(SiteSettings::class);
        if (isset($values['copyright']) && $values['copyright'] === '') {
            $values['copyright'] = (string) ($settings->get('theme.copyright') ?: $settings->get('website.copyright') ?: '');
        }
        $siteName = (string) ($settings->get('site.name') ?: 'Rexxsol');
        if (isset($values['copyright'])) {
            $values['copyright'] = $this->renderCopyright((string) $values['copyright'], $siteName);
        }

        return $values;
    }

    public function fingerprint(array $theme): string
    {
        return hash('sha256', json_encode(['theme' => $theme['slug'], 'values' => $this->databaseValues($theme)], JSON_THROW_ON_ERROR));
    }

    public function validate(array $theme, array $values): array
    {
        $schema = $this->schema($theme);
        $errors = [];

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, $schema)) {
                $errors[$key] = 'This option is not supported by the selected theme.';

                continue;
            }
            if ($key === 'brand_color' && ! preg_match('/\A#[0-9a-fA-F]{6}\z/', (string) $value)) {
                $errors[$key] = 'Enter a six-digit HEX color, for example #000080.';
            }
            if (in_array($key, ['heading_font', 'body_font'], true) && ! in_array((string) $value, $this->availableFonts($theme), true)) {
                $errors[$key] = 'Choose a supported font family.';
            }
            if ($key === 'copyright' && mb_strlen((string) $value) > 255) {
                $errors[$key] = 'Copyright text must be 255 characters or fewer.';
            }
            if ($key === 'copyright' && preg_match('/<|>|\{\{|\{!!|<\?/u', (string) $value)) {
                $errors[$key] = 'Enter plain text only. HTML, Blade, and PHP are not allowed.';
            }
            if ($key === 'copyright' && preg_match_all('/\{([^{}]+)\}/', (string) $value, $tokens)) {
                foreach ($tokens[1] as $token) {
                    if (! in_array($token, ['year', 'site_name'], true)) {
                        $errors[$key] = 'Supported placeholders are {year} and {site_name}.';
                    }
                }
            }
        }

        return $errors;
    }

    public function save(array $theme, array $values, string $baseline): bool
    {
        if ($errors = $this->validate($theme, $values)) {
            throw \Illuminate\Validation\ValidationException::withMessages(
                collect($errors)->mapWithKeys(fn (string $message, string $key): array => [$key => $message])->all(),
            );
        }

        $slug = $theme['slug'];
        $saved = DB::transaction(function () use ($slug, $values, $baseline, $theme): bool {
            $defaults = $this->defaults($theme);
            $rows = SiteSetting::query()
                ->whereIn('key', array_map(fn (string $key): string => $this->key($slug, $key), array_keys($defaults)))
                ->lockForUpdate()
                ->get()
                ->keyBy('key');
            $stored = collect($defaults)->mapWithKeys(fn ($default, $key): array => [
                $key => $rows[$this->key($slug, $key)]->value ?? null,
            ])->all();
            if (! hash_equals(hash('sha256', json_encode(['theme' => $slug, 'values' => $stored], JSON_THROW_ON_ERROR)), $baseline)) {
                return false;
            }
            $old = $this->values($theme);
            foreach ($values as $option => $value) {
                SiteSetting::query()->updateOrCreate(['key' => $this->key($slug, $option)], ['value' => $value]);
            }

            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'Appearance',
                'subject_type' => 'theme_options',
                'subject_id' => 0,
                'subject_label' => $theme['name'].' Theme Options',
                'changes' => [
                    'theme' => ['old' => $slug, 'new' => $slug],
                    'options' => collect($values)->mapWithKeys(fn ($value, $key): array => [$key => ['old' => $old[$key] ?? null, 'new' => $value]])->all(),
                    'scope' => 'theme:'.$slug,
                ],
            ]);

            return true;
        });

        if ($saved) {
            Cache::forget(SiteSettings::CACHE_KEY);
            PublicSiteSettings::forget();
            FooterService::forget();
        }

        return $saved;
    }

    public function fontFamily(string $name, string $fallback = 'Arial'): string
    {
        return self::FONT_FAMILIES[$name] ?? self::FONT_FAMILIES[$fallback];
    }

    public function renderCopyright(string $value, string $siteName): string
    {
        return strtr($value, ['{year}' => now()->format('Y'), '{site_name}' => $siteName]);
    }

    private function databaseValues(array $theme): array
    {
        $keys = array_map(fn (string $key): string => $this->key($theme['slug'], $key), array_keys($this->defaults($theme)));
        $query = SiteSetting::query()->whereIn('key', $keys)->get()->keyBy('key');

        return collect($this->defaults($theme))->mapWithKeys(fn ($default, $key): array => [
            $key => $query[$this->key($theme['slug'], $key)]->value ?? null,
        ])->all();
    }

    private function key(string $theme, string $option): string
    {
        return "appearance.theme_options.{$theme}.{$option}";
    }
}
