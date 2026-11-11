<?php

use App\Enums\UserRole;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\FooterService;
use App\Support\NavigationService;
use App\Support\RichText;
use App\Support\ThemeRegistry;
use Livewire\Livewire;

it('publishes a page with one live page-backed navbar and footer link and restores it without duplicates', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($admin);
    NavigationItem::create(['menu' => 'primary', 'label' => 'Existing link', 'type' => 'custom', 'url' => '/services', 'enabled' => true, 'sort_order' => 4]);
    NavigationItem::create(['menu' => 'footer', 'label' => 'Contact', 'type' => 'custom', 'url' => '/contact', 'enabled' => true, 'sort_order' => 1]);

    $form = Livewire::test('pages::admin.content-form', ['resource' => 'pages'])
        ->set('title', 'Company Profile')
        ->set('slug', 'company-profile')
        ->set('excerpt', 'A profile of the company.')
        ->set('content', '<h2>Our work</h2><p>Real page content.</p><script>alert(1)</script><a href="javascript:alert(1)">Unsafe link</a>')
        ->call('publish')
        ->assertHasNoErrors();

    $page = Page::where('slug', 'company-profile')->firstOrFail();
    expect($page->status)->toBe('published')
        ->and($page->show_in_navbar)->toBeTrue()
        ->and($page->show_in_footer)->toBeTrue()
        ->and(NavigationItem::where('menu', 'primary')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->count())->toBe(1)
        ->and(NavigationItem::where('menu', 'footer')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->count())->toBe(1);

    $form->set('navigation_label', 'Company')->set('menu_order', 3)->call('saveChanges')->assertHasNoErrors();
    $this->get('/company-profile')->assertOk()->assertSee('Company Profile')->assertSee('Real page content.')->assertDontSee('alert(1)');
    $footerLinks = collect(app(FooterService::class)->data()['locations'])->flatMap(fn (array $location) => $location['links'] ?? []);
    expect($footerLinks->where('label', 'Company')->count())->toBe(1);

    $primaryLink = collect(app(NavigationService::class)->menus()['primary'])->firstWhere('label', 'Company');
    expect($primaryLink['url'])->toEndWith('/company-profile')
        ->and($primaryLink['children'])->toBe([]);
    expect(NavigationItem::where('menu', 'primary')->where('target_id', $page->id)->where('is_automatic', true)->value('sort_order'))->toBe(3);
    expect(collect(app(NavigationService::class)->menus()['primary'])->firstWhere('label', 'Existing link')['url'])->toBe('/services');

    NavigationItem::where('menu', 'primary')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->firstOrFail()->delete();
    expect($page->fresh()->show_in_navbar)->toBeFalse();
    $form->set('show_in_navbar', false)->set('excerpt', 'Updated company profile.')->call('saveChanges')->assertHasNoErrors();
    expect(NavigationItem::where('menu', 'primary')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->exists())->toBeFalse();
    expect(NavigationItem::where('menu', 'footer')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->where('enabled', true)->exists())->toBeTrue();
    $form->set('show_in_footer', false)->call('saveChanges')->assertHasNoErrors();
    expect(NavigationItem::where('menu', 'footer')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->where('enabled', true)->exists())->toBeFalse();
    $form->set('show_in_navbar', true)->set('show_in_footer', true)->call('publish')->assertHasNoErrors();
    expect(NavigationItem::where('menu', 'primary')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->count())->toBe(1);

    $form->set('slug', 'company-profile-updated')->call('saveChanges')->assertHasNoErrors();
    $updatedLink = collect(app(NavigationService::class)->menus()['primary'])->firstWhere('label', 'Company');
    expect($updatedLink['url'])->toEndWith('/company-profile-updated');
    $this->get('/company-profile')->assertRedirect('/company-profile-updated');

    $form->set('slug', 'company-profile-final')->call('saveChanges')->assertHasNoErrors();
    $this->get('/company-profile')->assertRedirect('/company-profile-final');
    $this->get('/company-profile-updated')->assertRedirect('/company-profile-final');

    $form->set('status', 'draft')->call('saveChanges')->assertHasNoErrors();
    $this->get('/company-profile-final')->assertNotFound();
    expect(collect(app(NavigationService::class)->menus()['primary'])->firstWhere('label', 'Company'))->toBeNull();

    $form->set('status', 'published')->call('publish')->assertHasNoErrors();
    $this->get('/company-profile-final')->assertOk();
    $this->get('/company-profile')->assertRedirect('/company-profile-final');
    expect(NavigationItem::where('menu', 'primary')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->count())->toBe(1)
        ->and(NavigationItem::where('menu', 'footer')->where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->count())->toBe(1);

    $page->delete();
    $this->get('/company-profile-final')->assertNotFound();
    expect(NavigationItem::where('type', 'page')->where('target_id', $page->id)->where('is_automatic', true)->where('enabled', true)->exists())->toBeFalse();
});

it('keeps drafts, future pages, and soft-deleted pages out of public routes and menus', function () {
    $draft = Page::create(['title' => 'Draft page', 'slug' => 'draft-page', 'status' => 'draft']);
    $future = Page::create(['title' => 'Future page', 'slug' => 'future-page', 'status' => 'published', 'published_at' => now()->addDay()]);
    $deleted = Page::create(['title' => 'Deleted page', 'slug' => 'deleted-page', 'status' => 'published']);
    $deleted->delete();

    $this->get('/draft-page')->assertNotFound();
    $this->get('/future-page')->assertNotFound();
    $this->get('/deleted-page')->assertNotFound();
    expect($draft->show_in_navbar)->toBeTrue()
        ->and($future->show_in_footer)->toBeTrue()
        ->and(collect(app(NavigationService::class)->menus()['primary'])->pluck('label')->intersect(['Draft page', 'Future page', 'Deleted page'])->isEmpty())->toBeTrue();
});

