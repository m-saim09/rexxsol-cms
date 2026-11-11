<?php

use App\Enums\UserRole;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Page;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\MediaReferenceBackfill;
use App\Support\RichText;
use App\Support\ContentImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function phaseThreeAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

it('protects Phase 3 routes from guests', function () {
    foreach (['team', 'testimonials', 'faqs'] as $resource) {
        $this->get(route("admin.{$resource}.index"))->assertRedirect(route('login'));
    }
    $this->get(route('admin.media.index'))->assertRedirect(route('login'));
});

it('creates and validates team members', function () {
    actingAs(phaseThreeAdmin());
    Livewire::test('pages::admin.phase-three-form', ['resource' => 'team'])
        ->set(['name' => 'Amina Cole', 'designation' => 'Delivery Lead'])
        ->call('saveDraft')->assertHasNoErrors();
    expect(TeamMember::first()->name)->toBe('Amina Cole');
    Livewire::test('pages::admin.phase-three-form', ['resource' => 'team'])
        ->call('saveDraft')->assertHasErrors(['name', 'designation']);
});

it('creates testimonials and validates rating', function () {
    actingAs(phaseThreeAdmin());
    Livewire::test('pages::admin.phase-three-form', ['resource' => 'testimonials'])
        ->set(['name' => 'Jordan Lee', 'quote' => 'Excellent delivery.', 'rating' => 6])
        ->call('saveDraft')->assertHasErrors(['rating']);
    Livewire::test('pages::admin.phase-three-form', ['resource' => 'testimonials'])
        ->set(['name' => 'Jordan Lee', 'quote' => 'Excellent delivery.', 'rating' => 5])
        ->call('saveDraft')->assertHasNoErrors();
    expect(Testimonial::count())->toBe(1);
});

it('creates, filters, publishes, and deletes FAQs', function () {
    actingAs(phaseThreeAdmin());
    Livewire::test('pages::admin.phase-three-form', ['resource' => 'faqs'])
        ->set(['question' => 'What is Rexxsol?', 'answer' => 'A digital partner.', 'category' => 'General'])
        ->call('saveDraft')->assertHasNoErrors();
    $faq = Faq::first();
    Livewire::test('pages::admin.phase-three-list', ['resource' => 'faqs'])
        ->set('search', 'Rexxsol')->call('togglePublish', $faq->id)->call('confirmDelete', $faq->id)->call('delete');
    expect(Faq::withTrashed()->find($faq->id)->status)->toBe('published')
        ->and(Faq::withTrashed()->find($faq->id)->deleted_at)->not->toBeNull();
});

it('uploads, edits, and safely deletes media', function () {
    Storage::fake('public');
    actingAs(phaseThreeAdmin());
    Livewire::test('pages::admin.media')
        ->set('uploads', [UploadedFile::fake()->image('hero.png')]);
    $media = Media::first();
    expect($media->mime_type)->toStartWith('image/');
    expect($media->width)->toBe(10)->and($media->height)->toBe(10);
    Livewire::test('pages::admin.media')->call('select', $media->id)->set(['title' => 'Hero', 'alt_text' => 'Rexxsol team'])->call('saveMetadata');
    expect($media->fresh()->title)->toBe('Hero');
    TeamMember::create(['name' => 'Used', 'slug' => 'used', 'designation' => 'Lead', 'profile_image' => $media->path, 'status' => 'draft']);
    Livewire::test('pages::admin.media')->call('confirmDelete', $media->id)->call('delete')->assertStatus(422);
    TeamMember::withTrashed()->forceDelete();
    Livewire::test('pages::admin.media')->call('confirmDelete', $media->id)->call('delete');
    expect(Media::count())->toBe(0);
});

