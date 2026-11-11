<?php

use App\Enums\UserRole;
use App\Livewire\Admin\ActivityLogs;
use App\Livewire\Admin\ContactMessages;
use App\Livewire\Admin\SeoSettings;
use App\Livewire\Admin\SettingsEditor;
use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\Users;
use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function phaseFourUser(UserRole $role = UserRole::SuperAdmin): User
{
    return User::factory()->create(['role' => $role]);
}

function phaseFourMedia(): Media
{
    return Media::create(['disk' => 'public', 'path' => 'cms/media/test.png', 'original_name' => 'test.png', 'file_name' => 'test.png', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => 200]);
}

it('protects Phase 4 routes from guests and editors', function () {
    foreach (['admin.seo', 'admin.contact-messages', 'admin.activity-logs', 'admin.users.index', 'admin.users.create', 'admin.site-settings'] as $route) {
        $this->get(route($route))->assertRedirect(route('login'));
    }
    $this->actingAs(phaseFourUser(UserRole::Editor));
    foreach (['admin.activity-logs', 'admin.users.index', 'admin.users.create', 'admin.site-settings'] as $route) {
        $this->get(route($route))->assertForbidden();
    }
    foreach (['admin.seo', 'admin.contact-messages'] as $route) {
        $this->get(route($route))->assertOk();
    }
    foreach ([SettingsEditor::class, Users::class, UserForm::class, ActivityLogs::class] as $component) {
        Livewire::test($component)->assertForbidden();
    }
    Livewire::test(SeoSettings::class)->assertOk()->call('save')->assertForbidden();
    Livewire::test(ContactMessages::class)->assertOk()->call('changeStatus', 'read')->assertForbidden();
});

it('allows admins appropriate modules but denies user and system management', function () {
    $this->actingAs(phaseFourUser(UserRole::Admin));
    foreach (['admin.seo', 'admin.contact-messages'] as $route) {
        $this->get(route($route))->assertOk();
    }
    foreach (['admin.users.index', 'admin.site-settings', 'admin.activity-logs'] as $route) {
        $this->get(route($route))->assertForbidden();
    }
    $this->get(route('admin.users.edit', phaseFourUser(UserRole::Editor)))->assertForbidden();
});

it('blocks inactive users including Super Admins', function () {
    $user = phaseFourUser();
    $user->is_active = false;
    $user->save();
    $this->actingAs($user);
    $this->get(route('admin.dashboard'))->assertForbidden();
    $this->get(route('admin.users.index'))->assertForbidden();
    Livewire::test(SeoSettings::class)->assertForbidden();
});

it('saves and validates SEO with a shared media reference', function () {
    $this->actingAs(phaseFourUser());
    $media = phaseFourMedia();
    Livewire::test(SeoSettings::class)->set('values.seo_title', 'Rexxsol search title')->set('values.seo_description', str_repeat('A', 200))->set('values.canonical_base', 'https://example.org')->call('chooseMedia', 'social_image')->call('selectMedia', $media->id)->call('save')->assertHasNoErrors();
    expect(app(SiteSettings::class)->get('seo.title'))->toBe('Rexxsol search title')->and(app(SiteSettings::class)->get('branding.social_image'))->toBe($media->id);
    Livewire::test(SeoSettings::class)->assertSet('values.seo_title', 'Rexxsol search title')->set('values.canonical_base', 'javascript:alert(1)')->set('values.google_verification', '<meta token>')->set('values.seo_description', str_repeat('x', 256))->call('save')->assertHasErrors(['values.canonical_base', 'values.google_verification', 'values.seo_description']);
    Livewire::test(SeoSettings::class)->set('values.social_image', 9999)->call('save')->assertHasErrors(['values.social_image']);
});

