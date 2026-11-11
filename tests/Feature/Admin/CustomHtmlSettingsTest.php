<?php

use App\Enums\UserRole;
use App\Livewire\Admin\AppearanceManager;
use App\Models\User;
use App\Support\CustomHtmlConfiguration;
use App\Support\SiteSettings;
use Livewire\Livewire;

it('loads the custom html editor and preserves legacy settings into the three sections', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));

    app(SiteSettings::class)->put([
        'appearance.custom_html' => '<a href="https://example.com">Example</a>',
        'appearance.custom_html_location' => 'body_start',
        'appearance.custom_html_enabled' => true,
    ]);

    $configuration = app(CustomHtmlConfiguration::class);
    $configuration->migrateLegacySettings();

    $sections = $configuration->editableSections();
    expect($sections['header']['content'])->toBe('')
        ->and($sections['body']['content'])->toBe('<a href="https://example.com">Example</a>')
        ->and($sections['body']['enabled'])->toBeTrue()
        ->and($sections['footer']['content'])->toBe('');

    $this->get(route('admin.appearance.custom-html'))->assertOk();
    Livewire::test(AppearanceManager::class, ['page' => 'custom-html'])
        ->assertSet('headerHtml', '')
        ->assertSet('bodyHtml', '<a href="https://example.com">Example</a>');
});

it('saves header, body, and footer snippets together and rejects unsafe markup', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $configuration = app(CustomHtmlConfiguration::class);
    $configuration->migrateLegacySettings();

    $component = Livewire::test(AppearanceManager::class, ['page' => 'custom-html'])
        ->set('headerHtml', '<meta name="robots" content="index,follow">')
        ->set('headerHtmlEnabled', true)
        ->set('bodyHtml', '<p>Welcome</p>')
        ->set('bodyHtmlEnabled', true)
        ->set('footerHtml', '<p>Footer</p>')
        ->set('footerHtmlEnabled', true)
        ->set('customHtmlFingerprint', $configuration->fingerprint())
        ->call('saveHtml');

    $component->assertHasNoErrors();

    $settings = app(SiteSettings::class);
    expect($settings->get('appearance.custom_html.header'))->toBe('<meta name="robots" content="index,follow">')
        ->and($settings->get('appearance.custom_html.body'))->toBe('<p>Welcome</p>')
        ->and($settings->get('appearance.custom_html.footer'))->toBe('<p>Footer</p>');

    Livewire::test(AppearanceManager::class, ['page' => 'custom-html'])
        ->set('bodyHtml', '<script>alert(1)</script>')
        ->call('saveHtml')
        ->assertHasErrors('bodyHtml');
});