it('creates readable titles, alt-text guidance, and source metadata for uploaded media', function () {
    Storage::fake('public');
    actingAs(phaseThreeAdmin());

    $named = Media::fromUpload(UploadedFile::fake()->image('rexxsol-office-team.jpg', 80, 60), [
        'uploaded_by' => auth()->id(),
        'disk' => 'public',
        'source_module' => 'Team',
        'source_field' => 'Profile photo',
    ]);

    expect($named->title)->toBe('Rexxsol office team')
        ->and($named->title_source)->toBe('filename')
        ->and($named->alt_text)->toBe('Rexxsol office team')
        ->and($named->source_module)->toBe('Team');

    $random = Media::fromUpload(UploadedFile::fake()->image('IMG_4821.png', 120, 90), [
        'uploaded_by' => auth()->id(),
        'disk' => 'public',
    ]);

    expect($random->alt_text)->toBeNull()
        ->and($random->alt_text_status)->toBe('needs_description');

    $randomStorageName = Media::fromUpload(UploadedFile::fake()->image('NtvzFLJQARDYdntNCDU1TAb1RzXpFmpUs6ThZCYu.jpg', 90, 70), [
        'uploaded_by' => auth()->id(),
        'disk' => 'public',
    ]);
    expect($randomStorageName->title)->toBeNull()
        ->and($randomStorageName->alt_text_status)->toBe('needs_description');

    $named->update([
        'title' => 'Leadership team',
        'title_source' => 'manual',
        'alt_text' => 'Our leadership team',
        'alt_text_source' => 'manual',
        'alt_text_status' => 'reviewed',
    ]);

    expect($named->fresh()->title)->toBe('Leadership team')
        ->and($named->fresh()->alt_text)->toBe('Our leadership team');
});

it('does not turn an unchanged filename alt suggestion into reviewed text when only the title changes', function () {
    Storage::fake('public');
    actingAs(phaseThreeAdmin());
    $media = Media::fromUpload(UploadedFile::fake()->image('Rexxsol-office.jpg'), ['uploaded_by' => auth()->id(), 'disk' => 'public']);

    Livewire::test('pages::admin.media')
        ->call('select', $media->id)
        ->set('title', 'Office team')
        ->call('saveMetadata');

    expect($media->fresh()->title)->toBe('Office team')
        ->and($media->fresh()->alt_text)->toBe('Rexxsol office')
        ->and($media->fresh()->alt_text_source)->toBe('suggested')
        ->and($media->fresh()->alt_text_status)->toBe('suggested');
});

it('renders library alt defaults for inline images while preserving intentional empty alt values', function () {
    Storage::fake('public');
    $media = Media::fromUpload(UploadedFile::fake()->image('Rexxsol-office.png', 60, 40), ['disk' => 'public']);
    $url = Storage::disk('public')->url($media->path);

    $html = RichText::clean('<img src="'.$url.'"><img src="'.$url.'" alt="">');

    expect($html)->toContain('alt="Rexxsol office"')
        ->and($html)->toContain('alt=""');
});

it('does not invent public alt text when an image needs a description', function () {
    Storage::fake('public');
    $media = Media::fromUpload(UploadedFile::fake()->image('IMG_4821.png', 40, 30), ['disk' => 'public']);

    expect(ContentImage::alt($media->path, null, 'About page title'))->toBe('')
        ->and(ContentImage::alt($media->path, '', 'About page title'))->toBe('');
});

it('keeps upload source and usage tracking for reused media', function () {
    Storage::fake('public');
    actingAs(phaseThreeAdmin());

    $media = Media::fromUpload(UploadedFile::fake()->image('team-photo.jpg'), [
        'uploaded_by' => auth()->id(),
        'disk' => 'public',
        'source_module' => 'Team',
        'source_field' => 'Profile photo',
    ]);

    $member = TeamMember::create([
        'name' => 'Ali Khan',
        'slug' => 'ali-khan',
        'designation' => 'Lead',
        'profile_image' => $media->path,
        'status' => 'draft',
    ]);

    $media->refresh();
    $media->update([
        'source_record_type' => TeamMember::class,
        'source_record_id' => $member->id,
        'source_record_name' => $member->name,
        'source_label' => 'Profile photo',
    ]);

    expect($media->sourceLabel())->toContain('Team')
        ->and($media->currentUsage())->not->toBeEmpty();
});

