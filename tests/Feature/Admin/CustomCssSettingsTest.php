<?php

use App\Enums\UserRole;
use App\Livewire\Admin\AppearanceManager;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\CustomCssConfiguration;
use App\Support\CustomHtmlConfiguration;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

function customCssAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

it('saves and publicly serves enabled global CSS with a new version', function () {
    $this->actingAs(customCssAdmin());
    $configuration = app(CustomCssConfiguration::class);
    $before = $configuration->stylesheetVersion();

    Livewire::test(AppearanceManager::class, ['page' => 'custom-css'])
        ->set('customCss', ".rexxsol-notice {\n  color: #172033;\n}\n/* latest keystroke */")
        ->set('customCssEnabled', true)
        ->call('saveCss')
        ->assertHasNoErrors()
        ->assertDispatched('custom-css-saved');

    expect($configuration->stylesheetVersion())->not->toBe($before);
    expect(app(SiteSettings::class)->get('appearance.custom_css'))->toContain('latest keystroke');
    $this->get(route('custom-css', ['v' => $configuration->stylesheetVersion()]))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/css; charset=UTF-8')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertSee('.rexxsol-notice', false)
        ->assertSee('latest keystroke', false);
    expect(ActivityLog::query()->where('subject_type', 'custom_css')->count())->toBe(1);
});

it('invalidates cached settings only after the stylesheet transaction succeeds', function () {
    $this->actingAs(customCssAdmin());
    $configuration = app(CustomCssConfiguration::class);
    app(SiteSettings::class)->all();
    Cache::put(SiteSettings::CACHE_KEY, ['stale' => true], 60);

    expect($configuration->save('.valid { color: green; }', true, $configuration->fingerprint()))->toBeTrue()
        ->and(Cache::has(SiteSettings::CACHE_KEY))->toBeFalse();
});

it('loads versioned CSS after theme styles only on public pages and renders linked HTML once', function () {
    $this->actingAs(customCssAdmin());
    $configuration = app(CustomCssConfiguration::class);
    $configuration->save('.rexxsol-notice { color: blue; }', true, $configuration->fingerprint());
    $htmlConfiguration = app(CustomHtmlConfiguration::class);
    $htmlConfiguration->save([
        'header' => ['content' => '', 'enabled' => false],
        'body' => ['content' => '<div id="notice" class="rexxsol-notice">Test banner</div>', 'enabled' => true],
        'footer' => ['content' => '', 'enabled' => false],
    ], $htmlConfiguration->fingerprint());

    $response = $this->get('/')->assertOk();
    $markup = $response->getContent();
    $stylesheet = route('custom-css', ['v' => $configuration->stylesheetVersion()]);
    expect($markup)->toContain($stylesheet)
        ->and(substr_count($markup, $stylesheet))->toBe(1)
        ->and(substr_count($markup, 'id="notice"'))->toBe(1)
        ->and(strpos($markup, $stylesheet))->toBeGreaterThan(strpos($markup, 'theme.css'));

    $this->get(route('admin.appearance.custom-css'))
        ->assertOk()
        ->assertDontSee('/custom.css');

    auth()->logout();
    $this->get(route('login'))->assertOk()->assertDontSee('/custom.css');
    $this->get($stylesheet)->assertOk()->assertSee('.rexxsol-notice', false);
});

it('preserves CSS when disabled, then removes it from the public response', function () {
    $this->actingAs(customCssAdmin());
    $configuration = app(CustomCssConfiguration::class);
    $configuration->save('.notice { color: blue; }', true, $configuration->fingerprint());

    $component = Livewire::test(AppearanceManager::class, ['page' => 'custom-css']);
    $fingerprint = $configuration->fingerprint();
    $component->set('customCssEnabled', false)
        ->set('customCssFingerprint', $fingerprint)
        ->call('saveCss')
        ->assertHasNoErrors();

    expect(app(SiteSettings::class)->get('appearance.custom_css'))->toBe('.notice { color: blue; }');
    $this->get(route('custom-css'))->assertOk()->assertContent('');

    $component->set('customCssEnabled', true)
        ->set('customCssFingerprint', $configuration->fingerprint())
        ->call('saveCss')
        ->assertHasNoErrors();
    $this->get(route('custom-css'))->assertSee('.notice', false);

    $configuration->save('', true, $configuration->fingerprint());
    $this->get(route('custom-css'))->assertOk()->assertContent('');
});