it('keeps sitemap entries private by default and excludes drafts scheduled and deleted content', function () {
    Page::create(['title' => 'Published', 'slug' => 'published', 'status' => 'published']);
    Page::create(['title' => 'Draft', 'slug' => 'draft', 'status' => 'draft']);
    Page::create(['title' => 'Future', 'slug' => 'future', 'status' => 'published', 'published_at' => now()->addDay()]);
    Page::create(['title' => 'Deleted', 'slug' => 'deleted', 'status' => 'published'])->delete();
    Service::create(['name' => 'Service', 'slug' => 'service', 'status' => 'published', 'is_active' => true]);
    Project::create(['name' => 'Project', 'slug' => 'project', 'status' => 'published']);
    BlogPost::create(['title' => 'Post', 'slug' => 'post', 'status' => 'published']);
    expect($this->get('/sitemap.xml')->streamedContent())->not->toContain('<url>');
    $this->get('/robots.txt')->assertSee("User-agent: *\nDisallow:\n")->assertSee(route('sitemap'));
    app(SiteSettings::class)->put(['seo.indexing' => true, 'seo.canonical_base' => 'https://example.org']);
    $xml = $this->get('/sitemap.xml')->assertOk()->streamedContent();
    expect($xml)->toContain('https://example.org/published', '/services/service', '/projects/project', '/blog/post')->not->toContain('/draft', '/future', '/deleted');
    $this->get('/robots.txt')->assertSee('Sitemap: https://example.org/sitemap.xml');
});

it('filters contact messages and records read replied and archive transitions', function () {
    $this->actingAs(phaseFourUser(UserRole::Admin));
    $message = ContactMessage::create(['name' => 'Client Alpha', 'email' => 'alpha@example.org', 'phone' => '+1 (555) 123-4567', 'company' => 'Alpha Co', 'subject' => 'Website inquiry', 'message' => '<script>alert(1)</script>']);
    ContactMessage::create(['name' => 'Client Beta', 'email' => 'beta@example.org', 'subject' => 'Other inquiry', 'message' => 'Details']);
    $test = Livewire::test(ContactMessages::class)->set('search', 'Client Alpha')->assertSee('Client Alpha')->assertDontSee('Client Beta')->call('open', $message->id)->assertSee('Enquiry details')->assertSee('&lt;script&gt;', false)->assertSee('mailto:alpha@example.org')->assertSee('Reply via email')->assertSee('tel:+15551234567')->assertSee('Mark unread')->assertSee('Mark replied')->assertSee('Archive')->assertSee('Archive this message? You can change its status later.')->assertDontSee('Mark read');
    expect($message->fresh()->status)->toBe('read')->and($message->fresh()->read_at)->not->toBeNull();
    $test->call('changeStatus', 'new')->assertSee('Mark read');
    expect($message->fresh()->read_at)->toBeNull();
    $test->call('changeStatus', 'read')->assertSee('Mark unread')->assertDontSee('Mark read');
    expect($message->fresh()->read_at)->not->toBeNull();
    $test->call('changeStatus', 'replied')->assertSee('Replied')->assertSee('Mark unread')->assertSee('Mark read')->assertDontSee('Mark replied');
    expect($message->fresh()->replied_at)->not->toBeNull();
    $test->call('changeStatus', 'archived')->set('status', 'archived')->assertSee('Client Alpha')->assertDontSee('>Archive</button>');
    $test->call('changeStatus', 'new');
    expect($message->fresh()->read_at)->toBeNull();
    $test->call('changeStatus', 'invalid')->assertHasErrors(['status']);
});

it('filters contact messages by email subject date and combined criteria', function () {
    $this->actingAs(phaseFourUser(UserRole::Admin));
    $target = ContactMessage::create(['name' => 'Target Sender', 'email' => 'target@example.org', 'subject' => 'Invoice question', 'message' => 'Please help']);
    $target->status = 'read';
    $target->save();
    $other = ContactMessage::create(['name' => 'Other Sender', 'email' => 'other@example.org', 'subject' => 'Different topic', 'message' => 'Another inquiry']);
    $other->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->saveQuietly();

    Livewire::test(ContactMessages::class)
        ->set('search', 'target@example.org')->assertSee('Target Sender')->assertDontSee('Other Sender')
        ->set('search', 'Invoice question')->assertSee('Target Sender')->assertDontSee('Other Sender')
        ->set('search', 'Target Sender')->set('status', 'read')->set('date', now()->toDateString())->assertSee('Target Sender')->assertDontSee('Other Sender')
        ->set('search', '')->set('status', 'new')->set('date', now()->subDay()->toDateString())->assertDontSee('Target Sender')->assertSee('Other Sender');
});

