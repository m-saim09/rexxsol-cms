<?php

use App\Enums\UserRole;
use App\Livewire\Admin\RobotsTxtEditor;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\User;
use App\Support\RobotsTxtConfiguration;
use App\Support\SiteSettings;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

function robotsEditorUser(UserRole $role = UserRole::Admin): User
{
    return User::factory()->create(['role' => $role]);
}

it('serves a public crawl-permitting default as plain text without a physical robots file', function () {
    expect(file_exists(public_path('robots.txt')))->toBeFalse();

    $response = $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    expect($response->getContent())
        ->toContain("User-agent: *\nDisallow:\n")
        ->toContain('Sitemap: '.route('sitemap'));
});

it('preserves the existing dynamic crawl policy until an administrator publishes an edited file', function () {
    app(SiteSettings::class)->put(['seo.indexing' => false]);

    $this->get('/robots.txt')->assertOk()->assertContent("User-agent: *\nDisallow: /\n");
});

it('protects the editor route and every update action with SEO permissions', function () {
    $this->actingAs(robotsEditorUser(UserRole::Editor));
    $this->get(route('admin.appearance.robots-txt'))->assertOk();
    Livewire::test(RobotsTxtEditor::class)->call('save')->assertForbidden();
    Livewire::test(RobotsTxtEditor::class)->call('restoreDefaults')->assertForbidden();
    Livewire::test(RobotsTxtEditor::class)->call('loadUpload')->assertForbidden();
    $this->get(route('admin.seo.robots'))->assertRedirect(route('admin.appearance.robots-txt'));

    $this->actingAs(User::factory()->create(['role' => UserRole::Editor]));
    $this->get(route('admin.appearance.robots-txt'))->assertOk();
});

it('saves validated content atomically and serves exactly the saved rules', function () {
    $this->actingAs(robotsEditorUser());
    $rules = "User-agent: ExampleBot\nDisallow: /private\nAllow: /private/public\nSitemap: ".route('sitemap')."\n";
    app(SiteSettings::class)->all();
    Cache::put(SiteSettings::CACHE_KEY, ['old' => 'cached'], 60);

    Livewire::test(RobotsTxtEditor::class)
        ->set('content', $rules)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('All changes saved')
        ->assertDontSee('Unsaved changes');

    expect(Cache::has(SiteSettings::CACHE_KEY))->toBeFalse();
    $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->assertContent($rules);
    expect(ActivityLog::query()->where('subject_type', 'robots.txt')->where('action', 'updated')->count())->toBe(1);
});

it('loads a UTF-8 upload into the editor without publishing it', function () {
    $this->actingAs(robotsEditorUser());
    $before = app(RobotsTxtConfiguration::class)->content();
    $upload = UploadedFile::fake()->createWithContent('robots.txt', "\xEF\xBB\xBFUser-agent: PreviewBot\r\nDisallow: /draft-only\r\n");

    Livewire::test(RobotsTxtEditor::class)
        ->set('upload', $upload)
        ->call('loadUpload')
        ->assertHasNoErrors()
        ->assertSet('content', "User-agent: PreviewBot\nDisallow: /draft-only\n")
        ->assertSee('Unsaved changes');

    $this->get('/robots.txt')->assertContent($before);
});

it('rejects invalid, binary, oversized, and non-txt uploads without publishing them', function () {
    $this->actingAs(robotsEditorUser());
    $component = Livewire::test(RobotsTxtEditor::class);
    $component->set('upload', UploadedFile::fake()->createWithContent('robots.csv', "User-agent: *\nDisallow:\n"))->call('loadUpload')->assertHasErrors('upload');
    $component->set('upload', UploadedFile::fake()->createWithContent('robots.txt', "User-agent: *\0\n"))->call('loadUpload')->assertHasErrors('upload');
    $component->set('upload', UploadedFile::fake()->createWithContent('robots.txt', str_repeat('a', 102401)))->call('loadUpload')->assertHasErrors('upload');
});