it('accepts modern CSS and trusted local media URLs but reports invalid or unsafe input', function (string $css, bool $valid) {
    $error = app(CustomCssConfiguration::class)->validate($css);
    expect($error === null)->toBe($valid);
})->with([
    'media queries and custom properties' => [".notice { --gap: 12px; color: #172033; }\n@media (max-width: 640px) { .notice { padding: var(--gap); } }", true],
    'keyframes and local asset' => ["@keyframes fade { from { opacity: 0; } to { opacity: 1; } }\n.logo { background-image: url('/storage/media/logo.svg'); }", true],
    'reject import' => ['@import url("https://example.com/x.css");', false],
    'reject wrappers' => ['<style>.notice { color:red }</style>', false],
    'reject executable binding' => ['.notice { behavior: url(x.htc); }', false],
    'reject untrusted external resource' => ['.notice { background: url("https://evil.example/image.png"); }', false],
    'reject null byte' => [".notice { color: red; }\0", false],
    'reject unbalanced syntax' => ['.notice { color: red;', false],
    'accept URL-like text in a comment' => ['/* url(https://evil.example/image.png) */ .notice { color: red; }', true],
    'reject escaped import keyword' => ['@im\\70ort url(/storage/a.css);', false],
    'reject escaped URL function with script scheme' => ['.notice { background: u\\72l(javascript:alert(1)); }', false],
]);

it('rejects unauthorized save requests and prevents stale sessions overwriting newer CSS', function () {
    $configuration = app(CustomCssConfiguration::class);
    $this->actingAs(User::factory()->create(['role' => UserRole::Editor]));
    Livewire::test(AppearanceManager::class, ['page' => 'custom-css'])
        ->call('saveCss')
        ->assertForbidden();

    $this->actingAs(customCssAdmin());
    $staleFingerprint = $configuration->fingerprint();
    $configuration->save('.newer { color: green; }', true, $staleFingerprint);

    Livewire::test(AppearanceManager::class, ['page' => 'custom-css'])
        ->set('customCss', '.stale { color: red; }')
        ->set('customCssFingerprint', $staleFingerprint)
        ->call('saveCss')
        ->assertHasErrors('cssConflict');
    expect(app(SiteSettings::class)->get('appearance.custom_css'))->toBe('.newer { color: green; }');
});

it('keeps invalid editor content and reports a server-side validation error', function () {
    $this->actingAs(customCssAdmin());

    Livewire::test(AppearanceManager::class, ['page' => 'custom-css'])
        ->set('customCss', '@import url("/storage/theme.css");')
        ->call('saveCss')
        ->assertHasErrors('customCss')
        ->assertSet('customCss', '@import url("/storage/theme.css");');
});

it('keeps custom CSS separate from custom HTML and retains safe class and id attributes', function () {
    $settings = app(SiteSettings::class);
    $settings->put(['appearance.custom_html.body' => '<div id="notice" class="rexxsol-notice"><p>Hi</p></div>']);
    $beforeHtml = $settings->get('appearance.custom_html.body');

    $this->actingAs(customCssAdmin());
    $configuration = app(CustomCssConfiguration::class);
    $configuration->save('.rexxsol-notice { color: blue; }', true, $configuration->fingerprint());

    expect($settings->get('appearance.custom_html.body'))->toBe($beforeHtml)
        ->and(app(CustomHtmlConfiguration::class)->validationError('body', $beforeHtml))->toBeNull();
});
