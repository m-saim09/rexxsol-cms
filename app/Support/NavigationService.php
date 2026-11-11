<?php

namespace App\Support;

use App\Models\NavigationItem;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

final class NavigationService
{
    public const CACHE_KEY = 'cms.public-navigation.v1';

    public function menus(): array
    {
        return Cache::remember(self::CACHE_KEY, $this->cacheTtl(), function (): array {
            $items = NavigationItem::query()->where('enabled', true)->with('children')->orderBy('sort_order')->orderBy('id')->get();
            $menuNames = NavigationItem::allMenuKeys();
            $menus = collect($menuNames)
                ->mapWithKeys(fn (string $menu): array => [$menu => $this->tree($items->where('menu', $menu)->whereNull('parent_id'))])
                ->all();

            if (! NavigationItem::query()->where('menu', 'primary')->where('is_automatic', false)->exists()) {
                $primaryUrls = collect($menus['primary'] ?? [])->pluck('url')->all();
                $menus['primary'] = array_merge(
                    collect($this->systemPageLinks())
                        ->reject(fn (array $item): bool => in_array($item['url'], $primaryUrls, true))
                        ->all(),
                    $menus['primary'] ?? [],
                );
            }

            return $menus;
        });
    }

    public function forLocation(string $location, ?string $themeSlug = null): array
    {
        if (str_contains($location, 'footer')) {
            $footerItems = app(FooterService::class)->forLocation($location, $themeSlug);
            if (! empty($footerItems)) {
                return $footerItems;
            }
        }

        $menu = $this->locationMenu($location, $themeSlug);
        if ($menu === null && $location === 'primary') {
            $menu = 'primary';
        }

        return $menu ? ($this->menus()[$menu] ?? []) : [];
    }

    public function locationMenu(string $location, ?string $themeSlug = null): ?string
    {
        $settings = app(\App\Support\SiteSettings::class)->get('theme.menu_locations', []);
        $theme = $themeSlug ?? app(\App\Support\ThemeRegistry::class)->active()['slug'];

        if (! is_array($settings)) {
            return null;
        }

        $themeAssignments = $settings[$theme] ?? $settings;

        if (! is_array($themeAssignments)) {
            return null;
        }

        return isset($themeAssignments[$location]) && is_string($themeAssignments[$location])
            ? $themeAssignments[$location]
            : null;
    }

    public function cacheTtl(): int
    {
        $nextPublication = Page::query()
            ->where('status', 'published')
            ->where('published_at', '>', now())
            ->min('published_at');

        if (! $nextPublication) {
            return 3600;
        }

        return max(1, min(3600, now()->diffInSeconds($nextPublication)));
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        FooterService::forget();
    }

    private function tree($items): array
    {
        return $items->map(fn (NavigationItem $item): ?array => ($url = $this->url($item)) === null ? null : [
            'label' => $item->label,
            'url' => $url,
            'target' => $item->target,
            'children' => $this->tree($item->children->where('enabled', true)),
        ])->filter()->values()->all();
    }

    private function systemPageLinks(): array
    {
        $labels = [
            'system-home' => 'Home',
            'system-about' => 'About',
            'system-services' => 'Services',
            'system-projects' => 'Projects',
            'system-blog' => 'Insights',
            'system-contact' => 'Contact',
        ];
        $order = array_flip(array_keys($labels));

        return Page::query()
            ->where('is_system', true)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->get()
            ->sortBy(fn (Page $page): int => $order[$page->template] ?? count($order))
            ->map(fn (Page $page): array => [
                'label' => trim((string) $page->navigation_label) ?: ($labels[$page->template] ?? $page->title),
                'url' => $page->publicUrl(),
                'target' => '_self',
                'children' => [],
            ])
            ->values()
            ->all();
    }

    private function url(NavigationItem $item): ?string
    {
        if ($item->type === 'custom') {
            return (string) ($item->url ?? '');
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

        $resourceMap = ['page' => 'pages', 'service' => 'services', 'project' => 'projects', 'blog' => 'blog'];

        if (! array_key_exists($item->type, $resourceMap)) {
            return null;
        }

        $resource = $resourceMap[$item->type];
        $query = CmsContent::model($resource)::query()->whereKey($item->target_id)->where('status', 'published');
        if ($resource !== 'services') {
            $query->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
        }
        if ($resource === 'services') {
            $query->where('is_active', true);
        }
        $model = $query->first();

        return $model ? app(Seo::class)->contentUrl($resource, $model->slug) : null;
    }
}