it('clears active contact filters and returns pagination to the first page', function () {
    $this->actingAs(phaseFourUser(UserRole::Admin));
    foreach (range(1, 17) as $number) {
        ContactMessage::create(['name' => 'Inquiry '.$number, 'email' => 'inquiry'.$number.'@example.org', 'subject' => 'Question '.$number, 'message' => 'Details']);
    }

    Livewire::test(ContactMessages::class)
        ->assertViewHas('items', fn ($items) => $items->count() === 10 && $items->total() === 17 && $items->first()->name === 'Inquiry 17')
        ->call('gotoPage', 2)->assertSee('Inquiry 2')->assertDontSee('Inquiry 17')
        ->set('search', 'no matching inquiry')->set('status', 'replied')->set('date', '2000-01-01')
        ->assertSee('No matching enquiries')->assertSee('No enquiries match your search or selected filters.')->assertSee('Clear filters')
        ->assertSet('search', 'no matching inquiry')->assertSet('status', 'replied')->assertSet('date', '2000-01-01')
        ->call('clearFilters')->assertSet('search', '')->assertSet('status', '')->assertSet('date', '')
        ->assertSee('Inquiry 17')->assertDontSee('No matching enquiries');

});

it('shows the default empty state when there are no contact messages', function () {
    $this->actingAs(phaseFourUser(UserRole::Admin));

    Livewire::test(ContactMessages::class)->assertSee('Your inbox is empty')->assertSee('Website enquiries will appear here when visitors contact you.');
});

it('shows real activity filters without exposing sensitive fields', function () {
    $this->actingAs(phaseFourUser());
    $page = Page::create(['title' => 'Audit Example', 'slug' => 'audit-example']);
    $page->status = 'published';
    $page->save();
    Livewire::test(ActivityLogs::class)->set('module', 'Page')->set('action', 'published')->set('search', 'Audit Example')->assertSee('Audit Example');
    $log = ActivityLog::where('subject_id', $page->id)->where('module', 'Page')->latest('id')->first();
    $log->changes = ['password' => ['old' => 'SECRET_PASSWORD', 'new' => 'SECRET_NEW'], 'status' => ['old' => 'draft', 'new' => 'published']];
    $log->save();
    expect($log->fresh()->safeChanges())->toHaveKey('status');
    Livewire::test(ActivityLogs::class)
        ->call('open', $log->id)
        ->assertSee('Published')
        ->assertDontSee('SECRET_PASSWORD')
        ->assertDontSee('SECRET_NEW')
        ->assertViewHas('items', fn ($items) => ! str_contains(json_encode($items->items()), 'SECRET_PASSWORD') && ! str_contains(json_encode($items->items()), 'SECRET_NEW'))
        ->assertViewHas('selected', fn ($selected) => ! str_contains(json_encode($selected), 'SECRET_PASSWORD')
            && ! str_contains(json_encode($selected), 'SECRET_NEW')
            && ($selected['changes']['status']['old'] ?? null) === 'draft'
            && ($selected['changes']['status']['new'] ?? null) === 'published')
        ->call('close')
        ->assertSet('selectedId', null);
});

it('combines activity filters, supports pagination sizes, preserves filters on reload and clears them', function () {
    $this->actingAs(phaseFourUser());
    $actor = phaseFourUser(UserRole::Admin);
    $existingLogs = ActivityLog::count();

    foreach (range(1, 12) as $index) {
        ActivityLog::create([
            'user_id' => $actor->id,
            'action' => 'updated',
            'module' => 'Project',
            'subject_type' => Page::class,
            'subject_id' => $index,
            'subject_label' => 'Audit record '.$index,
            'changes' => [],
        ]);
    }

    ActivityLog::create([
        'user_id' => $actor->id,
        'action' => 'published',
        'module' => 'Project',
        'subject_type' => Page::class,
        'subject_id' => 99,
        'subject_label' => 'Audit record target',
        'changes' => [],
    ]);

    Livewire::test(ActivityLogs::class)
        ->assertSet('perPage', 10)
        ->assertViewHas('items', fn ($items) => $items->total() === $existingLogs + 13 && $items->count() === 10)
        ->call('nextPage')
        ->assertViewHas('items', fn ($items) => $items->currentPage() === 2 && $items->count() === $existingLogs + 3)
        ->set('perPage', 25)
        ->assertViewHas('items', fn ($items) => $items->currentPage() === 1 && $items->count() === $existingLogs + 13)
        ->set('search', 'Audit record target')
        ->set('actor', $actor->name)
        ->set('module', 'Project')
        ->set('action', 'published')
        ->set('date', now()->toDateString())
        ->assertSee('Audit record target')
        ->assertViewHas('items', fn ($items) => $items->total() === 1)
        ->call('$refresh')
        ->assertSet('actor', $actor->name)
        ->assertSet('module', 'Project')
        ->assertSet('action', 'published')
        ->assertSet('date', now()->toDateString())
        ->assertViewHas('items', fn ($items) => $items->total() === 1)
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('actor', '')
        ->assertSet('module', '')
        ->assertSet('action', '')
        ->assertSet('date', '')
        ->assertSet('perPage', 25)
        ->assertViewHas('items', fn ($items) => $items->currentPage() === 1 && $items->total() === $existingLogs + 13 && $items->count() === $existingLogs + 13);
});

