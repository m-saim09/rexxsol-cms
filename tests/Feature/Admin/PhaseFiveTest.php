<?php

use App\Enums\UserRole;
use App\Livewire\Admin\NavigationManager;
use App\Livewire\Admin\SettingsEditor;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\User;
use App\Support\NavigationService;
use App\Support\PublicSiteSettings;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

function phaseFiveUser(UserRole $role = UserRole::SuperAdmin): User
{
    return User::factory()->create(['role' => $role]);
}

it('protects admin navigation and pages screens and permits the correct CMS roles', function () {
    foreach (['admin.pages.index', 'admin.navigation'] as $route) {
        $this->get(route($route))->assertRedirect(route('login'));
    }

    $this->actingAs(phaseFiveUser(UserRole::Editor));
    $this->get(route('admin.pages.index'))->assertOk();
    $this->get(route('admin.navigation'))->assertOk();
    Livewire::test(NavigationManager::class)->call('createMenu')->assertForbidden();

    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $this->get(route('admin.pages.index'))->assertOk();
    $this->get(route('admin.navigation'))->assertOk();
});

it('shows real pages data and filters by status and search in the Pages list', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    Page::create(['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'status' => 'published']);
    Page::create(['title' => 'Internal Notes', 'slug' => 'internal-notes', 'status' => 'draft']);

    $list = Livewire::test('pages::admin.content-list', ['resource' => 'pages'])
        ->set('search', 'privacy')
        ->assertHasNoErrors();

    expect($list->get('search'))->toBe('privacy')
        ->and($list->html())->toContain('Privacy Policy');

    Livewire::test('pages::admin.content-list', ['resource' => 'pages'])
        ->set('status', 'draft')
        ->assertHasNoErrors();

    expect(Page::query()->where('status', 'draft')->count())->toBe(1);
});

it('manages safe navigation references, nesting, and ordering', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $page = Page::where('slug', 'about')->firstOrFail();

    $component = Livewire::test(NavigationManager::class)
        ->set(['label' => 'About', 'type' => 'page', 'targetId' => (string) $page->id])
        ->call('save')
        ->assertHasNoErrors();
    expect(NavigationItem::query()->where('menu', 'primary')->where('label', 'About')->exists())->toBeFalse();
    $component->call('saveMenu')->assertHasNoErrors();

    $parent = NavigationItem::firstOrFail();
    Livewire::test(NavigationManager::class)->set(['label' => 'Unsafe', 'type' => 'custom', 'url' => 'javascript:alert(1)'])->call('save')->assertHasErrors(['url']);
    Livewire::test(NavigationManager::class)->set(['label' => 'Protocol relative', 'type' => 'custom', 'url' => '//example.org/path'])->call('save')->assertHasErrors(['url']);
    Livewire::test(NavigationManager::class)
        ->set(['label' => 'External', 'type' => 'custom', 'url' => 'https://example.org'])
        ->call('save')
        ->call('saveMenu')
        ->assertHasNoErrors();

    expect(app(NavigationService::class)->menus()['primary'][0]['url'])->toContain('/about');
    expect(Cache::has(NavigationService::CACHE_KEY))->toBeTrue();
    expect($parent->fresh()->children)->toBeEmpty();
});

it('switches correctly between primary and footer menu tabs', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $page = Page::where('slug', 'contact')->firstOrFail();

    Livewire::test(NavigationManager::class)
        ->set(['menu' => 'primary', 'label' => 'Main Contact', 'type' => 'page', 'targetId' => (string) $page->id])
        ->call('save')
        ->call('saveMenu')
        ->call('selectMenu', 'footer')
        ->assertSet('menu', 'footer')
        ->assertSee('Footer menu items');

    expect(NavigationItem::where('menu', 'footer')->count())->toBe(0);
});

it('renders the redesigned menu workspace and opens inline item editors', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $item = NavigationItem::create([
        'menu' => 'primary',
        'label' => 'Editor test link',
        'type' => 'custom',
        'url' => '/editor-test',
        'enabled' => true,
        'sort_order' => 1,
    ]);

    Livewire::test(NavigationManager::class)
        ->assertSee('Manage your website navigation and footer links.')
        ->assertSee('Add menu items')
        ->assertSee('Menu structure')
        ->assertSee('Published content')
        ->assertSee('Custom link')
        ->call('startCustomLink')
        ->assertSet('type', 'custom')
        ->assertSee('id="primary-nav-url"', false)
        ->call('edit', $item->id)
        ->assertSee('id="navigation-item-' . $item->id . '-label"', false)
        ->assertSee('id="navigation-item-' . $item->id . '-url"', false);

    expect($item->fresh()->url)->toBe('/editor-test');
});