it('validates rules server-side and requires confirmation before blocking all crawlers', function () {
    $this->actingAs(robotsEditorUser());
    $before = app(RobotsTxtConfiguration::class)->content();
    $component = Livewire::test(RobotsTxtEditor::class)
        ->set('content', "Disallow: /private\nUser-agent: *\nDisallow: /\nSitemap: /sitemap.xml\n")
        ->call('save')
        ->assertHasErrors('content')
        ->assertSee('Line 1:')
        ->assertSee('Line 4:')
        ->assertSee('This blocks all crawlers from the site.')
        ->assertSee('Unsaved changes');

    expect($this->get('/robots.txt')->getContent())->toBe($before);

    $component->set('content', "User-agent: *\nDisallow: /\n")->set('blockingConfirmed', true)->call('save')->assertHasNoErrors();
    $this->get('/robots.txt')->assertContent("User-agent: *\nDisallow: /\n");
});

it('preserves the live version after validation errors and stale edits', function () {
    $this->actingAs(robotsEditorUser());
    $original = "User-agent: *\nDisallow:\n";
    app(SiteSettings::class)->put(['seo.robots' => $original]);
    $first = Livewire::test(RobotsTxtEditor::class);
    $stale = Livewire::test(RobotsTxtEditor::class);

    $first->set('content', "User-agent: *\nDisallow: /one\n")->call('save')->assertHasNoErrors();
    $stale->set('content', "User-agent: *\nDisallow: /stale\n")->call('save')->assertHasErrors('save')->assertSee('Your edits are preserved');

    $this->get('/robots.txt')->assertContent("User-agent: *\nDisallow: /one\n");
});

it('rolls back a failed audit write and keeps the previously published version', function () {
    $this->actingAs(robotsEditorUser());
    $original = "User-agent: *\nDisallow:\n";
    app(SiteSettings::class)->put(['seo.robots' => $original]);
    $event = 'eloquent.creating: '.ActivityLog::class;
    $this->app['events']->listen($event, function (): void {
        throw new QueryException('sqlite', 'insert into activity_logs', [], new PDOException('simulated audit failure'));
    });

    try {
        Livewire::test(RobotsTxtEditor::class)
            ->set('content', "User-agent: *\nDisallow: /new\n")
            ->call('save')
            ->assertHasErrors('save')
            ->assertSee('previously published version remains live')
            ->assertSee('Unsaved changes');
    } finally {
        $this->app['events']->forget($event);
    }

    $this->get('/robots.txt')->assertContent($original);
});

it('restores editor defaults without publishing until save', function () {
    $this->actingAs(robotsEditorUser());
    $original = "User-agent: *\nDisallow: /old\n";
    app(SiteSettings::class)->put(['seo.robots' => $original]);

    Livewire::test(RobotsTxtEditor::class)
        ->call('restoreDefaults')
        ->assertSet('content', app(RobotsTxtConfiguration::class)->defaultContent())
        ->assertSee('Unsaved changes');

    $this->get('/robots.txt')->assertContent($original);
});

it('reports compatibility warnings for nonstandard directives while accepting crawler-specific groups', function () {
    $analysis = app(RobotsTxtConfiguration::class)->analyze("User-agent: *\nUser-agent: ExampleBot\nCrawl-delay: 5\nNoindex: /private\nDisallow:\n");

    expect($analysis['errors'])->toBe([])
        ->and($analysis['warnings'])->toHaveCount(2)
        ->and($analysis['warnings'][1]['message'])->toContain('not a reliable')
        ->and($analysis['blocks_all'])->toBeFalse();
});

it('inserts the verified canonical sitemap URL once and reports obvious HTML', function () {
    $this->actingAs(robotsEditorUser());
    $component = Livewire::test(RobotsTxtEditor::class)
        ->set('content', '<p>copied from a web page</p>')
        ->assertSee('This looks like HTML')
        ->call('insertSitemap')
        ->call('insertSitemap');

    $sitemapLines = collect(explode("\n", $component->get('content')))
        ->filter(fn (string $line): bool => str_starts_with($line, 'Sitemap:'));

    expect($sitemapLines)->toHaveCount(1)
        ->and($sitemapLines->first())->toBe('Sitemap: '.route('sitemap'));
});
