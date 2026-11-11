<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Routing\Route;

it('marks only the route-owned submenu link as current', function (string $routeName, string $submenu, ?string $label) {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $route = (new Route('GET', '/sidebar-test', fn () => null))->name($routeName);
    request()->setRouteResolver(fn () => $route);

    $html = view('components.admin.sidebar')->render();
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
    $xpath = new DOMXPath($dom);
    $links = $xpath->query('//*[@id="sidebar-'.$submenu.'-submenu"]//a[@aria-current="page"]');

    expect($links->length)->toBe($label === null ? 0 : 1);
    if ($label !== null) {
        expect(trim($links->item(0)->textContent))->toBe($label);
    }
})->with([
    ['admin.appearance.themes', 'appearance', 'Themes'],
    ['admin.appearance.widgets', 'appearance', null],
    ['admin.appearance.theme-options', 'appearance', 'Theme Options'],
    ['admin.appearance.robots-txt', 'appearance', 'Robots.txt Editor'],
    ['admin.appearance.themes.edit', 'appearance', 'Themes'],
    ['admin.appearance.themes-extra', 'appearance', null],
    ['admin.dashboard', 'appearance', null],
    ['admin.blog.create', 'blog', 'Posts'],
    ['admin.blog.edit', 'blog', 'Posts'],
    ['admin.blog.show', 'blog', 'Posts'],
    ['admin.blog.categories.edit', 'blog', 'Categories'],
    ['admin.blog.tags.create', 'blog', 'Tags'],
    ['admin.blog.reports.index', 'blog', 'Reports'],
    ['admin.plugins.create', 'plugins', 'Add New Plugin'],
    ['admin.plugins.edit', 'plugins', 'Installed Plugins'],
    ['admin.tools.data', 'tools', 'Export/Import Data'],
]);