it('creates custom menus and saves theme menu-location assignments', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));

    Livewire::test(NavigationManager::class)
        ->set('newMenuName', 'Legal Menu')
        ->call('createMenu')
        ->assertSet('menu', 'legal-menu');

    expect(app(\App\Support\NavigationService::class)->menus())->toHaveKey('legal-menu');

    Livewire::test(NavigationManager::class)
        ->set('locationAssignments', [
            'primary' => 'legal-menu',
            'footer_company' => 'legal-menu',
        ])
        ->call('saveLocations')
        ->assertHasNoErrors();

    NavigationItem::create([
        'menu' => 'legal-menu',
        'label' => 'Custom assigned home',
        'type' => 'custom',
        'url' => '/assigned-home',
        'enabled' => true,
        'sort_order' => 1,
    ]);
    expect(app(NavigationService::class)->forLocation('primary', app(\App\Support\ThemeRegistry::class)->active()['slug']))
        ->toHaveCount(1)
        ->and(app(NavigationService::class)->forLocation('primary', app(\App\Support\ThemeRegistry::class)->active()['slug'])[0]['url'])
        ->toBe('/assigned-home');

    expect(app(\App\Support\SiteSettings::class)->get('theme.menu_locations'))->toMatchArray([
        app(\App\Support\ThemeRegistry::class)->active()['slug'] => [
            'primary' => 'legal-menu',
            'footer_company' => 'legal-menu',
        ],
    ]);
});

it('renames and deletes custom menus without deleting linked CMS content', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $page = Page::where('slug', 'about')->firstOrFail();

    $manager = Livewire::test(NavigationManager::class)
        ->set('newMenuName', 'Campaign Menu')
        ->call('createMenu')
        ->set('renameMenuName', 'Campaign Navigation')
        ->call('renameMenu')
        ->assertSet('menu', 'campaign-navigation')
        ->assertHasNoErrors();

    $item = NavigationItem::create([
        'menu' => 'campaign-navigation',
        'label' => 'About page',
        'type' => 'page',
        'target_type' => Page::class,
        'target_id' => $page->id,
        'enabled' => true,
        'sort_order' => 1,
    ]);

    $manager->call('deleteMenu', 'campaign-navigation')->assertSet('menu', 'primary');

    expect(NavigationItem::query()->where('menu', 'campaign-navigation')->exists())->toBeFalse()
        ->and(NavigationItem::query()->whereKey($item->id)->exists())->toBeFalse()
        ->and(Page::query()->whereKey($page->id)->exists())->toBeTrue();
});

it('keeps menu ordering changes within the selected menu', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $first = NavigationItem::create([
        'menu' => 'primary',
        'label' => 'First',
        'type' => 'custom',
        'url' => '/first',
        'enabled' => true,
        'sort_order' => 1,
    ]);
    $second = NavigationItem::create([
        'menu' => 'primary',
        'label' => 'Second',
        'type' => 'custom',
        'url' => '/second',
        'enabled' => true,
        'sort_order' => 2,
    ]);
    $otherMenuItem = NavigationItem::create([
        'menu' => 'legal-menu',
        'label' => 'Legal',
        'type' => 'custom',
        'url' => '/legal',
        'enabled' => true,
        'sort_order' => 1,
    ]);

    Livewire::test(NavigationManager::class)
        ->call('reorderItems', [$second->id, $first->id, $otherMenuItem->id])
        ->assertHasErrors('menuItems');

    expect($first->fresh()->sort_order)->toBe(1)
        ->and($second->fresh()->sort_order)->toBe(2)
        ->and($otherMenuItem->fresh()->sort_order)->toBe(1);

    $builder = Livewire::test(NavigationManager::class)
        ->call('reorderItems', [$second->id, $first->id])
        ->assertHasNoErrors();

    expect($first->fresh()->sort_order)->toBe(1)
        ->and($second->fresh()->sort_order)->toBe(2);

    $builder->call('saveMenu')->assertHasNoErrors();
    expect($second->fresh()->sort_order)->toBe(1)
        ->and($first->fresh()->sort_order)->toBe(2);
});

