<?php

use App\Enums\UserRole;
use App\Livewire\Admin\AppearanceManager;
use App\Livewire\Admin\NavigationManager;
use App\Models\BlogPost;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use App\Support\FooterService;
use App\Support\NavigationService;
use App\Support\SiteSettings;
use App\Support\ThemeRegistry;
use Livewire\Livewire;

function footerAdminUser(UserRole $role = UserRole::SuperAdmin): User
{
    return User::factory()->create(['role' => $role]);
}

it('authorizes only authorized users to access footer navigation and settings', function () {
    $this->actingAs(footerAdminUser(UserRole::Editor));
    $this->get(route('admin.appearance.menus'))->assertOk();
    $this->get(route('admin.appearance.footer'))->assertOk();
    Livewire::test(NavigationManager::class)->assertOk()->call('createMenu')->assertForbidden();

    $this->actingAs(footerAdminUser(UserRole::Admin));
    $this->get(route('admin.appearance.menus'))->assertOk();
    $this->get(route('admin.appearance.footer'))->assertOk();
});

it('switches between primary and footer menu tabs seamlessly', function () {
    $this->actingAs(footerAdminUser());

    Livewire::test(NavigationManager::class)
        ->assertSet('menu', 'primary')
        ->call('selectMenu', 'footer')
        ->assertSet('menu', 'footer')
        ->assertSee('Footer menu items & column structure', false);
});

it('creates, renames, reorders, toggles, and safely deletes footer columns', function () {
    $this->actingAs(footerAdminUser());
    $page = Page::create(['title' => 'About Us', 'slug' => 'about-us', 'status' => 'published']);

    $component = Livewire::test(NavigationManager::class)
        ->call('selectMenu', 'footer')
        ->set('newColumnName', 'Explore')
        ->call('createColumn')
        ->call('saveMenu')
        ->assertHasNoErrors();

    $column = NavigationItem::where('menu', 'footer')->where('label', 'Explore')->first();
    expect($column)->not->toBeNull();
    expect($column->type)->toBe('column');
    expect($column->parent_id)->toBeNull();
    expect($column->enabled)->toBeTrue();

    // Create second column
    $component->set('newColumnName', 'Company')
        ->call('createColumn')
        ->call('saveMenu')
        ->assertHasNoErrors();

    $companyCol = NavigationItem::where('menu', 'footer')->where('label', 'Company')->first();
    expect($companyCol)->not->toBeNull();

    // Rename column
    $component->call('startEditColumn', $column->id)
        ->set('editingColumnName', 'Discover')
        ->call('updateColumn')
        ->call('saveMenu')
        ->assertHasNoErrors();

    expect($column->fresh()->label)->toBe('Discover');

    // Toggle column visibility
    $component->call('toggleColumn', $column->id);
    $component->call('saveMenu');
    expect($column->fresh()->enabled)->toBeFalse();
    $component->call('toggleColumn', $column->id);
    $component->call('saveMenu');
    expect($column->fresh()->enabled)->toBeTrue();

    // Reorder columns
    $component->call('moveColumn', $companyCol->id, 'up');
    $component->call('saveMenu');
    expect($companyCol->fresh()->sort_order)->toBeLessThan($column->fresh()->sort_order);

    // Add a link into Discover column
    $component->set('linkColumnId', $column->id)
        ->set('label', 'About')
        ->set('type', 'page')
        ->set('targetId', (string) $page->id)
        ->call('saveLink')
        ->call('saveMenu')
        ->assertHasNoErrors();

    $link = NavigationItem::where('menu', 'footer')->where('label', 'About')->first();
    expect($link)->not->toBeNull();
    expect($link->parent_id)->toBe($column->id);

    // Delete column with reassignment
    $component->set('deletingColumnId', $column->id)
        ->set('reassignColumnId', $companyCol->id)
        ->call('deleteColumn')
        ->call('saveMenu');

    expect(NavigationItem::find($column->id))->toBeNull();
    expect($link->fresh()->parent_id)->toBe($companyCol->id);

    // Verify original CMS page is untouched
    expect(Page::find($page->id))->not->toBeNull();
});