it('keeps deleted records and removed actors readable in the activity log', function () {
    $this->actingAs(phaseFourUser());
    $recordName = str_repeat('Removed team member record ', 8);
    $log = ActivityLog::create([
        'user_id' => null,
        'action' => 'deleted',
        'module' => 'Team Member',
        'subject_type' => \App\Models\TeamMember::class,
        'subject_id' => 80,
        'subject_label' => $recordName,
        'changes' => [],
    ]);

    Livewire::test(ActivityLogs::class)
        ->assertSee('System / removed user')
        ->assertSee('Deleted')
        ->assertSee($recordName)
        ->call('open', $log->id)
        ->assertSee($recordName)
        ->assertSee('No field-level changes were recorded for this action.');
});

it('creates and edits users without logging passwords', function () {
    $this->actingAs(phaseFourUser());
    Livewire::test(UserForm::class)->set(['name' => 'New Editor', 'email' => 'editor@example.org', 'role' => 'editor', 'password' => 'Strong!Password42', 'password_confirmation' => 'Strong!Password42'])->call('save')->assertHasNoErrors();
    $user = User::where('email', 'editor@example.org')->firstOrFail();
    expect(Hash::check('Strong!Password42', $user->password))->toBeTrue()->and($user->role)->toBe(UserRole::Editor->value);
    Livewire::test(UserForm::class, ['user' => $user])->set('role', 'admin')->set('is_active', false)->call('save')->assertHasNoErrors();
    expect($user->fresh()->role)->toBe(UserRole::Admin->value)->and($user->fresh()->is_active)->toBeFalse();
    expect(ActivityLog::all()->toJson())->not->toContain('Strong!Password42', $user->password, 'password_confirmation');
    Livewire::test(Users::class)->set('status', 'inactive')->set('role', 'admin')->assertSee('New Editor');
});

it('validates user creation and unique emails', function () {
    $admin = phaseFourUser();
    $this->actingAs($admin);
    Livewire::test(UserForm::class)->set(['email' => $admin->email, 'role' => 'owner', 'password' => 'weak'])->call('save')->assertHasErrors(['name', 'email', 'role', 'password']);
});

it('prevents self lockout and last Super Admin removal', function () {
    $admin = phaseFourUser();
    $this->actingAs($admin);
    Livewire::test(UserForm::class, ['user' => $admin])->set('role', 'editor')->call('save')->assertHasErrors(['role']);
    Livewire::test(UserForm::class, ['user' => $admin])->set('is_active', false)->call('save')->assertHasErrors(['role']);
    expect($admin->fresh()->is_active)->toBeTrue()->and($admin->fresh()->role)->toBe(UserRole::SuperAdmin->value);
});

it('allows a Super Admin to demote another when an active Super Admin remains', function () {
    $admin = phaseFourUser();
    $other = phaseFourUser();
    $this->actingAs($admin);
    Livewire::test(UserForm::class, ['user' => $other])->set('role', 'editor')->call('save')->assertHasNoErrors();
    expect($other->fresh()->role)->toBe(UserRole::Editor->value);
});

