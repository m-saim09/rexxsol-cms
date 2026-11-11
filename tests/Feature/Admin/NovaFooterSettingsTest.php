<?php

use App\Enums\UserRole;
use App\Livewire\Admin\NovaFooterSettings;
use App\Models\NavigationItem;
use App\Models\User;
use App\Support\FooterService;
use App\Support\NovaFooterService;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

it('persists Nova CTA settings and renders them on the About page without changing other themes', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::SuperAdmin]));

    Livewire::test(NovaFooterSettings::class)
        ->set('settings.cta.heading', 'Build with us today')
        ->set('settings.cta.button_label', 'Talk to our team')
        ->set('settings.cta.button_icon', 'arrow-up')
        ->set('settings.cta.button_url', '/contact')
        ->set('settings.cta.secondary_label', 'Plan a project')
        ->set('settings.cta.secondary_url', '/contact')
        ->set('settings.contact.heading', 'Contact our team')
        ->set('settings.contact.email', 'nova@example.com')
        ->call('save')
        ->assertHasNoErrors();

    $siteSettings = app(SiteSettings::class);
    expect($siteSettings->get('theme.rexxsol-nova.footer.cta.heading'))->toBe('Build with us today')
        ->and($siteSettings->get('theme.rexxsol-nova.footer.cta.button_icon'))->toBe('arrow-up')
        ->and($siteSettings->get('theme.rexxsol-nova.footer.cta.secondary_label'))->toBe('Plan a project')
        ->and($siteSettings->get('theme.rexxsol-nova.footer.contact.heading'))->toBe('Contact our team');

    $siteSettings->put(['appearance.active_theme' => 'rexxsol-nova']);
    FooterService::forget();
    Cache::flush();

    $novaData = app(NovaFooterService::class)->data();
    expect($novaData['cta']['heading'])->toBe('Build with us today')
        ->and($novaData['cta']['button_icon'])->toBe('arrow-up')
        ->and($novaData['cta']['secondary_label'])->toBe('Plan a project')
        ->and($novaData['cta']['secondary_url'])->toEndWith('/contact')
        ->and($novaData['contact']['email'])->toBe('nova@example.com')
        ->and($novaData['contact_heading'])->toBe('Contact our team');

    $this->get(route('website.about'))
        ->assertOk()
        ->assertSee('Build with us today')
        ->assertSee('Plan a project')
        ->assertSee('mailto:nova@example.com')
        ->assertSee('class="nova-final-cta-title-link"', false)
        ->assertSee('aria-label="Talk to our team"', false)
        ->assertSee('Contact our team');

    $siteSettings->put(['appearance.active_theme' => 'rexxsol-corporate']);
    FooterService::forget();
    Cache::flush();

    expect(app(NovaFooterService::class)->data()['cta']['heading'])->toBe('Build with us today');
});

it('keeps the Nova footer compact by default and shows CMS legal links on the About reference page', function () {
    $explore = NavigationItem::create([
        'menu' => 'footer',
        'label' => 'Explore',
        'type' => 'column',
        'enabled' => true,
        'sort_order' => 1,
    ]);
    $legal = NavigationItem::create([
        'menu' => 'footer',
        'label' => 'Legal',
        'type' => 'column',
        'enabled' => true,
        'sort_order' => 2,
    ]);

    foreach ([
        ['Privacy information', '/privacy'],
        ['Terms and Conditions', '/terms-and-conditions'],
    ] as $index => [$label, $url]) {
        NavigationItem::create([
            'menu' => 'footer',
            'parent_id' => $explore->id,
            'label' => $label,
            'type' => 'custom',
            'url' => $url,
            'enabled' => true,
            'sort_order' => $index + 1,
        ]);
    }

    foreach ([
        ['Privacy Policy', '/privacy-policy'],
        ['Terms and Conditions', '/terms'],
        ['Accessibility', '/accessibility'],
    ] as $index => [$label, $url]) {
        NavigationItem::create([
            'menu' => 'footer',
            'parent_id' => $legal->id,
            'label' => $label,
            'type' => 'custom',
            'url' => $url,
            'enabled' => true,
            'sort_order' => $index + 1,
        ]);
    }

    app(SiteSettings::class)->put(['appearance.active_theme' => 'rexxsol-nova']);
    FooterService::forget();
    Cache::flush();

    $footerData = app(NovaFooterService::class)->data();
    $footerLocationLabels = collect($footerData['locations'])
        ->flatMap(fn (array $location): array => $location['links'])
        ->pluck('label')
        ->all();

    expect($footerData['legal'])
        ->toHaveCount(1)
        ->and($footerData['legal'][0]['label'])
        ->toBe('Accessibility')
        ->and($footerLocationLabels)
        ->not->toContain('Privacy information')
        ->not->toContain('Terms and Conditions');

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('nova-premium-footer-waves')
        ->assertDontSee('Privacy Policy')
        ->assertDontSee('Terms and Conditions')
        ->assertSee('Accessibility');

    $this->get(route('website.about'))
        ->assertOk()
        ->assertSee('nova-premium-footer')
        ->assertSee('Privacy information')
        ->assertSee('Terms and Conditions')
        ->assertSee('Accessibility');
});

it('renders CMS social URLs as accessible Nova footer SVG links', function () {
    app(SiteSettings::class)->put([
        'appearance.active_theme' => 'rexxsol-nova',
        'social.linkedin' => 'https://linkedin.com/company/rexxsol',
        'social.facebook' => 'https://facebook.com/rexxsol',
        'social.instagram' => 'https://instagram.com/rexxsol',
    ]);
    FooterService::forget();
    Cache::flush();

    $this->get(route('website.about'))
        ->assertOk()
        ->assertSee('href="https://linkedin.com/company/rexxsol" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"', false)
        ->assertSee('href="https://facebook.com/rexxsol" target="_blank" rel="noopener noreferrer" aria-label="Facebook"', false)
        ->assertSee('href="https://instagram.com/rexxsol" target="_blank" rel="noopener noreferrer" aria-label="Instagram"', false)
        ->assertSee('<svg viewBox="0 0 448 512" aria-hidden="true" focusable="false">', false)
        ->assertSee('<svg viewBox="0 0 320 512" aria-hidden="true" focusable="false">', false)
        ->assertSee('<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">', false);
});