it('safely compares and repeatably registers referenced legacy images', function () {
    Storage::fake('public');
    $legacy = UploadedFile::fake()->image('rexxsol-office-team.jpg', 80, 60);
    $path = $legacy->storeAs('legacy', 'rexxsol-office-team.jpg', 'public');
    Page::create([
        'title' => 'About Rexxsol',
        'slug' => 'about-rexxsol',
        'status' => 'draft',
        'featured_image' => $path,
    ]);

    $backfill = app(MediaReferenceBackfill::class);
    $preview = $backfill->run();
    expect($preview['references'])->toBe(1)
        ->and($preview['created'])->toBe(0)
        ->and($preview['would_register'])->toHaveCount(1)
        ->and(Media::count())->toBe(0);

    $applied = $backfill->run(true);
    $media = Media::where('path', $path)->firstOrFail();
    expect($applied['created'])->toBe(1)
        ->and($media->title)->toBe('Rexxsol office team')
        ->and($media->source_module)->toBeNull()
        ->and($media->uploaded_at)->toBeNull();

    $media->update(['title' => 'Reviewed image title', 'alt_text' => 'Reviewed description']);
    $rerun = $backfill->run(true);
    expect($rerun['created'])->toBe(0)
        ->and($rerun['registered'])->toBe(1)
        ->and($media->fresh()->title)->toBe('Reviewed image title')
        ->and($media->fresh()->alt_text)->toBe('Reviewed description');
});

it('reuses inline article images and records pending upload provenance after article save', function () {
    Storage::fake('public');
    actingAs(phaseThreeAdmin());

    $existing = Media::fromUpload(UploadedFile::fake()->image('shared-office.png'), [
        'uploaded_by' => auth()->id(),
        'disk' => 'public',
        'source_module' => 'Settings',
        'source_field' => 'Site logo',
    ]);
    $editor = Livewire::test('pages::admin.content-form', ['resource' => 'blog'])
        ->call('openMediaPicker', 'inline')
        ->set('inlineImageAltMode', 'decorative')
        ->call('selectFeaturedMedia', $existing->id);

    expect(Media::count())->toBe(1)
        ->and($editor->get('content'))->toContain('alt=""')
        ->and($existing->fresh()->source_module)->toBe('Settings');

    $editor->set('title', 'A Rexxsol update')
        ->set('slug', 'a-rexxsol-update')
        ->set('inlineImageAltMode', 'default')
        ->set('inlineImageUpload', UploadedFile::fake()->image('team-at-work.png', 20, 15))
        ->call('uploadInlineImage')
        ->call('saveDraft')
        ->assertHasNoErrors();

    $post = \App\Models\BlogPost::where('slug', 'a-rexxsol-update')->firstOrFail();
    $newMedia = Media::where('source_module', 'Blog')->firstOrFail();
    expect(Media::count())->toBe(2)
        ->and($post->content)->toContain('src="')
        ->and($newMedia->source_record_id)->toBe($post->id)
        ->and($newMedia->source_record_type)->toBe(\App\Models\BlogPost::class);
});

it('registers distinct legacy paths that share file content without duplicating uploads', function () {
    Storage::fake('public');
    actingAs(phaseThreeAdmin());
    $source = Media::fromUpload(UploadedFile::fake()->image('known-image.png', 30, 20), [
        'uploaded_by' => auth()->id(),
        'disk' => 'public',
    ]);
    $legacyPath = 'legacy/copied-image.png';
    Storage::disk('public')->copy($source->path, $legacyPath);
    Page::create(['title' => 'Legacy use', 'slug' => 'legacy-use', 'featured_image' => $legacyPath]);

    $report = app(MediaReferenceBackfill::class)->run(true);
    $legacy = Media::where('path', $legacyPath)->firstOrFail();
    expect($report['created'])->toBe(1)
        ->and($report['checksum_shared'])->toHaveCount(1)
        ->and($legacy->checksum)->toBeNull()
        ->and($legacy->currentUsage())->toHaveCount(1);

    $rerun = app(MediaReferenceBackfill::class)->run(true);
    expect($rerun['created'])->toBe(0)
        ->and($rerun['registered'])->toBe(1)
        ->and(Media::count())->toBe(2);
});

it('rejects media actions for users without media permissions', function () {
    Storage::fake('public');
    $media = Media::fromUpload(UploadedFile::fake()->image('restricted-media.png'), ['disk' => 'public']);
    $role = Role::create(['slug' => 'media-restricted', 'name' => 'Media Restricted']);
    $user = User::factory()->create(['role' => $role->slug]);
    actingAs($user);

    Livewire::test('pages::admin.media')->call('openUpload')->assertForbidden();
    Livewire::test('pages::admin.media')->call('select', $media->id)->assertForbidden();
    Livewire::test('pages::admin.media')->call('confirmDelete', $media->id)->assertForbidden();
    expect(Media::count())->toBe(1);
});
