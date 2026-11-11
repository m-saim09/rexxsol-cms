<?php

use App\Models\HomepageSection;
use App\Models\Service;
use App\Support\HomepageContentService;
use App\Support\SiteSettings;
use Carbon\CarbonImmutable;

function createPublicService(array $attributes = []): Service
{
    return Service::create(array_merge([
        'name' => 'Test Service',
        'slug' => 'test-service',
        'short_description' => 'A published test service.',
        'description' => 'A published test service description.',
        'icon' => 'web',
        'status' => 'published',
        'is_active' => true,
        'published_at' => now(),
        'sort_order' => 0,
    ], $attributes));
}

it('uses only currently public services across homepage and listing', function () {
    HomepageSection::create([
        'key' => 'services',
        'name' => 'Services',
        'enabled' => true,
        'sort_order' => 2,
        'settings' => ['limit' => 3, 'selected_ids' => []],
    ]);

    createPublicService(['name' => 'First Public Service', 'slug' => 'first-public-service', 'sort_order' => 1]);
    createPublicService(['name' => 'Second Public Service', 'slug' => 'second-public-service', 'sort_order' => 2]);
    createPublicService(['name' => 'Third Public Service', 'slug' => 'third-public-service', 'sort_order' => 3]);
    createPublicService(['name' => 'Fourth Public Service', 'slug' => 'fourth-public-service', 'sort_order' => 4]);
    createPublicService(['name' => 'Draft Service', 'slug' => 'draft-service', 'status' => 'draft', 'is_active' => false, 'sort_order' => 5]);
    createPublicService(['name' => 'Future Service', 'slug' => 'future-service', 'published_at' => CarbonImmutable::now()->addDay(), 'sort_order' => 6]);

    HomepageContentService::forget();

    $homepageItems = app(HomepageContentService::class)->data()['services']['items'];

    expect($homepageItems)->toHaveCount(4);
    expect(collect($homepageItems)->pluck('title')->all())
        ->toEqual(['First Public Service', 'Second Public Service', 'Third Public Service', 'Fourth Public Service']);

    $this->get('/services')
        ->assertOk()
        ->assertSee('First Public Service')
        ->assertSee('Fourth Public Service')
        ->assertDontSee('Draft Service')
        ->assertDontSee('Future Service');

    $this->get('/services/draft-service')->assertNotFound();
    $this->get('/services/future-service')->assertNotFound();
});

it('reflects service edits and removes unpublished or deleted detail pages', function () {
    $service = createPublicService([
        'name' => 'Editable Service',
        'slug' => 'editable-service',
        'short_description' => 'Before editing.',
    ]);

    $this->get('/services/editable-service')->assertOk()->assertSee('Before editing.');

    $service->update(['short_description' => 'After editing.']);
    $this->get('/services/editable-service')->assertOk()->assertSee('After editing.');

    $service->update(['status' => 'draft', 'is_active' => false]);
    $this->get('/services/editable-service')->assertNotFound();

    $service->delete();
    $this->get('/services/editable-service')->assertNotFound();
});

it('refreshes homepage testimonials when testimonial publication changes', function () {
    HomepageSection::create([
        'key' => 'testimonials',
        'name' => 'Testimonials',
        'enabled' => true,
        'sort_order' => 6,
        'settings' => ['limit' => 3, 'selected_ids' => []],
    ]);

    $testimonial = \App\Models\Testimonial::create([
        'name' => 'Taylor Brooks',
        'company' => 'Northlane',
        'quote' => 'The delivery process felt calm and focused.',
        'status' => 'draft',
        'published_at' => null,
        'sort_order' => 1,
    ]);

    $items = app(HomepageContentService::class)->data()['testimonials']['items'];
    expect($items)->toHaveCount(3)
        ->and(collect($items)->pluck('title')->all())->toBe(['Erin Hall', 'Samir Khan', 'Sofia Chen']);

    $testimonial->update(['status' => 'published', 'published_at' => now()]);
    $items = app(HomepageContentService::class)->data()['testimonials']['items'];
    expect($items)->toHaveCount(3)
        ->and($items[0]['title'])->toBe('Taylor Brooks');

    $testimonial->update(['quote' => 'The updated delivery process feels calm and focused.']);
    expect(app(HomepageContentService::class)->data()['testimonials']['items'][0]['quote'])
        ->toBe('The updated delivery process feels calm and focused.');

    $testimonial->update(['status' => 'draft']);

    $items = app(HomepageContentService::class)->data()['testimonials']['items'];
    expect($items)->toHaveCount(3)
        ->and(collect($items)->pluck('title')->all())->toBe(['Erin Hall', 'Samir Khan', 'Sofia Chen']);
});

