<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

final class ThemeRegistry
{
    public const CORPORATE = 'rexxsol-corporate';

    public const BIZCONSULT = 'bizconsult';

    public function all(): array
    {
        $themes = [
            self::CORPORATE => [
                'name' => 'Rexxsol Corporate',
                'slug' => self::CORPORATE,
                'version' => '1.0.1',
                'description' => 'A refined corporate theme for technology, software, and consulting businesses.',
                'preview' => asset(
                    is_file(public_path('images/themes/rexxsol-corporate-preview.jpg'))
                        ? 'images/themes/rexxsol-corporate-preview.jpg'
                        : 'images/logo.png'
                ),
                'view' => 'website',
                'path' => resource_path('views/themes/rexxsol-corporate'),
                'assets_path' => public_path('themes/rexxsol-corporate'),
                'layouts' => ['default', 'full-width'],
                'page_template' => 'templates.page',
                'menu_locations' => ['primary', 'footer_company', 'footer_resources', 'footer_legal'],
                'widget_areas' => ['footer_one', 'footer_two', 'footer_three'],
                'theme_options' => [
                    'brand_color' => ['default' => '#000080'],
                    'heading_font' => ['default' => 'Georgia'],
                    'body_font' => ['default' => 'Instrument Sans'],
                    'copyright' => ['default' => ''],
                ],
                'footer' => [
                    'columns' => 2,
                    'supports_logo' => true,
                    'supports_description' => true,
                    'supports_contact' => true,
                    'supports_socials' => true,
                    'supports_cta' => false,
                    'locations' => ['footer_company' => 'Company', 'footer_resources' => 'Resources', 'footer_legal' => 'Legal'],
                ],
                'supports' => ['hero', 'rich-text', 'image-text', 'services', 'projects', 'blog', 'testimonials', 'team', 'faq', 'cta', 'contact'],
            ],
            self::BIZCONSULT => [
                'name' => 'BizConsult',
                'slug' => self::BIZCONSULT,
                'version' => '1.0.0',
                'description' => 'A bright, green consulting theme with illustrated sections and responsive Bootstrap interactions.',
                'preview' => asset('assets-bizconsult/img/hero.png'),
                'preview_note' => 'Temporary preview image from the supplied BizConsult presentation assets.',
                'view' => 'themes.bizconsult',
                'path' => resource_path('views/themes/bizconsult'),
                'assets_path' => public_path('themes/bizconsult'),
                'layouts' => ['default', 'full-width'],
                'page_template' => 'templates.page',
                'menu_locations' => ['primary', 'footer_column_1', 'footer_column_2', 'footer_column_3', 'footer_legal', 'mobile'],
                'widget_areas' => ['footer_one', 'footer_two', 'footer_three'],
                'theme_options' => [
                    'brand_color' => ['default' => '#78b77a'],
                    'heading_font' => ['default' => 'Inter'],
                    'body_font' => ['default' => 'Heebo'],
                    'copyright' => ['default' => ''],
                ],
                'footer' => [
                    'columns' => 3,
                    'supports_logo' => true,
                    'supports_description' => true,
                    'supports_contact' => true,
                    'supports_socials' => true,
                    'supports_cta' => true,
                    'locations' => ['footer_column_1' => 'Column One', 'footer_column_2' => 'Column Two', 'footer_column_3' => 'Column Three', 'footer_legal' => 'Legal'],
                ],
                'supports' => ['hero', 'rich-text', 'services', 'projects', 'blog', 'testimonials', 'team', 'faq', 'cta'],
            ],
        ];

        foreach ($this->discoveredThemes() as $slug => $manifest) {
            $themes[$slug] = array_replace_recursive($themes[$slug] ?? [], $manifest);
            if (! isset($themes[$slug]['footer']) || ! is_array($themes[$slug]['footer'])) {
                $themes[$slug]['footer'] = [
                    'columns' => max(1, count($themes[$slug]['menu_locations'] ?? ['primary'])),
                    'supports_logo' => true,
                    'supports_description' => true,
                    'supports_contact' => true,
                    'supports_socials' => true,
                    'supports_cta' => in_array('cta', $themes[$slug]['supports'] ?? [], true),
                    'locations' => collect($themes[$slug]['menu_locations'] ?? ['primary'])->filter(fn ($location) => str_contains((string) $location, 'footer'))->mapWithKeys(fn ($location) => [(string) $location => ucfirst(str_replace(['_', '-'], ' ', (string) $location))])->all(),
                ];
            }
        }

        return $themes;
    }

    public function manifest(string $slug): array
    {
        $theme = $this->find($slug);

        return $theme ?? [
            'name' => ucfirst(str_replace(['-', '_'], ' ', $slug)),
            'slug' => $slug,
            'version' => '0.0.0',
            'description' => 'A discovered theme without a manifest is using the shared fallback metadata.',
            'preview' => asset('images/logo.png'),
            'view' => 'website',
            'path' => resource_path('views/themes/'.$slug),
            'assets_path' => public_path('themes/'.$slug),
            'layouts' => ['default'],
            'menu_locations' => ['primary'],
            'widget_areas' => [],
            'footer' => [
                'columns' => 1,
                'supports_logo' => true,
                'supports_description' => true,
                'supports_contact' => true,
                'supports_socials' => true,
                'supports_cta' => false,
                'locations' => ['footer' => 'Footer'],
            ],
            'supports' => [],
        ];
    }

