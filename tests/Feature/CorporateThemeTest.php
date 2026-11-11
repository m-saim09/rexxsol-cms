<?php

use App\Livewire\Admin\AppearanceManager;
use App\Models\User;
use App\Support\SiteSettings;
use App\Support\ThemeRegistry;
use Livewire\Livewire;

it('registers Rexxsol Corporate and isolates theme previews', function () {
    expect(app(ThemeRegistry::class)->find('rexxsol-corporate'))->toMatchArray([
        'name' => 'Rexxsol Corporate',
        'slug' => 'rexxsol-corporate',
        'version' => '1.0.0',
    ]);

    app(SiteSettings::class)->put(['appearance.active_theme' => 'rexxsol-corporate']);

    $this->get('/theme-preview/rexxsol-corporate')->assertOk()->assertSee('Building digital solutions that move business forward.');
    expect(app(ThemeRegistry::class)->active()['slug'])->toBe('rexxsol-corporate');
});

it('allows appearance viewers to view themes but requires manage permission to activate one', function () {
    $user = User::factory()->create(['role' => 'editor']);

    Livewire::actingAs($user)
        ->test(AppearanceManager::class, ['page' => 'themes'])
        ->assertOk()
        ->call('activateTheme', 'rexxsol-corporate')
        ->assertForbidden();
});