it('stages item edits additions removals and ordering until one atomic save', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $first = NavigationItem::create([
        'menu' => 'primary',
        'label' => 'Keep and edit',
        'type' => 'custom',
        'url' => '/before',
        'enabled' => true,
        'sort_order' => 1,
    ]);
    $removed = NavigationItem::create([
        'menu' => 'primary',
        'label' => 'Remove later',
        'type' => 'custom',
        'url' => '/remove',
        'enabled' => true,
        'sort_order' => 2,
    ]);

    $builder = Livewire::test(NavigationManager::class)
        ->call('edit', $first->id)
        ->set('label', 'Edited label')
        ->set('url', '/after')
        ->call('save')
        ->set('label', 'New item')
        ->set('type', 'custom')
        ->set('url', '/new')
        ->call('save');
    $newId = collect($builder->get('menuDraft'))->pluck('id')->filter(fn ($id) => (int) $id < 0)->first();
    $builder
        ->call('remove', $removed->id)
        ->call('reorderItems', [$newId, $first->id])
        ->assertHasNoErrors();

    expect($first->fresh()->label)->toBe('Keep and edit')
        ->and($first->fresh()->url)->toBe('/before')
        ->and($removed->fresh())->not->toBeNull()
        ->and(NavigationItem::query()->where('menu', 'primary')->where('label', 'New item')->exists())->toBeFalse();

    $builder->call('saveMenu')->assertHasNoErrors();
    expect($first->fresh()->label)->toBe('Edited label')
        ->and($first->fresh()->url)->toBe('/after')
        ->and($removed->fresh())->toBeNull()
        ->and(NavigationItem::query()->where('menu', 'primary')->where('label', 'New item')->value('sort_order'))->toBe(1)
        ->and($first->fresh()->sort_order)->toBe(2);
});

it('rejects stale menu item edits without overwriting the newer value', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $item = NavigationItem::create([
        'menu' => 'primary',
        'label' => 'Original label',
        'type' => 'custom',
        'url' => '/original',
        'enabled' => true,
        'sort_order' => 1,
    ]);

    $editor = Livewire::test(NavigationManager::class)
        ->call('edit', $item->id)
        ->set('label', 'Stale edit')
        ->call('save')
        ->assertHasNoErrors();
    NavigationItem::query()->whereKey($item->id)->update([
        'label' => 'Updated by another admin',
        'updated_at' => now()->addMinute(),
    ]);

    $editor->call('saveMenu')->assertHasErrors('menuItems');
    expect($item->fresh()->label)->toBe('Updated by another admin');
});

it('supports keyboard-accessible one-level nesting and prevents deeper nesting', function () {
    $this->actingAs(phaseFiveUser(UserRole::Admin));
    $first = NavigationItem::create([
        'menu' => 'primary',
        'label' => 'Parent',
        'type' => 'custom',
        'url' => '/parent',
        'enabled' => true,
        'sort_order' => 1,
    ]);
    $second = NavigationItem::create([
        'menu' => 'primary',
        'label' => 'Child',
        'type' => 'custom',
        'url' => '/child',
        'enabled' => true,
        'sort_order' => 2,
    ]);

    $builder = Livewire::test(NavigationManager::class)
        ->call('indent', $second->id)
        ->assertHasNoErrors();
    expect($second->fresh()->parent_id)->toBeNull();
    $builder->call('saveMenu')->assertHasNoErrors();
    expect($second->fresh()->parent_id)->toBe($first->id);

    $builder
        ->call('outdent', $second->id)
        ->assertHasNoErrors();
    $builder->call('saveMenu')->assertHasNoErrors();
    expect($second->fresh()->parent_id)->toBeNull();
});

it('renders BizConsult desktop and mobile location assignments separately', function () {
    \App\Models\NavigationItem::create([
        'menu' => 'mobile-menu-check',
        'label' => 'Mobile-only link',
        'type' => 'custom',
        'url' => '/mobile-only',
        'enabled' => true,
        'sort_order' => 1,
    ]);
    app(\App\Support\SiteSettings::class)->put([
        'theme.menu_locations' => [
            'bizconsult' => [
                'primary' => 'primary',
                'mobile' => 'mobile-menu-check',
            ],
        ],
    ]);
    $page = Page::create([
        'title' => 'Mobile destination',
        'slug' => 'mobile-destination',
        'status' => 'published',
        'show_in_navbar' => true,
        'show_in_footer' => false,
    ]);
    expect(NavigationItem::query()
        ->where('menu', 'mobile-menu-check')
        ->where('target_id', $page->id)
        ->where('is_automatic', true)
        ->exists())->toBeTrue();

    $html = view('themes.bizconsult.partials.header', [
        'settings' => app(PublicSiteSettings::class)->all(),
        'previewTheme' => 'bizconsult',
    ])->render();

    expect($html)->toContain('Mobile-only link')
        ->and($html)->toContain('d-flex d-lg-none')
        ->and($html)->toContain('d-none d-lg-flex');
});

it('saves header and footer settings through the existing settings service', function () {
    $this->actingAs(phaseFiveUser());
    Livewire::test(SettingsEditor::class)
        ->set('values.header_cta_label', 'Start a project')
        ->set('values.header_cta_url', 'https://example.org/contact')
        ->set('values.footer_description', 'Rexxsol builds digital products.')
        ->call('save')
        ->assertHasNoErrors();

    expect(app(PublicSiteSettings::class)->all())
        ->toHaveKey('header.cta_label', 'Start a project')
        ->toHaveKey('footer.company_description', 'Rexxsol builds digital products.');
});
