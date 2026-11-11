<?php

use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function cmsAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

it('protects all content module routes', function () {
    foreach (['pages', 'services', 'projects', 'blog'] as $resource) {
        $this->get(route("admin.{$resource}.index"))->assertRedirect(route('login'));
    }
});

it('uses the canonical Pages route and exposes the structured homepage editor', function () {
    expect(route('admin.pages.index', [], false))->toBe('/admin/pages')
        ->and(Route::has('admin.homepage'))->toBeTrue()
        ->and(Route::has('admin.homepage.preview'))->toBeTrue();
});

it('creates each content type through its Livewire form', function (string $resource, string $component, string $model, array $fields) {
    actingAs(cmsAdmin());

    Livewire::test($component, ['resource' => $resource])
        ->set($fields)
        ->call('saveDraft')
        ->assertHasNoErrors();

    expect($model::query()->where('slug', $fields['slug'])->count())->toBe(1);
})->with([
    'pages' => ['pages', 'pages::admin.content-form', Page::class, ['title' => 'Our approach', 'slug' => 'our-approach']],
    'services' => ['services', 'pages::admin.content-form', Service::class, ['title' => 'Cloud delivery', 'slug' => 'cloud-delivery']],
    'projects' => ['projects', 'pages::admin.content-form', Project::class, ['title' => 'Rexxsol launch', 'slug' => 'rexxsol-launch']],
    'blog' => ['blog', 'pages::admin.content-form', BlogPost::class, ['title' => 'Inside Rexxsol', 'slug' => 'inside-rexxsol']],
]);

it('validates required titles and unique slugs', function () {
    actingAs(cmsAdmin());
    Page::create(['title' => 'Existing', 'slug' => 'existing', 'status' => 'draft']);

    Livewire::test('pages::admin.content-form', ['resource' => 'pages'])
        ->set('slug', 'existing')
        ->call('saveDraft')
        ->assertHasErrors(['title', 'slug']);
});

it('loads and saves an existing Page through the edit form', function () {
    actingAs(cmsAdmin());
    $page = Page::create([
        'title' => 'Original title',
        'slug' => 'original-title',
        'excerpt' => 'Original excerpt',
        'content' => '<p>Original content</p>',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'seo_title' => 'Original SEO title',
        'seo_description' => 'Original SEO description',
    ]);

    Livewire::test('pages::admin.content-form', ['resource' => 'pages', 'model' => (string) $page->id])
        ->assertSet('title', 'Original title')
        ->assertSet('slug', 'original-title')
        ->assertSet('content', '<p>Original content</p>')
        ->assertSet('status', 'published')
        ->set('title', 'Updated title')
        ->set('slug', 'updated-title')
        ->set('excerpt', 'Updated excerpt')
        ->set('content', '<h2>Updated content</h2>')
        ->set('seo_title', 'Updated SEO title')
        ->set('seo_description', 'Updated SEO description')
        ->call('saveChanges')
        ->assertHasNoErrors();

    $page->refresh();
    expect($page->title)->toBe('Updated title')
        ->and($page->slug)->toBe('updated-title')
        ->and($page->excerpt)->toBe('Updated excerpt')
        ->and($page->content)->toBe('<h2>Updated content</h2>')
        ->and($page->seo_title)->toBe('Updated SEO title')
        ->and($page->seo_description)->toBe('Updated SEO description')
        ->and($page->published_at)->not->toBeNull();
});

it('allows the system Contact page copy to be edited without changing its reserved route slug', function () {
    actingAs(cmsAdmin());
    $page = Page::query()->where('template', 'system-contact')->firstOrFail();

    Livewire::test('pages::admin.content-form', ['resource' => 'pages', 'model' => (string) $page->id])
        ->assertSet('slug', 'contact')
        ->set('hero_eyebrow', 'LET’S BUILD SOMETHING')
        ->set('hero_heading', 'Tell us what you have in mind.')
        ->set('hero_description', 'Share a little about your project.')
        ->set('contact_info_heading', 'Let’s start a conversation.')
        ->set('contact_info_description', 'We would love to hear it.')
        ->set('contact_form_heading', 'Tell us about your project')
        ->set('contact_form_description', 'A few details to help us understand what you need.')
        ->call('saveChanges')
        ->assertHasNoErrors();

    $page->refresh();
    expect($page->hero_eyebrow)->toBe('LET’S BUILD SOMETHING')
        ->and($page->hero_heading)->toBe('Tell us what you have in mind.')
        ->and($page->contact_info_heading)->toBe('Let’s start a conversation.')
        ->and($page->contact_form_heading)->toBe('Tell us about your project');
});

