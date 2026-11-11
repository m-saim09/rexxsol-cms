<?php

namespace App\Support;

use App\Models\NavigationItem;
use App\Models\Page;

final class PageNavigationSync
{
    public function sync(Page $page, bool $applyOrder = false): void
    {
        if ($page->is_system) {
            return;
        }

        $published = ! $page->trashed() && $page->status === 'published';
        $this->syncLocation($page, 'primary', $published && (bool) $page->show_in_navbar, $applyOrder);
        $this->syncLocation($page, 'footer', $published && (bool) $page->show_in_footer, $applyOrder);

        $assignments = app(SiteSettings::class)->get('theme.menu_locations', []);
        if (! is_array($assignments)) {
            return;
        }

        $menus = [];
        foreach (array_keys(app(ThemeRegistry::class)->all()) as $theme) {
            $themeAssignments = $assignments[$theme] ?? [];
            if (! is_array($themeAssignments)) {
                continue;
            }

            foreach ($themeAssignments as $location => $menu) {
                $isPrimary = in_array($location, ['primary', 'mobile'], true);
                $isFooter = str_starts_with((string) $location, 'footer') && ! str_contains((string) $location, 'legal');
                if (! is_string($menu) || $menu === '' || str_starts_with($menu, 'column:') || (! $isPrimary && ! $isFooter)) {
                    continue;
                }

                $visible = $isPrimary ? (bool) $page->show_in_navbar : (bool) $page->show_in_footer;
                if ($menu !== ($isPrimary ? 'primary' : 'footer')) {
                    $menus[$menu] = ($menus[$menu] ?? false) || $visible;
                }
            }
        }

        foreach ($menus as $menu => $visible) {
            $this->syncLocation($page, $menu, $published && $visible, $applyOrder);
        }
    }

    private function syncLocation(Page $page, string $menu, bool $visible, bool $applyOrder): void
    {
        $automaticItems = NavigationItem::query()
            ->where('menu', $menu)
            ->where('type', 'page')
            ->where('target_id', $page->id)
            ->where('is_automatic', true)
            ->orderBy('id')
            ->get();

        if (! $visible) {
            $automaticItems->each(fn (NavigationItem $item) => $item->updateQuietly(['enabled' => false]));
            NavigationService::forget();

            return;
        }

        $pagePath = '/'.trim($page->slug, '/');
        $hasManualLink = NavigationItem::query()
            ->where('menu', $menu)
            ->where('enabled', true)
            ->where('is_automatic', false)
            ->whereIn('type', ['custom', 'system', 'contact'])
            ->get()
            ->contains(function (NavigationItem $candidate) use ($pagePath): bool {
                $url = $candidate->type === 'system'
                    ? match (trim((string) $candidate->url, '/')) {
                        'home' => '/',
                        'about' => '/about',
                        'services' => '/services',
                        'portfolio', 'projects' => '/projects',
                        'blog' => '/blog',
                        'contact' => '/contact',
                        default => (string) $candidate->url,
                    }
                    : (string) ($candidate->type === 'contact' ? route('website.contact', [], false) : $candidate->url);
                $candidatePath = '/'.trim((string) parse_url($url, PHP_URL_PATH), '/');

                return $candidatePath === $pagePath || ($pagePath === '/projects' && $candidatePath === '/portfolio');
            });

        if ($hasManualLink) {
            $automaticItems->each->deleteQuietly();
            NavigationService::forget();

            return;
        }

        if ($automaticItems->isEmpty() && NavigationItem::query()
            ->where('menu', $menu)
            ->where('type', 'page')
            ->where('target_id', $page->id)
            ->exists()) {
            return;
        }

        $item = $automaticItems->first() ?? new NavigationItem;
        $automaticLabel = trim((string) $page->navigation_label) ?: $page->title;
        $labelWasCustomized = $item->exists
            && $item->automatic_label !== null
            && $item->label !== $item->automatic_label;
        $appendOrder = (int) NavigationItem::query()->where('menu', $menu)->when($item->exists, fn ($query) => $query->whereKeyNot($item->id))->max('sort_order') + 1;
        $sortOrder = $item->exists && ! $applyOrder
            ? $item->sort_order
            : ($page->menu_order ?: $appendOrder);

        $item->fill([
            'menu' => $menu,
            'label' => $labelWasCustomized ? $item->label : $automaticLabel,
            'automatic_label' => $automaticLabel,
            'type' => 'page',
            'target_type' => Page::class,
            'target_id' => $page->id,
            'url' => null,
            'target' => '_self',
            'enabled' => true,
            'sort_order' => $sortOrder,
            'parent_id' => $item->exists ? $item->parent_id : null,
            'is_automatic' => true,
        ]);
        $item->saveQuietly();

        $automaticItems->skip(1)->each->deleteQuietly();
        NavigationService::forget();
    }
}