it('saves settings invalidates cache and protects branding media', function () {
    $this->actingAs(phaseFourUser());
    $media = phaseFourMedia();
    $settings = app(SiteSettings::class);
    $settings->all();
    Livewire::test(SettingsEditor::class)->set('values.company_name', 'Rexxsol Updated')->call('chooseMedia', 'logo')->call('selectMedia', $media->id)->call('save')->assertHasNoErrors();
    expect($settings->get('site.company'))->toBe('Rexxsol Updated')->and($settings->get('branding.logo'))->toBe($media->id);
    Livewire::test(SettingsEditor::class)->assertSet('values.company_name', 'Rexxsol Updated')->set('values.linkedin', 'javascript:alert(1)')->set('values.business_email', 'invalid')->set('values.timezone', 'Invalid/Zone')->call('save')->assertHasErrors(['values.linkedin', 'values.business_email', 'values.timezone']);
    expect(fn () => $media->delete())->toThrow(ValidationException::class);
    Livewire::test('pages::admin.media')->call('confirmDelete', $media->id)->call('delete')->assertStatus(422);
    expect(Media::find($media->id))->not->toBeNull();
});

it('keeps edits across settings tabs and opens the section with validation errors', function () {
    $this->actingAs(phaseFourUser());

    $settings = Livewire::test(SettingsEditor::class)
        ->set('values.company_name', 'Edited company')
        ->set('section', 'Contact')
        ->set('values.business_email', 'invalid')
        ->call('save')
        ->assertHasErrors(['values.business_email'])
        ->assertSet('section', 'Contact')
        ->assertSet('values.company_name', 'Edited company')
        ->set('values.business_email', 'support@example.test')
        ->call('save')
        ->assertHasNoErrors();

    expect(app(SiteSettings::class)->get('site.company'))->toBe('Edited company')
        ->and(app(SiteSettings::class)->get('contact.business_email'))->toBe('support@example.test');
});

it('keeps account settings routes separate', function () {
    $this->actingAs(phaseFourUser());
    $this->get('/settings/profile')->assertOk();
    $this->get('/admin/settings')->assertOk();
});

it('protects Super Admins from deletion through the existing profile flow', function () {
    $admin = phaseFourUser();
    $this->actingAs($admin);
    Livewire::test('pages::settings.delete-user-modal')->set('password', 'password')->call('deleteUser')->assertHasErrors(['password']);
    expect(auth()->check())->toBeTrue()->and($admin->fresh())->not->toBeNull();
    expect(fn () => $admin->delete())->toThrow(ValidationException::class);
});

it('requires an explicit canonical base before allowing indexing', function () {
    $this->actingAs(phaseFourUser());
    Livewire::test(SeoSettings::class)->set('values.indexing', true)->call('save')->assertHasErrors(['values.canonical_base']);
});

it('renders redesigned users table with sorting, filtering, and pagination', function () {
    $superAdmin = phaseFourUser(UserRole::SuperAdmin);
    $this->actingAs($superAdmin);

    $editor = User::factory()->create(['name' => 'Alice Editor', 'email' => 'alice@example.com', 'role' => UserRole::Editor, 'is_active' => true]);
    $admin = User::factory()->create(['name' => 'Bob Admin', 'email' => 'bob@example.com', 'role' => UserRole::Admin, 'is_active' => false]);

    Livewire::test(Users::class)
        ->assertSee('Users')
        ->assertSee('Bulk Actions')
        ->assertSee('Filters')
        ->assertSee('Create user')
        ->assertSee('Reload')
        ->assertSee('Alice Editor')
        ->assertSee('Bob Admin')
        ->assertSee('alice@example.com')
        ->assertSee('bob@example.com')
        // Test search
        ->set('search', 'alice@')
        ->assertSee('Alice Editor')
        ->assertDontSee('Bob Admin')
        ->set('search', '')
        // Test role filter
        ->set('role', UserRole::Admin->value)
        ->assertSee('Bob Admin')
        ->assertDontSee('Alice Editor')
        ->set('role', '')
        // Test status filter
        ->set('status', 'inactive')
        ->assertSee('Bob Admin')
        ->assertDontSee('Alice Editor')
        // Test clear filters
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('role', '')
        ->assertSet('status', '')
        ->assertSee('Alice Editor')
        ->assertSee('Bob Admin')
        // Test sorting
        ->call('sortBy', 'name')
        ->assertSet('sortField', 'name')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'desc');
});

