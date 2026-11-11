<?php

use App\Enums\UserRole;
use App\Livewire\Admin\AppearanceManager;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\SiteSettings;
use App\Support\ThemeOptionsConfiguration;
use App\Support\ThemeRegistry;
use Livewire\Livewire;

it('renders the theme options page with per-theme controls and a scoped preview', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));

    $this->get(route('admin.appearance.theme-options'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Theme Options')
        ->assertDontSee('Live sample preview')
        ->assertSee('Restore theme defaults')
        ->assertSee('Custom CSS');
});

it('uses declared defaults for empty colors and keeps theme options isolated when switching themes', function () {
    $registry = app(ThemeRegistry::class);
    $options = app(ThemeOptionsConfiguration::class);
    $corporate = $registry->find(ThemeRegistry::CORPORATE);
    $bizconsult = $registry->find(ThemeRegistry::BIZCONSULT);

    app(SiteSettings::class)->put(['appearance.brand_color' => '']);

    expect($options->values($corporate)['brand_color'])->toBe('#000080')
        ->and($options->values($bizconsult)['brand_color'])->toBe('#78b77a')
        ->and($options->availableFonts($bizconsult))->toContain('Inter', 'Heebo');

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    Livewire::test(AppearanceManager::class, ['page' => 'theme-options'])
        ->call('selectTheme', ThemeRegistry::BIZCONSULT)
        ->assertSet('themeSlug', ThemeRegistry::BIZCONSULT)
        ->assertSet('themeOptions.brand_color', '#78b77a')
        ->call('restoreThemeDefaults')
        ->assertSet('themeOptions.brand_color', '#78b77a');
});

it('persists theme-specific values, actor audit, and copyright placeholders without activating that theme', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $configuration = app(ThemeOptionsConfiguration::class);
    $registry = app(ThemeRegistry::class);
    $theme = $registry->find(ThemeRegistry::BIZCONSULT);
    $other = $registry->find(ThemeRegistry::CORPORATE);
    $active = $registry->active()['slug'];

    Livewire::test(AppearanceManager::class, ['page' => 'theme-options'])
        ->call('selectTheme', ThemeRegistry::BIZCONSULT)
        ->set('themeOptions.brand_color', '#13579B')
        ->set('themeOptions.heading_font', 'Inter')
        ->set('themeOptions.body_font', 'Heebo')
        ->set('themeOptions.copyright', '© {year} {site_name} - rights reserved')
        ->call('saveThemeOptions')
        ->assertHasNoErrors()
        ->assertDispatched('theme-options-saved');

    $saved = $configuration->values($theme);
    expect($saved['brand_color'])->toBe('#13579B')
        ->and($saved['copyright'])->toBe('© {year} {site_name} - rights reserved')
        ->and($configuration->values($other)['brand_color'])->toBe('#000080')
        ->and($configuration->publicValues($theme)['copyright'])->toBe('© '.now()->format('Y').' Rexxsol - rights reserved')
        ->and($registry->active()['slug'])->toBe($active)
        ->and(ActivityLog::query()->where('subject_type', 'theme_options')->where('subject_label', 'BizConsult Theme Options')->exists())->toBeTrue();
});

it('rejects unsupported values and prevents stale theme option edits from overwriting changes', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $configuration = app(ThemeOptionsConfiguration::class);
    $theme = app(ThemeRegistry::class)->find(ThemeRegistry::CORPORATE);
    $baseline = $configuration->fingerprint($theme);

    expect($configuration->validate($theme, [
        'brand_color' => 'not-a-color',
        'heading_font' => 'https://fonts.example/font.css',
        'body_font' => 'Instrument Sans',
        'copyright' => '{unknown}',
    ]))->toHaveKeys(['brand_color', 'heading_font', 'copyright']);

    $this->assertTrue($configuration->save($theme, [
        'brand_color' => '#123456',
        'heading_font' => 'Georgia',
        'body_font' => 'Instrument Sans',
        'copyright' => '',
    ], $baseline));
    expect($configuration->save($theme, [
        'brand_color' => '#654321',
        'heading_font' => 'Georgia',
        'body_font' => 'Instrument Sans',
        'copyright' => '',
    ], $baseline))->toBeFalse();
});

it('requires appearance manage permission for save and reset actions', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Editor]));
    Livewire::test(AppearanceManager::class, ['page' => 'theme-options'])
        ->call('saveThemeOptions')
        ->assertForbidden();
    Livewire::test(AppearanceManager::class, ['page' => 'theme-options'])
        ->call('restoreThemeDefaults')
        ->assertForbidden();
});

it('applies theme tokens in the public layout while leaving admin stylesheets out', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    app(SiteSettings::class)->put(['appearance.active_theme' => ThemeRegistry::CORPORATE]);
    $registry = app(ThemeRegistry::class);
    $theme = $registry->active();
    $config = app(ThemeOptionsConfiguration::class);
    $values = $config->values($theme);
    $values['brand_color'] = '#123456';
    $config->save($theme, $values, $config->fingerprint($theme));

    $public = $this->get('/')->assertOk()->getContent();
    expect($public)->toContain('#123456')
        ->and($public)->toContain('--corporate-navy: #123456')
        ->and($public)->toContain('rexxsol-corporate.css');

    $admin = $this->get(route('admin.appearance.theme-options'))->assertOk()->getContent();
    expect($admin)->not->toContain('--nova-blue: #123456');

});