it('adds, edits, moves, and manages footer links across types', function () {
    $this->actingAs(footerAdminUser());

    $column = NavigationItem::create([
        'menu' => 'footer',
        'label' => 'Resources',
        'type' => 'column',
        'enabled' => true,
        'sort_order' => 1,
    ]);

    $service = Service::create([
        'name' => 'Custom Web Development',
        'slug' => 'custom-web-development',
        'status' => 'published',
        'is_active' => true,
        'description' => 'Service description',
    ]);

    $component = Livewire::test(NavigationManager::class)
        ->call('selectMenu', 'footer');

    // 1. Add Service link
    $component->set('linkColumnId', $column->id)
        ->set('label', 'Web Dev')
        ->set('type', 'service')
        ->set('targetId', (string) $service->id)
        ->call('saveLink')
        ->call('saveMenu')
        ->assertHasNoErrors();

    $serviceLink = NavigationItem::where('menu', 'footer')->where('label', 'Web Dev')->first();
    expect($serviceLink)->not->toBeNull();
    expect($serviceLink->target_id)->toBe($service->id);

    // 2. Add System link
    $component->set('linkColumnId', $column->id)
        ->set('label', 'Contact Us')
        ->set('type', 'system')
        ->set('url', 'contact')
        ->call('saveLink')
        ->assertHasNoErrors();

    // 3. Add Custom URL with New Tab
    $component->set('linkColumnId', $column->id)
        ->set('label', 'Rexxsol Docs')
        ->set('type', 'custom')
        ->set('url', 'https://docs.rexxsol.com')
        ->set('target', '_blank')
        ->call('saveLink')
        ->call('saveMenu')
        ->assertHasNoErrors();

    $customLink = NavigationItem::where('menu', 'footer')->where('label', 'Rexxsol Docs')->first();
    expect($customLink->target)->toBe('_blank');

    // Edit link
    $component->call('editLink', $serviceLink->id)
        ->assertSet('label', 'Web Dev')
        ->set('label', 'Web & App Development')
        ->call('saveLink')
        ->call('saveMenu')
        ->assertHasNoErrors();

    expect($serviceLink->fresh()->label)->toBe('Web & App Development');

    // Remove link
    $component->call('removeLink', $customLink->id);
    $component->call('saveMenu');
    expect(NavigationItem::find($customLink->id))->toBeNull();

    // Removing link does NOT delete the service
    expect(Service::find($service->id))->not->toBeNull();
});

it('validates complete footer link reorder payloads against their owning column', function () {
    $this->actingAs(footerAdminUser(UserRole::Admin));
    $column = NavigationItem::create(['menu' => 'footer', 'label' => 'Resources', 'type' => 'column', 'enabled' => true, 'sort_order' => 1]);
    $otherColumn = NavigationItem::create(['menu' => 'footer', 'label' => 'Company', 'type' => 'column', 'enabled' => true, 'sort_order' => 2]);
    $first = NavigationItem::create(['menu' => 'footer', 'parent_id' => $column->id, 'label' => 'First', 'type' => 'custom', 'url' => '/first', 'enabled' => true, 'sort_order' => 1]);
    $second = NavigationItem::create(['menu' => 'footer', 'parent_id' => $column->id, 'label' => 'Second', 'type' => 'custom', 'url' => '/second', 'enabled' => true, 'sort_order' => 2]);
    $foreign = NavigationItem::create(['menu' => 'footer', 'parent_id' => $otherColumn->id, 'label' => 'Foreign', 'type' => 'custom', 'url' => '/foreign', 'enabled' => true, 'sort_order' => 1]);

    $component = Livewire::test(NavigationManager::class)->call('selectMenu', 'footer');
    $component->call('reorderLinks', $column->id, [$second->id, $first->id])->assertHasNoErrors()
        ->call('saveMenu')->assertHasNoErrors();
    expect($second->fresh()->sort_order)->toBe(1)
        ->and($first->fresh()->sort_order)->toBe(2);

    $component->call('reorderLinks', $column->id, [$first->id, $foreign->id])->assertHasErrors('footerLinks');
    expect($second->fresh()->sort_order)->toBe(1)
        ->and($first->fresh()->sort_order)->toBe(2)
        ->and($foreign->fresh()->sort_order)->toBe(1);
});

it('detects broken footer links when CMS content is unpublished or deleted', function () {
    $this->actingAs(footerAdminUser());

    $column = NavigationItem::create([
        'menu' => 'footer',
        'label' => 'Explore',
        'type' => 'column',
        'enabled' => true,
        'sort_order' => 1,
    ]);

    $page = Page::create(['title' => 'Careers', 'slug' => 'careers', 'status' => 'published']);

    $link = NavigationItem::create([
        'menu' => 'footer',
        'parent_id' => $column->id,
        'label' => 'Careers',
        'type' => 'page',
        'target_id' => $page->id,
        'target_type' => Page::class,
        'enabled' => true,
        'sort_order' => 1,
    ]);

    expect($link->isTargetUnavailable())->toBeFalse();

    // Unpublish the page
    $page->update(['status' => 'draft']);
    expect($link->fresh()->isTargetUnavailable())->toBeTrue();

    // Broken links are omitted from public footer
    $footerData = app(FooterService::class)->data();
    $columnLinks = collect($footerData['columns'])->firstWhere('id', $column->id)['links'] ?? [];
    expect(collect($columnLinks)->pluck('label'))->not->toContain('Careers');
});