it('saves a Page as draft and Save & Exit returns to the Pages listing', function () {
    actingAs(cmsAdmin());
    $page = Page::create(['title' => 'Exit test', 'slug' => 'exit-test', 'status' => 'published', 'published_at' => now()]);

    Livewire::test('pages::admin.content-form', ['resource' => 'pages', 'model' => (string) $page->id])
        ->set('title', 'Draft exit test')
        ->set('status', 'draft')
        ->call('saveAndExit')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.pages.index'));

    expect($page->refresh()->title)->toBe('Draft exit test')
        ->and($page->status)->toBe('draft')
        ->and($page->published_at)->toBeNull();
});

it('rejects duplicate Page slugs without losing entered values', function () {
    actingAs(cmsAdmin());
    $existing = Page::create(['title' => 'Existing', 'slug' => 'existing', 'status' => 'draft']);
    $page = Page::create(['title' => 'Editable', 'slug' => 'editable', 'status' => 'draft']);

    Livewire::test('pages::admin.content-form', ['resource' => 'pages', 'model' => (string) $page->id])
        ->set('title', 'Changed but invalid')
        ->set('slug', $existing->slug)
        ->set('content', '<p>Keep this content</p>')
        ->call('saveChanges')
        ->assertHasErrors(['slug'])
        ->assertSet('title', 'Changed but invalid')
        ->assertSet('content', '<p>Keep this content</p>');

    expect($page->refresh()->title)->toBe('Editable')
        ->and($page->slug)->toBe('editable');
});

it('prevents editors from publishing through the Page edit form', function () {
    actingAs(User::factory()->create(['role' => UserRole::Editor]));
    $page = Page::create(['title' => 'Editor page', 'slug' => 'editor-page', 'status' => 'draft']);

    Livewire::test('pages::admin.content-form', ['resource' => 'pages', 'model' => (string) $page->id])
        ->set('status', 'published')
        ->call('saveChanges')
        ->assertForbidden();
});

it('uploads and removes a featured image safely', function () {
    actingAs(cmsAdmin());
    Storage::fake('public');
    $page = Page::create(['title' => 'Image page', 'slug' => 'image-page', 'status' => 'draft']);

    $component = Livewire::test('pages::admin.content-form', ['resource' => 'pages', 'model' => (string) $page->id])
        ->set('featuredImageUpload', UploadedFile::fake()->image('featured.jpg'))
        ->call('saveDraft')
        ->assertHasNoErrors();

    $page->refresh();
    expect($page->featured_image)->not->toBeNull();
    expect(Storage::disk('public')->exists($page->featured_image))->toBeTrue();

    $component->call('removeFeaturedImage')
        ->assertSet('featuredImageRemoved', true)
        ->assertSet('featured_image', null)
        ->call('saveDraft')
        ->assertHasNoErrors();
    expect($page->refresh()->featured_image)->toBeNull();
});

it('returns 404 for a missing Page edit route', function () {
    actingAs(cmsAdmin());

    $this->get(route('admin.pages.edit', 999999))->assertNotFound();
});

it('redirects guests away from a Page edit route', function () {
    $this->get(route('admin.pages.edit', 1))->assertRedirect(route('login'));
});

it('allows an Editor to open a Page edit route but not publish', function () {
    $editor = User::factory()->create(['role' => UserRole::Editor]);
    $page = Page::create(['title' => 'Editor view', 'slug' => 'editor-view', 'status' => 'draft']);

    actingAs($editor);
    $this->get(route('admin.pages.edit', $page))->assertOk();
});

it('allows admins to publish and soft delete content', function () {
    actingAs(cmsAdmin());
    $page = Page::create(['title' => 'Launch page', 'slug' => 'launch-page', 'status' => 'draft']);

    Livewire::test('pages::admin.content-list', ['resource' => 'pages'])
        ->call('togglePublish', $page->id)
        ->assertHasNoErrors()
        ->call('confirmDelete', $page->id)
        ->call('delete');

    expect(Page::withTrashed()->find($page->id)->status)->toBe('published')
        ->and(Page::withTrashed()->find($page->id)->deleted_at)->not->toBeNull();
});

it('prevents editors from publishing content server side', function () {
    $editor = User::factory()->create(['role' => UserRole::Editor]);
    actingAs($editor);
    $page = Page::create(['title' => 'Editorial draft', 'slug' => 'editorial-draft', 'status' => 'draft']);

    Livewire::test('pages::admin.content-list', ['resource' => 'pages'])
        ->call('togglePublish', $page->id)
        ->assertForbidden();
});