it('shows all published testimonials newest first and removes frontend fallbacks as real testimonials replace them', function () {
    HomepageSection::create([
        'key' => 'testimonials',
        'name' => 'Testimonials',
        'enabled' => true,
        'sort_order' => 6,
        'settings' => ['limit' => 1, 'selected_ids' => []],
    ]);

    $oldest = \App\Models\Testimonial::create([
        'name' => 'Oldest Real',
        'quote' => 'An older published testimonial from our client.',
        'status' => 'published',
        'published_at' => now()->subDays(3),
    ]);
    $newest = \App\Models\Testimonial::create([
        'name' => 'Newest Real',
        'quote' => 'The newest published testimonial from our client.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    \App\Models\Testimonial::create([
        'name' => 'Draft Testimonial',
        'quote' => 'This testimonial is not published.',
        'status' => 'draft',
        'published_at' => now(),
    ]);

    $items = app(HomepageContentService::class)->data()['testimonials']['items'];
    expect($items)->toHaveCount(3)
        ->and(collect($items)->pluck('title')->all())->toBe(['Newest Real', 'Oldest Real', 'Erin Hall']);

    \App\Models\Testimonial::create([
        'name' => 'Third Real',
        'quote' => 'A third published testimonial from our client.',
        'status' => 'published',
        'published_at' => now()->subDays(2),
    ]);

    $items = app(HomepageContentService::class)->data()['testimonials']['items'];
    expect($items)->toHaveCount(3)
        ->and(collect($items)->pluck('title')->all())->toBe(['Newest Real', 'Third Real', 'Oldest Real']);

    \App\Models\Testimonial::create([
        'name' => 'Fourth Real',
        'quote' => 'A fourth published testimonial from our client.',
        'status' => 'published',
        'published_at' => now()->subDays(4),
    ]);

    $items = app(HomepageContentService::class)->data()['testimonials']['items'];
    expect($items)->toHaveCount(4)
        ->and(collect($items)->pluck('title')->all())->toBe(['Newest Real', 'Third Real', 'Oldest Real', 'Fourth Real']);

    $oldest->delete();
    $items = app(HomepageContentService::class)->data()['testimonials']['items'];
    expect(collect($items)->pluck('title')->all())->toBe(['Newest Real', 'Third Real', 'Fourth Real']);
});

it('renders the same published-first testimonial fallback across all registered themes', function () {
    HomepageSection::create([
        'key' => 'testimonials',
        'name' => 'Testimonials',
        'enabled' => true,
        'sort_order' => 6,
        'settings' => ['limit' => 1, 'selected_ids' => [999999]],
    ]);
    \App\Models\Testimonial::create([
        'name' => 'Published Client',
        'quote' => 'Our published CMS testimonial.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    foreach (['rexxsol-corporate', 'rexxsol-nova', 'bizconsult'] as $theme) {
        app(SiteSettings::class)->put(['appearance.active_theme' => $theme]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Published Client')
            ->assertSee('Erin Hall')
            ->assertSee('Samir Khan')
            ->assertDontSee('Sofia Chen');
    }

    $this->get('/services')
        ->assertOk()
        ->assertSee('Published Client')
        ->assertSee('Erin Hall')
        ->assertSee('Samir Khan')
        ->assertDontSee('Sofia Chen');

    $portfolio = $this->get('/projects')->assertOk();
    expect(substr_count($portfolio->getContent(), 'Published Client'))->toBe(2)
        ->and(substr_count($portfolio->getContent(), 'Erin Hall'))->toBe(2)
        ->and(substr_count($portfolio->getContent(), 'Samir Khan'))->toBe(2)
        ->and($portfolio->getContent())->not->toContain('Sofia Chen');
});
