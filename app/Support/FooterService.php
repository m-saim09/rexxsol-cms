<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class FooterService
{
    public const CACHE_KEY = 'cms.public-footer.v4';

    public function data(?string $previewTheme = null): array
    {
        $themeSlug = $previewTheme ?: app(ThemeRegistry::class)->active()['slug'];
        $cacheKey = self::CACHE_KEY.'.'.$themeSlug;

        return Cache::remember($cacheKey, app(NavigationService::class)->cacheTtl(), function () use ($themeSlug): array {
            $settings = app(SiteSettings::class);
            $themeRegistry = app(ThemeRegistry::class);
            $capabilities = $themeRegistry->footerCapabilities($themeSlug);

            $brand = [
                'logo' => $settings->get('branding.footer_logo') ?: $settings->get('branding.logo'),
                'name' => $settings->get('site.name') ?: $settings->get('site.company') ?: 'Rexxsol',
                'company' => $settings->get('site.company') ?: 'Rexxsol',
                'description' => $settings->get('footer.company_description') ?: $settings->get('site.description') ?: 'Technology strategy, product delivery, and digital transformation for ambitious teams.',
            ];

            $contact = app(PublicSiteSettings::class)->contactDetails();

            $rawSocials = [
                'linkedin' => ['url' => $settings->get('social.linkedin'), 'label' => 'LinkedIn', 'icon' => 'fab fa-linkedin'],
                'facebook' => ['url' => $settings->get('social.facebook'), 'label' => 'Facebook', 'icon' => 'fab fa-facebook'],
                'instagram' => ['url' => $settings->get('social.instagram'), 'label' => 'Instagram', 'icon' => 'fab fa-instagram'],
            ];

            $socials = collect($rawSocials)
                ->filter(fn (array $item): bool => ! empty($item['url']) && is_string($item['url']) && trim($item['url']) !== '')
                ->all();

            $cta = [
                'enabled' => (bool) $settings->get('footer.cta_enabled', true),
                'eyebrow' => (string) $settings->get('footer.cta_eyebrow', 'Get in touch'),
                'heading' => (string) $settings->get('footer.cta_heading', 'Let’s build something great together.'),
                'description' => (string) $settings->get('footer.cta_description', 'Smart digital solutions for your next chapter.'),
                'button_label' => (string) $settings->get('footer.cta_button_label', 'Start a project'),
                'button_url' => (string) $settings->get('footer.cta_button_url', route('website.contact')),
                'image' => (string) $settings->get('footer.cta_image', asset('assets-bizconsult/img/hero.png')),
            ];

            $themeOptions = app(ThemeOptionsConfiguration::class)->publicValues($themeRegistry->manifest($themeSlug));
            $copyright = (string) (($themeOptions['copyright'] ?? '') ?: ('© '.now()->year.' '.$brand['name'].'. All rights reserved.'));

            // Fetch columns and links
            $allFooterItems = NavigationItem::query()
                ->where('menu', 'footer')
                ->where('enabled', true)
                ->with(['children' => fn ($query) => $query->where('enabled', true)->orderBy('sort_order')->orderBy('id')])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $columnsData = [];
            $columnItems = $allFooterItems->whereNull('parent_id')->values();

            // Check if there are columns or only legacy flat items
            $hasExplicitColumns = $columnItems->contains(fn (NavigationItem $item) => $item->type === 'column' || $item->children->isNotEmpty());

            if ($hasExplicitColumns) {
                foreach ($columnItems as $col) {
                    $links = $this->resolveLinks($col->children);
                    $columnsData[] = [
                        'id' => $col->id,
                        'heading' => $col->label,
                        'slug' => Str::slug($col->label),
                        'enabled' => $col->enabled,
                        'sort_order' => $col->sort_order,
                        'links' => $links,
                    ];
                }
            } elseif ($columnItems->isNotEmpty()) {
                // Legacy flat items without column parents: wrap in a single column
                $links = $this->resolveLinks($columnItems);
                $columnsData[] = [
                    'id' => 0,
                    'heading' => 'Quick Links',
                    'slug' => 'quick-links',
                    'enabled' => true,
                    'sort_order' => 1,
                    'links' => $links,
                ];
            }

            // Theme Location Mapping
            $themeAssignments = $settings->get('theme.menu_locations', []);
            $themeLocations = is_array($themeAssignments) && isset($themeAssignments[$themeSlug]) && is_array($themeAssignments[$themeSlug])
                ? $themeAssignments[$themeSlug]
                : [];

            $locationMap = [];
            $legalLinks = [];

            // Identify legal links
            $legalColumn = collect($columnsData)->first(function (array $col): bool {
                $slug = Str::lower($col['slug']);
                return str_contains($slug, 'legal') || str_contains($slug, 'privacy') || str_contains($slug, 'terms');
            });

            if ($legalColumn) {
                $legalLinks = $legalColumn['links'];
            }

            $footerLocations = $capabilities['locations'] ?? [];

            $nonLegalColumns = collect($columnsData)->reject(function (array $col) use ($legalColumn): bool {
                return $legalColumn && $col['id'] === $legalColumn['id'];
            })->values();

            $colIndex = 0;
            foreach ($footerLocations as $locKey => $locLabel) {
                $assigned = $themeLocations[$locKey] ?? null;

                if ($assigned && str_starts_with($assigned, 'column:')) {
                    $colId = (int) substr($assigned, 7);
                    $matchedCol = collect($columnsData)->firstWhere('id', $colId);
                    if ($matchedCol) {
                        $locationMap[$locKey] = [
                            'heading' => $matchedCol['heading'],
                            'links' => $matchedCol['links'],
                        ];
                        if (str_contains($locKey, 'legal')) {
                            $legalLinks = $matchedCol['links'];
                        }
                        continue;
                    }
                } elseif ($assigned && ! empty($assigned)) {
                    // Assigned to a named menu (e.g. primary, legal, etc.)
                    $menuItems = app(NavigationService::class)->menus()[$assigned] ?? [];
                    $locationMap[$locKey] = [
                        'heading' => is_string($locLabel) ? $locLabel : ucfirst(str_replace(['_', '-'], ' ', $locKey)),
                        'links' => $menuItems,
                    ];
                    if (str_contains($locKey, 'legal')) {
                        $legalLinks = $menuItems;
                    }
                    continue;
                }

                // Default auto fallback
                if (str_contains($locKey, 'legal')) {
                    $locationMap[$locKey] = [
                        'heading' => is_string($locLabel) ? $locLabel : 'Legal',
                        'links' => $legalLinks,
                    ];
                } else {
                    $fallbackCol = $nonLegalColumns->get($colIndex) ?? $nonLegalColumns->first();
                    $colIndex++;

                    $locationMap[$locKey] = [
                        'heading' => $fallbackCol['heading'] ?? (is_string($locLabel) ? $locLabel : ucfirst(str_replace(['_', '-'], ' ', $locKey))),
                        'links' => $fallbackCol['links'] ?? [],
                    ];
                }
            }

            $automaticFooterLinks = $this->resolveLinks(NavigationItem::query()
                ->where('menu', 'footer')
                ->where('is_automatic', true)
                ->where('enabled', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get());
            if ($automaticFooterLinks) {
                $automaticIds = collect($automaticFooterLinks)->pluck('id')->all();
                $targetLocation = collect($footerLocations)->first(function (string $location) use ($locationMap, $automaticIds): bool {
                    $linkIds = collect($locationMap[$location]['links'] ?? [])->pluck('id');

                    return ! str_contains($location, 'legal') && $linkIds->intersect($automaticIds)->isNotEmpty();
                });

                foreach ($locationMap as $location => &$data) {
                    if ($targetLocation === null || $location !== $targetLocation) {
                        $data['links'] = collect($data['links'] ?? [])->reject(fn (array $link): bool => in_array($link['id'], $automaticIds, true))->values()->all();
                    }
                }
                unset($data);

                if ($targetLocation === null) {
                    $targetLocation = collect($footerLocations)->first(fn (string $location): bool => ! str_contains($location, 'legal'));
                    if ($targetLocation) {
                        $locationMap[$targetLocation]['links'] = array_merge($locationMap[$targetLocation]['links'] ?? [], $automaticFooterLinks);
                    }
                }
            }

            // Display columns for themes iterating directly over columns
            $maxColumns = $capabilities['columns'] ?? 4;
            $displayColumns = $nonLegalColumns->take($maxColumns)->all();

            return [
                'brand' => $brand,
                'contact' => $contact,
                'socials' => $socials,
                'columns' => $columnsData,
                'display_columns' => $displayColumns,
                'locations' => $locationMap,
                'automatic_links' => $automaticFooterLinks,
                'legal' => $legalLinks,
                'copyright' => $copyright,
                'cta' => $cta,
                'capabilities' => $capabilities,
            ];
        });
    }

    public function forLocation(string $location, ?string $themeSlug = null): array
    {
        $data = $this->data($themeSlug);

        return $data['locations'][$location]['links'] ?? [];
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY.'.rexxsol-corporate');
        Cache::forget(self::CACHE_KEY.'.bizconsult');

        foreach (array_keys(app(ThemeRegistry::class)->all()) as $slug) {
            Cache::forget(self::CACHE_KEY.'.'.$slug);
        }
    }

    private function resolveLinks(iterable $items): array
    {
        $links = [];

        foreach ($items as $item) {
            if (! $item->enabled) {
                continue;
            }

            $url = $this->url($item);
            if ($url === null) {
                continue; // Omit broken / unpublished content
            }

            $links[] = [
                'id' => $item->id,
                'label' => $item->label,
                'url' => $url,
                'target' => $item->target ?: '_self',
                'is_external' => $item->target === '_blank',
                'rel' => $item->target === '_blank' ? 'noopener noreferrer' : null,
                'type' => $item->type,
            ];
        }

        return $links;
    }

    public function url(NavigationItem $item): ?string
    {
        if ($item->type === 'custom') {
            return (string) ($item->url ?? '');
        }

        if ($item->type === 'contact') {
            return route('website.contact', [], false);
        }

        if ($item->type === 'system') {
            $systemMap = [
                'home' => route('home', [], false),
                'about' => route('website.about', [], false),
                'services' => route('website.services', [], false),
                'portfolio' => route('website.projects', [], false),
                'blog' => route('website.blog', [], false),
                'contact' => route('website.contact', [], false),
            ];

            $key = trim((string) ($item->url ?? ''));

            return $systemMap[$key] ?? ($item->url ? (string) $item->url : null);
        }

        if (! isset($item->target_id) || $item->target_id === null) {
            return null;
        }

        $resourceMap = [
            'page' => [Page::class, 'pages'],
            'service' => [Service::class, 'services'],
            'project' => [Project::class, 'projects'],
            'blog' => [BlogPost::class, 'blog'],
        ];

        if (! array_key_exists($item->type, $resourceMap)) {
            return null;
        }

        [$modelClass, $resource] = $resourceMap[$item->type];

        $query = $modelClass::query()->whereKey($item->target_id)->where('status', 'published');

        if ($item->type === 'service') {
            $query->where('is_active', true);
        } else {
            $query->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
        }

        $model = $query->first();

        return $model ? app(Seo::class)->contentUrl($resource, $model->slug) : null;
    }
}