it('saves non-menu global footer settings into site settings without duplication', function () {
    $this->actingAs(footerAdminUser());

    Livewire::test(NavigationManager::class)
        ->call('selectMenu', 'footer')
        ->call('setFooterSubTab', 'settings')
        ->set('siteName', 'Rexxsol Technologies')
        ->set('footerDescription', 'Practical software engineering and digital advisory.')
        ->set('contactEmail', 'contact@rexxsol.com')
        ->set('contactPhone', '+1 800 555 1234')
        ->set('socialLinkedin', 'https://linkedin.com/company/rexxsol')
        ->set('ctaHeading', 'Build something meaningful.')
        ->call('saveFooterSettings')
        ->assertHasNoErrors();

    $settings = app(SiteSettings::class);
    expect($settings->get('site.name'))->toBe('Rexxsol Technologies');
    expect($settings->get('footer.company_description'))->toBe('Practical software engineering and digital advisory.');
    expect($settings->get('contact.business_email'))->toBe('contact@rexxsol.com');
    expect($settings->get('contact.phone'))->toBe('+1 800 555 1234');
    expect($settings->get('social.linkedin'))->toBe('https://linkedin.com/company/rexxsol');
    expect($settings->get('theme.copyright'))->toBeNull();
    expect($settings->get('footer.cta_heading'))->toBe('Build something meaningful.');
});

it('preserves footer configuration and adapts when switching themes between Rexxsol Corporate and BizConsult', function () {
    $this->actingAs(footerAdminUser());

    // 1. Setup footer columns and links
    $col1 = NavigationItem::create(['menu' => 'footer', 'label' => 'Services', 'type' => 'column', 'enabled' => true, 'sort_order' => 1]);
    $col2 = NavigationItem::create(['menu' => 'footer', 'label' => 'Company', 'type' => 'column', 'enabled' => true, 'sort_order' => 2]);
    $col3 = NavigationItem::create(['menu' => 'footer', 'label' => 'Legal', 'type' => 'column', 'enabled' => true, 'sort_order' => 3]);

    NavigationItem::create(['menu' => 'footer', 'parent_id' => $col1->id, 'label' => 'Web Apps', 'type' => 'custom', 'url' => '/services/web', 'enabled' => true, 'sort_order' => 1]);
    NavigationItem::create(['menu' => 'footer', 'parent_id' => $col2->id, 'label' => 'About Rexxsol', 'type' => 'custom', 'url' => '/about', 'enabled' => true, 'sort_order' => 1]);
    NavigationItem::create(['menu' => 'footer', 'parent_id' => $col3->id, 'label' => 'Privacy Policy', 'type' => 'custom', 'url' => '/privacy-policy', 'enabled' => true, 'sort_order' => 1]);

    // 2. Active theme is Rexxsol Corporate
    app(SiteSettings::class)->put(['appearance.active_theme' => 'rexxsol-corporate']);
    FooterService::forget();

    $corpData = app(FooterService::class)->data();
    expect($corpData['capabilities']['columns'])->toBe(2);
    expect(count($corpData['columns']))->toBe(3);
    expect(count($corpData['legal']))->toBe(1);
    expect($corpData['legal'][0]['label'])->toBe('Privacy Policy');

    // 3. Switch to BizConsult
    app(SiteSettings::class)->put(['appearance.active_theme' => 'bizconsult']);
    FooterService::forget();

    $bizData = app(FooterService::class)->data();
    expect($bizData['capabilities']['columns'])->toBe(3);
    expect($bizData['capabilities']['supports_cta'])->toBeTrue();
    // All 3 columns and links survive!
    expect(count($bizData['columns']))->toBe(3);
    expect(count($bizData['legal']))->toBe(1);
    expect($bizData['columns'][0]['heading'])->toBe('Services');
    expect($bizData['columns'][1]['heading'])->toBe('Company');
    expect($bizData['columns'][2]['heading'])->toBe('Legal');

    // 4. Switch back to Rexxsol Corporate
    app(SiteSettings::class)->put(['appearance.active_theme' => 'rexxsol-corporate']);
    FooterService::forget();

    $corpDataAfter = app(FooterService::class)->data();
    expect(count($corpDataAfter['columns']))->toBe(3);
    expect($corpDataAfter['columns'][0]['heading'])->toBe('Services');
});