it('supports bulk selection and activation/deactivation with safeguards', function () {
    $superAdmin = phaseFourUser(UserRole::SuperAdmin);
    $this->actingAs($superAdmin);

    $user1 = User::factory()->create(['role' => UserRole::Editor, 'is_active' => true]);
    $user2 = User::factory()->create(['role' => UserRole::Editor, 'is_active' => false]);

    $test = Livewire::test(Users::class)
        ->call('toggleSelected', $user1->id)
        ->assertSet('selected', [$user1->id])
        ->call('toggleSelected', $user2->id)
        ->assertSet('selected', [$user1->id, $user2->id])
        // Bulk activate
        ->call('applyBulkAction', 'activate')
        ->assertSet('selected', []);

    expect($user1->fresh()->is_active)->toBeTrue()
        ->and($user2->fresh()->is_active)->toBeTrue();

    // Bulk deactivate
    $test->call('toggleSelected', $user1->id)
        ->call('toggleSelected', $user2->id)
        ->call('applyBulkAction', 'deactivate')
        ->assertSet('selected', []);

    expect($user1->fresh()->is_active)->toBeFalse()
        ->and($user2->fresh()->is_active)->toBeFalse();

    // Safeguard: Cannot deactivate own account
    $test->call('toggleSelected', $superAdmin->id)
        ->call('applyBulkAction', 'deactivate');
    expect($superAdmin->fresh()->is_active)->toBeTrue();
});

it('renders and manages all Global Settings tabs and preserves state across tab changes', function () {
    $superAdmin = phaseFourUser(UserRole::SuperAdmin);
    $this->actingAs($superAdmin);

    $test = Livewire::test(SettingsEditor::class)
        ->assertSee('Global Settings')
        ->assertSee('Manage your website identity, branding, and preferences.')
        // Check tab bar
        ->assertSee('General')
        ->assertSee('Branding')
        ->assertSee('Contact')
        ->assertSee('Social')
        ->assertSee('Website')
        ->assertSee('Maintenance')
        ->assertSee('Header')
        ->assertSee('Footer')
        // General tab fields
        ->assertSee('Site name')
        ->assertSee('Company name')
        ->assertSee('Site tagline')
        ->assertSee('Company description')
        ->assertSee('Timezone')
        ->assertSee('Default language')
        // Set values on General tab
        ->set('values.site_name', 'Rexxsol Global')
        ->set('values.tagline', 'Engineering Excellence')
        // Switch to Contact tab
        ->set('section', 'Contact')
        ->assertSee('Business email')
        ->assertSee('Support email')
        ->assertSee('Physical office address')
        ->set('values.business_email', 'ops@rexxsol.com')
        // Switch to Social tab
        ->set('section', 'Social')
        ->assertSee('Facebook')
        ->assertSee('LinkedIn')
        ->assertSee('X / Twitter')
        ->set('values.twitter', 'https://x.com/rexxsol')
        // Switch back to General and confirm unsaved values are preserved
        ->set('section', 'General')
        ->assertSet('values.site_name', 'Rexxsol Global')
        ->assertSet('values.tagline', 'Engineering Excellence')
        ->assertSet('values.business_email', 'ops@rexxsol.com')
        ->assertSet('values.twitter', 'https://x.com/rexxsol')
        // Switch to Maintenance tab and check toggles
        ->set('section', 'Maintenance')
        ->assertSee('Public Website Maintenance Mode')
        ->assertSee('Maintenance title')
        ->assertSee('Maintenance message')
        // Switch to Header & Footer tabs
        ->set('section', 'Header')
        ->assertSee('Sticky Header Navigation')
        ->assertSee('Header CTA label')
        ->set('section', 'Footer')
        ->assertSee('Footer Call-to-Action Banner')
        ->assertSee('Footer CTA eyebrow')
        // Save and verify persistence
        ->call('save')
        ->assertHasNoErrors();

    $settings = app(\App\Support\SiteSettings::class);
    expect($settings->get('site.name'))->toBe('Rexxsol Global')
        ->and($settings->get('site.tagline'))->toBe('Engineering Excellence')
        ->and($settings->get('contact.business_email'))->toBe('ops@rexxsol.com')
        ->and($settings->get('social.twitter'))->toBe('https://x.com/rexxsol');
});

it('automatically switches to the error tab when validation fails on a hidden section', function () {
    $superAdmin = phaseFourUser(UserRole::SuperAdmin);
    $this->actingAs($superAdmin);

    Livewire::test(SettingsEditor::class)
        ->set('section', 'General')
        // Set invalid URL in social section
        ->set('values.linkedin', 'invalid-url')
        ->call('save')
        ->assertHasErrors(['values.linkedin'])
        // Component automatically opened Social section!
        ->assertSet('section', 'Social');
});