it('registers core pages and allows only authorized previews of ordinary drafts', function () {
    foreach (['system-home', 'system-about', 'system-services', 'system-projects', 'system-blog', 'system-contact'] as $template) {
        expect(Page::query()->where('template', $template)->where('is_system', true)->count())->toBe(1);
    }

    $draft = Page::create(['title' => 'Preview page', 'slug' => 'preview-page', 'status' => 'draft', 'content' => '<p>Private draft content</p>']);
    $this->get('/preview-page')->assertNotFound();
    $this->get(route('admin.pages.preview', $draft))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.pages.preview', $draft))
        ->assertOk()
        ->assertSee('Private draft content');
});

it('synchronizes automatic page links with explicitly assigned theme menus', function () {
    $theme = app(ThemeRegistry::class)->active()['slug'];
    app(\App\Support\SiteSettings::class)->put([
        'cms.navigation.menus' => ['custom-header'],
        'theme.menu_locations' => [$theme => ['primary' => 'custom-header']],
    ]);

    $page = Page::create([
        'title' => 'Assigned destination',
        'slug' => 'assigned-destination',
        'status' => 'published',
        'show_in_navbar' => true,
        'show_in_footer' => false,
    ]);
    $automaticItem = NavigationItem::query()
        ->where('menu', 'custom-header')
        ->where('target_id', $page->id)
        ->where('is_automatic', true)
        ->firstOrFail();

    $assignedLink = collect(app(NavigationService::class)->forLocation('primary', $theme))
        ->firstWhere('label', 'Assigned destination');

    expect($assignedLink)->not->toBeNull()
        ->and($assignedLink['url'])->toContain('assigned-destination')
        ->and($automaticItem->label)->toBe('Assigned destination');

    $automaticItem->delete();

    expect($page->fresh()->show_in_navbar)->toBeFalse()
        ->and(NavigationItem::query()->where('menu', 'custom-header')->where('target_id', $page->id)->where('is_automatic', true)->exists())->toBeFalse();
});

it('keeps all primary links in the mobile menu and places overflow in an accessible desktop disclosure', function () {
    foreach (range(1, 6) as $position) {
        NavigationItem::create([
            'menu' => 'primary',
            'label' => "Page {$position}",
            'type' => 'custom',
            'url' => "/page-{$position}",
            'enabled' => true,
            'sort_order' => $position,
        ]);
    }

    $response = $this->get('/about')
        ->assertOk()
        ->assertSee('<details class="site-nav-group">', false)
        ->assertSee('<summary class="site-nav-link site-nav-link--button', false)
        ->assertSee('More navigation links')
        ->assertSee('Page 6');
    expect(substr_count($response->getContent(), 'href="/page-6"'))->toBe(2);
});

it('rejects page slugs that collide with top-level application routes', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));

    foreach (['admin', 'login', 'logout', 'services', 'contact'] as $slug) {
        Livewire::test('pages::admin.content-form', ['resource' => 'pages'])
            ->set('title', 'Reserved route copy')
            ->set('slug', $slug)
            ->call('publish')
            ->assertHasErrors(['slug']);
    }
});

it('resolves each active theme default page view and retains the page URL and content after theme switching', function () {
    $page = Page::create(['title' => 'Theme-aware page', 'slug' => 'theme-aware-page', 'status' => 'published', 'content' => '<p>Theme-independent content</p>', 'show_in_navbar' => true, 'show_in_footer' => true]);
    app(\App\Support\PageNavigationSync::class)->sync($page);
    $registry = app(ThemeRegistry::class);

    expect($registry->resolveTemplate('page', 'rexxsol-corporate'))->toBe('themes.rexxsol-corporate.templates.page')
        ->and($registry->resolveTemplate('page', 'bizconsult'))->toBe('themes.bizconsult.templates.page');

    $this->get('/theme-aware-page')->assertOk()->assertSee('Theme-independent content');
    SiteSetting::updateOrCreate(['key' => 'appearance.active_theme'], ['value' => 'bizconsult']);
    $response = $this->get('/theme-aware-page');
    $response->assertOk()->assertSee('theme-bizconsult')->assertSee('Theme-independent content')->assertSee('bizconsult-navbar', false)->assertSee('navbar-collapse', false)->assertSee('/theme-aware-page')->assertSee('Theme-aware page');
    $footerLinks = collect(app(FooterService::class)->data('bizconsult')['locations'])->flatMap(fn (array $location) => $location['links'] ?? []);
    expect($footerLinks->firstWhere('label', 'Theme-aware page'))->not->toBeNull();
    $footerMarkup = \Illuminate\Support\Str::after($response->getContent(), '<footer');
    $footerMarkup = \Illuminate\Support\Str::before($footerMarkup, '</footer>');
    expect($footerMarkup)->toContain('theme-aware-page', 'Theme-aware page');
    expect($page->fresh()->slug)->toBe('theme-aware-page');
});

it('sanitizes rich text tags, attributes, and dangerous links', function () {
    $safe = RichText::clean('<h2 onclick="run()">Heading</h2><script>alert(1)</script><a href="javascript:alert(1)">Link</a><img src="/image.jpg" onerror="run()" alt="Example">');

    expect($safe)->toContain('<h2>Heading</h2>', 'Link', 'src="/image.jpg"')
        ->not->toContain('<script', 'onclick', 'javascript:', 'onerror', 'alert(1)');
});