<?php

use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('redirects guests away from the admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('allows CMS users to view truthful dashboard totals', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    Page::where('slug', 'about')->firstOrFail()->update(['status' => 'published']);
    Service::create(['name' => 'Consulting', 'slug' => 'consulting', 'is_active' => true]);
    Project::create(['name' => 'Launch', 'slug' => 'launch', 'status' => 'published']);
    BlogPost::create(['title' => 'News', 'slug' => 'news', 'status' => 'draft']);

    actingAs($user);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Good afternoon, '.$user->name)
        ->assertSee('Live database totals')
        ->assertSee('Content snapshot');
});
