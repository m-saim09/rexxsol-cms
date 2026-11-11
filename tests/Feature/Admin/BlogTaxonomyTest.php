<?php

use App\Enums\UserRole;
use App\Livewire\Admin\BlogCategories;
use App\Livewire\Admin\BlogTags;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\User;
use Livewire\Livewire;

function blogTaxonomyAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

it('registers the complete Blog management route structure', function () {
    foreach (['admin.blog.index', 'admin.blog.categories.index', 'admin.blog.categories.create', 'admin.blog.tags.index', 'admin.blog.tags.create', 'admin.blog.reports.index'] as $route) {
        expect(route($route, [], false))->toStartWith('/admin/blog');
    }
});

it('creates categories and tags with normalized slugs', function () {
    $this->actingAs(blogTaxonomyAdmin());

    Livewire::test(BlogCategories::class)
        ->call('create')
        ->set('name', 'Digital Transformation')
        ->call('save')
        ->assertHasNoErrors();
    Livewire::test(BlogTags::class)
        ->call('create')
        ->set('name', 'Case Studies')
        ->call('save')
        ->assertHasNoErrors();

    expect(BlogCategory::first()->slug)->toBe('digital-transformation')
        ->and(BlogTag::first()->slug)->toBe('case-studies');
});

it('syncs blog taxonomy relationships when a post is saved', function () {
    $this->actingAs(blogTaxonomyAdmin());

    Livewire::test('pages::admin.content-form', ['resource' => 'blog'])
        ->set('title', 'Taxonomy post')
        ->set('slug', 'taxonomy-post')
        ->set('category', 'Engineering')
        ->set('tags', 'Laravel, CMS')
        ->call('saveDraft')
        ->assertHasNoErrors();

    $post = BlogPost::firstOrFail();
    expect($post->categories()->pluck('name')->all())->toBe(['Engineering'])
        ->and($post->tagsRelation()->pluck('name')->sort()->values()->all())->toBe(['CMS', 'Laravel']);
});

it('prevents deleting taxonomy terms that are in use', function () {
    $this->actingAs(blogTaxonomyAdmin());
    $category = BlogCategory::create(['name' => 'Used', 'slug' => 'used']);
    $tag = BlogTag::create(['name' => 'Used tag', 'slug' => 'used-tag']);
    $post = BlogPost::create(['title' => 'Uses taxonomy', 'slug' => 'uses-taxonomy']);
    $post->categories()->attach($category);
    $post->tagsRelation()->attach($tag);

    Livewire::test(BlogCategories::class)->call('confirmDelete', $category->id)->call('delete')->assertHasErrors('delete');
    Livewire::test(BlogTags::class)->call('confirmDelete', $tag->id)->call('delete')->assertHasErrors('delete');

    expect(BlogCategory::find($category->id))->not->toBeNull()
        ->and(BlogTag::find($tag->id))->not->toBeNull();
});

it('denies blog taxonomy management to inactive users', function () {
    $user = User::factory()->create(['role' => UserRole::Editor, 'is_active' => false]);
    $this->actingAs($user);

    $this->get(route('admin.blog.categories.index'))->assertForbidden();
    Livewire::test(BlogCategories::class)->assertForbidden();
});

it('searches content modules granted to editors', function () {
    $editor = User::factory()->create(['role' => UserRole::Editor]);
    Page::create(['title' => 'Searchable page', 'slug' => 'searchable-page']);
    ContactMessage::create(['name' => 'Searchable contact', 'email' => 'search@example.org', 'subject' => 'Search subject', 'message' => 'Private message']);

    $this->actingAs($editor);
    $this->get(route('admin.search', ['q' => 'Searchable']))->assertOk()->assertSee('Searchable page')->assertSee('Searchable contact');
});