    public function find(string $slug): ?array
    {
        return $this->all()[$slug] ?? null;
    }

    public function active(): array
    {
        $slug = app(SiteSettings::class)->get('appearance.active_theme', self::CORPORATE);
        $themes = $this->all();

        if (! is_string($slug)) {
            return $themes[self::CORPORATE];
        }

        return $themes[$slug] ?? $themes[self::CORPORATE];
    }

    public function isRegistered(string $slug): bool
    {
        return $this->find($slug) !== null;
    }

    public function supports(string $slug, string $feature): bool
    {
        $manifest = $this->manifest($slug);
        $supports = $manifest['supports'] ?? [];

        if (! is_array($supports)) {
            return false;
        }

        return in_array($feature, $supports, true);
    }

    public function resolveComponent(string $component, ?string $themeSlug = null): string
    {
        $slug = $themeSlug ?? $this->active()['slug'];

        $candidates = [
            "themes.{$slug}.components.{$component}",
            "components.theme-blocks.{$component}",
            "components.{$component}",
            'components.theme-blocks.fallback',
        ];

        foreach ($candidates as $candidate) {
            if (View::exists($candidate)) {
                return $candidate;
            }
        }

        return 'components.theme-blocks.fallback';
    }

    public function resolveTemplate(string $template, ?string $themeSlug = null): string
    {
        $slug = $themeSlug ?? $this->active()['slug'];
        $manifestTemplate = $template === 'page'
            ? ($this->find($slug)['page_template'] ?? 'templates.page')
            : null;

        $candidates = array_filter([
            $manifestTemplate ? "themes.{$slug}.{$manifestTemplate}" : null,
            "themes.{$slug}.templates.{$template}",
            "themes.{$slug}.pages.{$template}",
            "themes.{$slug}.templates.default",
            "website.pages.{$template}",
            'website.pages.page',
        ]);

        foreach ($candidates as $candidate) {
            if (View::exists($candidate)) {
                return $candidate;
            }
        }

        return 'website.pages.page';
    }

    public function themePath(string $slug): string
    {
        return resource_path('views/themes/'.$slug);
    }

    public function assetPath(string $slug): string
    {
        return public_path('themes/'.$slug);
    }

    public function footerCapabilities(?string $slug = null): array
    {
        $manifest = $this->manifest($slug ?? $this->active()['slug']);

        return is_array($manifest['footer'] ?? null) ? $manifest['footer'] : [
            'columns' => 1,
            'supports_logo' => true,
            'supports_description' => true,
            'supports_contact' => true,
            'supports_socials' => true,
            'supports_cta' => false,
            'locations' => collect($manifest['menu_locations'] ?? ['primary'])->filter(fn ($location) => str_contains((string) $location, 'footer'))->mapWithKeys(fn ($location) => [(string) $location => ucfirst(str_replace(['_', '-'], ' ', (string) $location))])->all(),
        ];
    }

    public function previewUrl(string $slug): string
    {
        abort_unless($this->isRegistered($slug), 404);

        return Route::has('theme.preview')
            ? route('theme.preview', ['theme' => $slug])
            : url('/').'?'.http_build_query(['theme' => $slug]);
    }

    /**
     * Discover themes that follow the standard resources/views/themes/{slug}/theme.php contract.
     */
    private function discoveredThemes(): array
    {
        $themeDirectory = resource_path('views/themes');

        if (! is_dir($themeDirectory)) {
            return [];
        }

        $themes = [];

        foreach (glob($themeDirectory.'/*', GLOB_ONLYDIR) ?: [] as $directory) {
            $slug = basename($directory);
            $manifestPath = $directory.'/theme.php';

            if (! is_file($manifestPath)) {
                continue;
            }

            $manifest = require $manifestPath;

            if (! is_array($manifest)) {
                continue;
            }

            $themes[$slug] = [
                'name' => $manifest['name'] ?? ucfirst(str_replace(['-', '_'], ' ', $slug)),
                'slug' => $manifest['slug'] ?? $slug,
                'version' => $manifest['version'] ?? '0.0.0',
                'description' => $manifest['description'] ?? '',
                'preview' => $manifest['preview'] ?? asset('images/logo.png'),
                'preview_note' => $manifest['preview_note'] ?? null,
                'view' => $manifest['view'] ?? 'themes.'.str_replace(['-', '_'], '', $slug),
                'path' => $directory,
                'assets_path' => public_path('themes/'.$slug),
                'layouts' => $manifest['layouts'] ?? ['default'],
                'page_template' => $manifest['page_template'] ?? 'templates.page',
                'menu_locations' => $manifest['menu_locations'] ?? ['primary'],
                'widget_areas' => $manifest['widget_areas'] ?? [],
                'theme_options' => $manifest['theme_options'] ?? [],
                'supports' => $manifest['supports'] ?? [],
            ];
        }

        return $themes;
    }
}
