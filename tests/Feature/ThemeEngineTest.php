<?php

use App\Enums\UserRole;
use App\Models\HomepageSection;
use App\Models\Project;
use App\Models\User;
use App\Support\HomepageContentService;
use App\Support\ThemeRegistry;

it('discovers the standard theme manifest and keeps the active theme fallback stable', function () {
    $registry = app(ThemeRegistry::class);

    expect($registry->all())->toHaveKeys(['rexxsol-corporate', 'bizconsult', 'rexxsol-nova'])
        ->and($registry->manifest('bizconsult'))->toMatchArray([
            'name' => 'BizConsult',
            'slug' => 'bizconsult',
            'version' => '1.0.0',
        ])
        ->and($registry->manifest('rexxsol-nova'))->toMatchArray([
            'name' => 'Rexxsol Nova',
            'slug' => 'rexxsol-nova',
            'view' => 'themes.rexxsol-nova',
        ])
        ->and($registry->active()['slug'])->toBe('rexxsol-corporate');

    expect($registry->supports('rexxsol-corporate', 'hero'))->toBeTrue()
        ->and($registry->supports('bizconsult', 'team'))->toBeTrue()
        ->and($registry->supports('rexxsol-nova', 'hero'))->toBeTrue();
});

it('resolves theme-specific components and falls back safely when a theme does not define one', function () {
    $registry = app(ThemeRegistry::class);

    expect($registry->resolveComponent('hero', 'bizconsult'))->toBeString()
        ->and($registry->resolveComponent('missing-block', 'bizconsult'))->toBeString()
        ->and($registry->resolveTemplate('page', 'bizconsult'))->toBeString()
        ->and($registry->resolveTemplate('page', 'rexxsol-nova'))->toBe('themes.rexxsol-nova.templates.page');
});

it('renders the Nova preview without changing the active theme', function () {
    HomepageSection::updateOrCreate(
        ['key' => 'hero'],
        ['name' => 'Hero', 'enabled' => true, 'sort_order' => 0, 'settings' => []]
    );
    HomepageContentService::forget();

    $response = $this->get('/theme-preview/rexxsol-nova');

    $response->assertOk()
        ->assertSee('theme-rexxsol-nova', false)
        ->assertSee('<div class="nova-header-slot" data-nova-header-slot data-home-page="true">', false)
        ->assertSee('data-nova-header data-home-page="true"', false)
        ->assertSee('Build Your Digital Future')
        ->assertSee('With <span class="nova-title-brand">Rexxsol</span>', false)
        ->assertSee('From idea to launch')
        ->assertSee('Built for your business')
        ->assertSee('Let’s talk.')
        ->assertSee('Tell us what you’re building.')
        ->assertSee('We’ll help you take it forward.')
        ->assertSee('HAVE A PROJECT IN MIND?')
        ->assertSee('Start a conversation')
        ->assertSee('<div class="nova-final-cta-inner nova-container">', false)
        ->assertSee('class="nova-final-cta-title-link"', false)
        ->assertSee('class="nova-final-cta-action" aria-hidden="true"', false)
        ->assertSee('Thoughtful digital solutions.')
        ->assertSee('contact@rexxsol.com')
        ->assertSee(route('website.contact'), false)
        ->assertSee(route('website.portfolio'), false);

    $this->actingAs(User::factory()->create(['role' => UserRole::SuperAdmin]));
    $this->get('/projects?theme=rexxsol-nova')
        ->assertOk()
        ->assertSee('class="nova-page nova-inner-hero"', false);

    Project::create(['name' => 'Nova detail page test', 'slug' => 'nova-detail-page-test', 'status' => 'published', 'published_at' => now()]);
    $this->get('/projects/nova-detail-page-test?theme=rexxsol-nova')
        ->assertOk()
        ->assertSee('class="nova-page nova-inner-hero"', false);

    foreach (['/about', '/services', '/portfolio', '/blog', '/contact'] as $path) {
        $this->get($path.'?theme=rexxsol-nova')
            ->assertOk()
            ->assertSee('theme-rexxsol-nova', false)
            ->assertSee('data-nova-header data-home-page="false"', false);
    }

    expect(app(ThemeRegistry::class)->active()['slug'])->toBe('rexxsol-corporate');
});
