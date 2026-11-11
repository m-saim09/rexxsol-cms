<?php

use App\Support\CmsContent;
use App\Models\BlogCategory;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Support\ContentImage;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin')] #[Title('Edit content')] class extends Component {
    use WithFileUploads;

    public string $resource = 'pages';
    public ?int $modelId = null;
    public string $title = '';
    public string $slug = '';
    public string $excerpt = '';
    public string $content = '';
    public string $short_description = '';
    public string $description = '';
    public string $icon = '';
    public string $client = '';
    public string $category = '';
    public ?int $author_id = null;
    public string $technologies = '';
    public string $tags = '';
    public string $project_url = '';
    public string $completion_date = '';
    public string $seo_title = '';
    public string $seo_description = '';
    public string $seo_keywords = '';
    public string $canonical_url = '';
    public bool $noindex = false;
    public bool $nofollow = false;
    public bool $show_in_navbar = true;
    public bool $show_in_footer = true;
    public string $navigation_label = '';
    public int $menu_order = 0;
    public string $status = 'draft';
    public $published_at;
    public string $featured_image_alt = '';
    public string $hero_eyebrow = '';
    public string $hero_heading = '';
    public string $hero_description = '';
    public string $contact_info_heading = '';
    public string $contact_info_description = '';
    public string $contact_form_heading = '';
    public string $contact_form_description = '';
    public ?int $selectedMediaId = null;
    public string $mediaSearch = '';
    public bool $showMediaPicker = false;
    public int $sort_order = 0;
    public bool $is_active = true;
    public ?string $featured_image = null;
    public bool $featuredImageRemoved = false;
    public $featuredImageUpload;
    public array $gallery = [];
    public array $galleryUploads = [];

    public function mount(string $resource, ?string $model = null): void
    {
        abort_unless(in_array($resource, ['pages', 'services', 'projects', 'blog'], true), 404);
        $this->resource = $resource;
        if ($model) $this->fillFromModel(CmsContent::model($resource)::query()->findOrFail($model));
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->modelId || $this->slug === Str::slug($this->title)) $this->slug = Str::slug($value);
    }

    protected function rules(bool $publishing = false): array
    {
        $model = CmsContent::model($this->resource);
        $table = (new $model)->getTable();
        $slugRules = ['required', 'string', 'max:255'];
        if ($this->resource === 'pages') {
            $slugRules[] = 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/';
            $systemPage = $this->modelId
                ? Page::query()->whereKey($this->modelId)->where('is_system', true)->first()
                : null;
            if (! $systemPage || $this->slug !== $systemPage->slug) {
                $slugRules[] = Rule::notIn($this->reservedSlugs());
            }
        }
        $slugRules[] = Rule::unique($table, 'slug')->ignore($this->modelId);

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'status' => ['required', Rule::in(['draft', 'published'])],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'client' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'url', 'max:500'],
            'completion_date' => ['nullable', 'date'],
            'technologies' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:500'],
            'noindex' => ['boolean'],
            'nofollow' => ['boolean'],
            'show_in_navbar' => ['boolean'],
            'show_in_footer' => ['boolean'],
            'navigation_label' => ['nullable', 'string', 'max:255'],
            'menu_order' => ['integer', 'min:0', 'max:9999'],
            'featuredImageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'galleryUploads.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
        ];
        if ($this->resource === 'pages' && $this->slug === 'contact') {
            $rules += [
                'hero_eyebrow' => ['nullable', 'string', 'max:100'],
                'hero_heading' => ['nullable', 'string', 'max:255'],
                'hero_description' => ['nullable', 'string', 'max:1000'],
                'contact_info_heading' => ['nullable', 'string', 'max:255'],
                'contact_info_description' => ['nullable', 'string', 'max:1000'],
                'contact_form_heading' => ['nullable', 'string', 'max:255'],
                'contact_form_description' => ['nullable', 'string', 'max:1000'],
            ];
        }
        if ($this->resource === 'blog') {
            $rules['author_id'] = ['nullable', 'integer', Rule::exists('users', 'id')];
            $rules['published_at'] = ['nullable', 'date'];
            $rules['selectedMediaId'] = ['nullable', 'integer', Rule::exists('media', 'id')->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])];
        }

        return $rules;
    }

    public function saveDraft(): void { $this->save(false); }
    public function saveChanges(): void { $this->save($this->status === 'published'); }
    public function saveAndExit(): void { $this->save($this->status === 'published', true); }
    public function publish(): void { abort_unless(auth()->user()->can('publish-cms-content'), 403); $this->save(true, false, true); }

    public function removeFeaturedImage(): void
    {
        abort_unless(auth()->user()->can('access-admin'), 403);
        $this->featuredImageRemoved = true;
        $this->featured_image = null;
        $this->featuredImageUpload = null;
        $this->selectedMediaId = null;
        $this->featured_image_alt = '';
    }

    public function openMediaPicker(): void
    {
        abort_unless(auth()->user()->can('manage-media'), 403);
        $this->showMediaPicker = true;
        $this->mediaSearch = '';
    }

    public function closeMediaPicker(): void
    {
        $this->showMediaPicker = false;
    }

    public function selectFeaturedMedia(int $id): void
    {
        abort_unless(auth()->user()->can('manage-media'), 403);
        $media = Media::query()->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])->findOrFail($id);
        $this->selectedMediaId = $media->id;
        $this->featured_image = Media::normalizePath($media->path);
        $this->featured_image_alt = $media->alt_text ?? '';
        $this->featuredImageUpload = null;
        $this->featuredImageRemoved = false;
        $this->showMediaPicker = false;
    }

    protected function save(bool $publish, bool $exit = false, bool $republish = false): void
    {
        if ($publish) abort_unless(auth()->user()->can('publish-cms-content'), 403);
        try {
            $this->validate($this->rules($publish));
        } catch (ValidationException $exception) {
            Flux::toast(
                text: 'Some fields need your attention before saving.',
                heading: 'Please check the form',
                duration: 6500,
                variant: 'warning',
            );

            throw $exception;
        }
        $modelClass = CmsContent::model($this->resource);
        $model = $this->modelId ? $modelClass::query()->findOrFail($this->modelId) : new $modelClass;
        $wasNew = ! $model->exists;
        $oldStatus = $model->status;
        $oldTitle = (string) ($model->title ?? '');
        $oldMenuOrder = (int) ($model->menu_order ?? 0);
        $navigationChanged = $this->resource === 'pages' && (
            (bool) $model->show_in_navbar !== $this->show_in_navbar
            || (bool) $model->show_in_footer !== $this->show_in_footer
            || (string) ($model->navigation_label ?? '') !== $this->navigation_label
            || (int) ($model->menu_order ?? 0) !== $this->menu_order
            || (trim((string) $model->navigation_label) === '' && $oldTitle !== $this->title)
        );
        $model->fill($this->payload());
        $model->status = $publish ? 'published' : 'draft';
        $model->published_at = $publish
            ? ($this->resource === 'blog' && $this->published_at ? \Illuminate\Support\Carbon::parse($this->published_at) : ($model->published_at ?: now()))
            : null;
        if ($this->resource === 'services') $model->is_active = $publish;
        if ($this->resource === 'blog' && $this->author_id) $model->author_id = $this->author_id;
        if ($this->resource === 'blog' && ! $model->author_id) $model->author_id = auth()->id();
        $oldFeaturedImage = $model->featured_image;
        $imageUploaded = false;
        $imageRemoved = false;
        if ($this->featuredImageUpload) {
            $path = $this->featuredImageUpload->store('cms/media', 'public');
            $dimensions = @getimagesize($this->featuredImageUpload->getRealPath()) ?: [];
            $media = Media::create([
                'uploaded_by' => auth()->id(),
                'disk' => 'public',
                'path' => $path,
                'original_name' => $this->featuredImageUpload->getClientOriginalName(),
                'file_name' => basename($path),
                'mime_type' => $this->featuredImageUpload->getMimeType(),
                'extension' => strtolower($this->featuredImageUpload->getClientOriginalExtension()),
                'size' => $this->featuredImageUpload->getSize(),
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
                'alt_text' => $this->featured_image_alt ?: null,
                'title' => $this->title,
            ]);
            $this->selectedMediaId = $media->id;
            $model->featured_image = $path;
            $imageUploaded = true;
        } elseif ($this->resource === 'blog' && $this->selectedMediaId) {
            $media = Media::query()->where('disk', 'public')->findOrFail($this->selectedMediaId);
            $model->featured_image = Media::normalizePath($media->path);
            $model->featured_image_alt = $this->featured_image_alt ?: ($media->alt_text ?? null);
        } elseif ($this->featuredImageRemoved) {
            $model->featured_image = null;
            $imageRemoved = true;
        }
        if ($this->resource === 'blog') {
            $model->featured_image_alt = $model->featured_image ? (trim($this->featured_image_alt) ?: null) : null;
        }
        if ($this->resource === 'projects' && $this->galleryUploads) {
            $model->gallery = array_merge($model->gallery ?? [], collect($this->galleryUploads)->map(fn ($file) => $file->store('cms/gallery', 'public'))->all());
        }
        $model->save();
        if ($this->resource === 'pages' && $publish && ($republish || $wasNew || $oldStatus !== 'published' || $navigationChanged)) {
            app(\App\Support\PageNavigationSync::class)->sync($model, $oldMenuOrder !== $this->menu_order);
        }
        if ($this->resource === 'blog') {
            $categoryNames = collect(explode(',', $this->category))->map(fn (string $name) => trim($name))->filter()->values();
            $categoryIds = $categoryNames->map(fn (string $name) => \App\Models\BlogCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)->all();
            $tagIds = collect($this->csv($this->tags))->map(fn (string $name) => \App\Models\BlogTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)->all();
            $model->categories()->sync($categoryIds);
            $model->tagsRelation()->sync($tagIds);
        }
        if ($this->resource !== 'blog' && $oldFeaturedImage && $oldFeaturedImage !== $model->featured_image) Storage::disk('public')->delete($oldFeaturedImage);
        $this->featuredImageRemoved = false;
        $this->featuredImageUpload = null;
        $this->modelId = $model->id;
        if (! $exit) {
            Flux::toast(
                text: $imageUploaded ? 'The selected image has been added and your changes have been saved.' : ($imageRemoved ? 'The featured image has been removed.' : 'Your changes have been saved successfully.'),
                heading: $imageUploaded ? 'Image uploaded' : ($imageRemoved ? 'Image removed' : ($publish ? 'Page published' : ($model->wasChanged('status') && $model->status === 'draft' ? 'Moved to draft' : 'Page saved'))),
                duration: 4000,
                variant: 'success',
            );
        }
        if ($exit) {
            session()->flash('cms_toast', [
                'heading' => $imageUploaded ? 'Image uploaded' : ($imageRemoved ? 'Image removed' : ($publish ? 'Page published' : ($model->wasChanged('status') ? 'Moved to draft' : 'Page saved'))),
                'text' => $imageUploaded ? 'The selected image has been added and your changes have been saved.' : ($imageRemoved ? 'The featured image has been removed.' : 'Changes saved successfully. Returning to Pages.'),
                'variant' => 'success',
                'duration' => 4000,
            ]);
            $this->redirectRoute("admin.{$this->resource}.index", navigate: true);
        } elseif ($this->resource !== 'pages') {
            $this->redirectRoute("admin.{$this->resource}.edit", ['model' => $model->id], navigate: true);
        }
    }

    protected function payload(): array
    {
        $payload = ['title' => $this->title, 'name' => $this->title, 'slug' => $this->slug, 'status' => $this->status, 'published_at' => null, 'excerpt' => $this->excerpt, 'content' => $this->content, 'short_description' => $this->short_description, 'description' => $this->description, 'icon' => $this->icon, 'client' => $this->client, 'category' => $this->category, 'project_url' => $this->project_url ?: null, 'completion_date' => $this->completion_date ?: null, 'sort_order' => $this->sort_order, 'seo_title' => $this->seo_title, 'seo_description' => $this->seo_description, 'seo_keywords' => $this->seo_keywords, 'is_active' => $this->is_active];
        $payload['canonical_url'] = $this->canonical_url ?: null;
        $payload['noindex'] = $this->noindex;
        $payload['nofollow'] = $this->nofollow;
        if ($this->resource === 'pages') {
            $payload['show_in_navbar'] = $this->show_in_navbar;
            $payload['show_in_footer'] = $this->show_in_footer;
            $payload['navigation_label'] = trim($this->navigation_label) ?: null;
            $payload['menu_order'] = $this->menu_order;
            if ($this->slug === 'contact') {
                foreach (['hero_eyebrow', 'hero_heading', 'hero_description', 'contact_info_heading', 'contact_info_description', 'contact_form_heading', 'contact_form_description'] as $field) {
                    $payload[$field] = trim($this->{$field}) ?: null;
                }
            }
        }
        if ($this->resource === 'blog') {
            $payload['author_id'] = $this->author_id;
            $payload['featured_image_alt'] = trim($this->featured_image_alt) ?: null;
        }
        if ($this->resource === 'services') unset($payload['published_at']);
        if ($this->resource === 'projects') $payload['technologies'] = $this->csv($this->technologies);
        if ($this->resource === 'blog') $payload['tags'] = $this->csv($this->tags);
        return array_filter($payload, fn ($value) => $value !== null || in_array($this->resource, ['pages', 'services', 'projects', 'blog'], true));
    }

    protected function csv(string $value): array { return collect(explode(',', $value))->map(fn (string $item) => trim($item))->filter()->values()->all(); }

    protected function fillFromModel($model): void
    {
        $this->modelId = $model->id; $this->title = $model->title ?? $model->name; $this->slug = $model->slug; $this->status = $model->status ?? ($model->is_active ? 'published' : 'draft'); $this->published_at = $model->published_at;
        foreach (['excerpt', 'content', 'short_description', 'description', 'icon', 'client', 'category', 'project_url', 'seo_title', 'seo_description', 'seo_keywords', 'canonical_url', 'noindex', 'nofollow', 'sort_order', 'is_active', 'featured_image', 'featured_image_alt', 'show_in_navbar', 'show_in_footer', 'navigation_label', 'menu_order', 'author_id', 'hero_eyebrow', 'hero_heading', 'hero_description', 'contact_info_heading', 'contact_info_description', 'contact_form_heading', 'contact_form_description'] as $field) if (isset($model->{$field})) $this->{$field} = $model->{$field};
        $this->completion_date = $model->completion_date?->format('Y-m-d') ?? '';
        $this->technologies = implode(', ', $model->technologies ?? []); $this->tags = implode(', ', $model->tags ?? []); $this->gallery = $model->gallery ?? [];
    }

    public function getHeadingProperty(): string { return ($this->modelId ? 'Edit ' : 'Create ').CmsContent::singular($this->resource); }
    public function getBackRouteProperty(): string { return route("admin.{$this->resource}.index"); }

    public function getMediaItemsProperty()
    {
        return Media::query()
            ->where('disk', 'public')
            ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->when($this->mediaSearch, fn ($query) => $query->where(fn ($search) => $search->where('original_name', 'like', '%'.$this->mediaSearch.'%')->orWhere('title', 'like', '%'.$this->mediaSearch.'%')))
            ->latest()
            ->limit(18)
            ->get();
    }

    protected function reservedSlugs(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route): string => trim($route->uri(), '/'))
            ->filter(fn (string $uri): bool => $uri !== '' && ! str_contains($uri, '/') && ! str_contains($uri, '{'))
            ->merge(['api', 'admin', 'settings', 'theme-preview', 'login', 'logout', 'register', 'password', 'sitemap.xml', 'robots.txt', 'custom.css', 'home', 'about', 'services', 'portfolio', 'blog', 'contact'])
            ->unique()
            ->values()
            ->all();
    }
}; ?>

<div class="rex-content-form-root">
@if ($resource === 'pages')
    <div class="rex-page-edit-page mx-auto w-full max-w-[1440px] space-y-5">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Dashboard</a>
            <span>/</span>
            <a href="{{ $this->backRoute }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Pages</a>
            <span>/</span>
            <span class="text-[#172033]">{{ $modelId ? 'Edit' : 'Create' }} {{ $title ?: 'Page' }}</span>
        </nav>

        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#2563eb]">Detail</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#172033]">{{ $modelId ? 'Edit Page' : 'Create Page' }}</h1>
                <p class="mt-1 text-sm text-[#64748b]">Update the page content and publishing settings.</p>
            </div>
            <span class="rex-page-edit-state {{ $status === 'published' ? 'rex-page-edit-state--published' : 'rex-page-edit-state--draft' }}">{{ ucfirst($status) }}</span>
        </div>

        <form wire:submit.prevent="saveChanges" class="rex-page-edit-grid" style="display:grid;grid-template-columns:minmax(0,3fr) minmax(260px,1fr);gap:16px;align-items:start">
            <main class="space-y-4">
                <section class="rex-page-edit-card">
                    <div class="rex-page-edit-card-heading"><div><h2>Page details</h2><p>Set the public title, URL and page content.</p></div></div>
                    <div class="rex-page-edit-fields">
                        <div>
                            <label for="page-title">Page name <span>*</span></label>
                            <input id="page-title" wire:model.live="title" type="text" value="{{ $title }}" placeholder="About Rexxsol" />
                            @error('title')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="page-slug">Permalink / slug <span>*</span></label>
                            <div class="rex-page-edit-slug"><span>{{ url('/') }}/</span><input id="page-slug" wire:model="slug" type="text" value="{{ $slug }}" placeholder="about" /></div>
                            <p class="rex-page-edit-help">Preview: {{ url('/'.ltrim($slug, '/')) }}</p>
                            @error('slug')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="page-excerpt">Description</label>
                            <textarea id="page-excerpt" wire:model="excerpt" rows="3" placeholder="A short description for cards, search and previews.">{{ $excerpt }}</textarea>
                            @error('excerpt')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <div class="rex-page-edit-editor-label"><label for="page-content">Content</label><span>HTML/text supported</span></div>
                            <textarea id="page-content" wire:model="content" rows="18" class="rex-page-edit-content" placeholder="Write your page content here...">{{ $content }}</textarea>
                            @error('content')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                @if ($resource === 'pages' && $slug === 'contact')
                    <section class="rex-page-edit-card">
                        <div class="rex-page-edit-card-heading"><div><h2>Contact page content</h2><p>Customize the Contact page hero, contact details heading, and enquiry form introduction.</p></div></div>
                        <div class="rex-page-edit-fields">
                            <div><label for="contact-hero-eyebrow">Hero eyebrow</label><input id="contact-hero-eyebrow" wire:model="hero_eyebrow" type="text" maxlength="100" />@error('hero_eyebrow')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                            <div><label for="contact-hero-heading">Hero heading</label><input id="contact-hero-heading" wire:model="hero_heading" type="text" maxlength="255" />@error('hero_heading')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                            <div><label for="contact-hero-description">Hero description</label><textarea id="contact-hero-description" wire:model="hero_description" rows="2" maxlength="1000"></textarea>@error('hero_description')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                            <div><label for="contact-info-heading">Contact information heading</label><input id="contact-info-heading" wire:model="contact_info_heading" type="text" maxlength="255" />@error('contact_info_heading')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                            <div><label for="contact-info-description">Contact information description</label><textarea id="contact-info-description" wire:model="contact_info_description" rows="2" maxlength="1000"></textarea>@error('contact_info_description')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                            <div><label for="contact-form-heading">Form heading</label><input id="contact-form-heading" wire:model="contact_form_heading" type="text" maxlength="255" />@error('contact_form_heading')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                            <div><label for="contact-form-description">Form description</label><textarea id="contact-form-description" wire:model="contact_form_description" rows="2" maxlength="1000"></textarea>@error('contact_form_description')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div>
                        </div>
                    </section>
                @endif

                <section class="rex-page-edit-card">
                    <div class="rex-page-edit-card-heading"><div><h2>Search Engine Optimization</h2><p>Control how this page appears in search results.</p></div><span class="rex-page-edit-meta">SEO</span></div>
                    <div class="rex-page-edit-seo-preview"><div class="text-base font-medium text-[#1a0dab]">{{ $seo_title ?: ($title ?: 'Page title') }}</div><div class="mt-1 text-xs text-[#188038]">{{ url('/'.ltrim($slug, '/')) }}</div><p class="mt-1 text-xs text-[#4d5156]">{{ $seo_description ?: ($excerpt ?: 'Add a meta description to improve this page preview.') }}</p></div>
                    <details class="rex-page-edit-details"><summary>Edit SEO Meta</summary><div class="mt-4 grid gap-4 sm:grid-cols-2"><div><label for="seo-title">SEO title</label><input id="seo-title" wire:model="seo_title" type="text" value="{{ $seo_title }}" placeholder="Page title for search engines" />@error('seo_title')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div><div><label for="canonical-url">Canonical URL</label><input id="canonical-url" wire:model="canonical_url" type="url" value="{{ $canonical_url }}" placeholder="https://example.com/page" />@error('canonical_url')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div><div class="sm:col-span-2"><label for="seo-description">Meta description</label><textarea id="seo-description" wire:model="seo_description" rows="3" placeholder="A concise search result description.">{{ $seo_description }}</textarea>@error('seo_description')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div><div class="sm:col-span-2 flex flex-wrap gap-5"><label class="rex-page-edit-check"><input wire:model="noindex" type="checkbox" @checked($noindex) /> Discourage search indexing</label><label class="rex-page-edit-check"><input wire:model="nofollow" type="checkbox" @checked($nofollow) /> Discourage link following</label></div></div></details>
                </section>
            </main>

            <aside class="rex-page-edit-sidebar" style="position:sticky;top:1rem;display:grid;gap:16px">
                <section class="rex-page-edit-card rex-page-edit-publish">
                    <div class="rex-page-edit-card-heading"><h2>Publish</h2></div>
                    <div class="space-y-3"><button type="button" wire:click="saveChanges" wire:loading.attr="disabled" wire:target="saveChanges" class="rex-page-edit-button rex-page-edit-button--primary"><span wire:loading.remove wire:target="saveChanges">Save</span><span wire:loading wire:target="saveChanges">Saving...</span></button><button type="button" wire:click="saveAndExit" wire:loading.attr="disabled" wire:target="saveAndExit" class="rex-page-edit-button rex-page-edit-button--secondary"><span wire:loading.remove wire:target="saveAndExit">Save &amp; Exit</span><span wire:loading wire:target="saveAndExit">Saving...</span></button></div>
                    <a href="{{ $this->backRoute }}" wire:navigate class="rex-page-edit-cancel">Cancel</a>
                </section>
                <section class="rex-page-edit-card"><div class="rex-page-edit-card-heading"><h2>Status</h2></div><label for="page-status" class="sr-only">Status</label><select id="page-status" wire:model="status"><option value="draft" @selected($status === 'draft')>Draft</option><option value="published" @selected($status === 'published')>Published</option></select>@error('status')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</section>
                <section class="rex-page-edit-card"><div class="rex-page-edit-card-heading"><h2>Navigation</h2></div><div class="space-y-3"><label class="rex-page-edit-check"><input wire:model="show_in_navbar" type="checkbox" /> Show in Navbar</label><label class="rex-page-edit-check"><input wire:model="show_in_footer" type="checkbox" /> Show in Footer</label><div><label for="navigation-label">Navigation Label</label><input id="navigation-label" wire:model="navigation_label" type="text" placeholder="Defaults to page title" />@error('navigation_label')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div><div><label for="menu-order">Menu Order</label><input id="menu-order" wire:model="menu_order" type="number" min="0" max="9999" />@error('menu_order')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div></div></section>
                <section class="rex-page-edit-card"><div class="rex-page-edit-card-heading"><h2>Featured image</h2></div><label for="featured-image" class="rex-page-edit-upload">Choose image<input id="featured-image" wire:model="featuredImageUpload" type="file" accept="image/jpeg,image/png,image/webp" /></label>@if ($featuredImageUpload)<img src="{{ $featuredImageUpload->temporaryUrl() }}" alt="Featured image preview" class="rex-page-edit-image">@elseif ($featured_image)<img src="{{ Storage::disk('public')->url($featured_image) }}" alt="Current featured image" class="rex-page-edit-image"><button type="button" wire:click="removeFeaturedImage" class="rex-page-edit-media-remove">Remove image</button>@else<div class="rex-page-edit-image-empty"><flux:icon name="photo" class="size-7" /><span>No image selected</span></div>@endif @error('featuredImageUpload')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</section>
            </aside>
        </form>
    </div>
@else
<div class="mx-auto max-w-[1200px] space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><a href="{{ $this->backRoute }}" wire:navigate class="text-xs font-bold uppercase tracking-[0.16em] text-[#000080]">{{ $this->resource }}</a><h1 class="mt-2 text-2xl font-bold tracking-tight text-[#172033]">{{ $this->heading }}</h1><p class="mt-2 text-sm text-[#64748b]">Keep your content clear, useful, and ready for the public website.</p></div><span class="inline-flex w-fit items-center gap-2 border border-[#e5eaf2] bg-white px-3 py-2 text-xs font-semibold text-[#64748b]"><span class="size-2 rounded-full {{ $status === 'published' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>{{ ucfirst($status) }}</span></div>
    <form wire:submit="saveDraft" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-6">
            <section class="border border-[#e5eaf2] bg-white p-6 shadow-[0_2px_12px_rgba(23,32,51,0.04)]"><h2 class="text-base font-bold text-[#172033]">Core content</h2><div class="mt-5 space-y-5"><div><label for="title" class="mb-2 block text-sm font-semibold text-[#172033]">{{ $resource === 'services' || $resource === 'projects' ? 'Title' : 'Title' }} <span class="text-red-500">*</span></label><input id="title" wire:model.live="title" type="text" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm outline-none focus:border-[#000080] focus:ring-2 focus:ring-[#000080]/10" placeholder="Enter a clear title" />@error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div><div><label for="slug" class="mb-2 block text-sm font-semibold text-[#172033]">Slug <span class="text-red-500">*</span></label><input id="slug" wire:model="slug" type="text" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm outline-none focus:border-[#000080] focus:ring-2 focus:ring-[#000080]/10" placeholder="url-friendly-slug" />@error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                @if ($resource === 'services')<div><label class="mb-2 block text-sm font-semibold text-[#172033]">Short description</label><textarea wire:model="short_description" rows="3" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm outline-none focus:border-[#000080]" placeholder="A concise service summary"></textarea></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Description</label><textarea wire:model="description" rows="10" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm outline-none focus:border-[#000080]" placeholder="Describe this service..."></textarea></div>@elseif ($resource === 'projects')<div class="grid gap-5 sm:grid-cols-2"><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Client</label><input wire:model="client" type="text" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Category</label><input wire:model="category" type="text" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Short description</label><textarea wire:model="short_description" rows="3" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm"></textarea></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Description</label><textarea wire:model="description" rows="10" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm"></textarea></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Technologies</label><input wire:model="technologies" type="text" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" placeholder="Laravel, Node.js, MySQL" /></div>@else<div><label class="mb-2 block text-sm font-semibold text-[#172033]">Excerpt</label><textarea wire:model="excerpt" rows="3" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" placeholder="A concise summary for cards and search"></textarea></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Content</label><textarea wire:model="content" rows="14" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" placeholder="Write the content here..."></textarea><p class="mt-2 text-xs text-[#94a3b8]">Basic rich-content editing can be expanded in a later editor-focused phase.</p></div>@endif
            </div></section>
            <section class="border border-[#e5eaf2] bg-white p-6 shadow-[0_2px_12px_rgba(23,32,51,0.04)]"><h2 class="text-base font-bold text-[#172033]">Media</h2><div class="mt-5 grid gap-5 sm:grid-cols-2"><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Featured image</label><input wire:model="featuredImageUpload" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full border border-dashed border-[#b8c4d6] p-3 text-xs text-[#64748b]" />@if ($featuredImageUpload)<img src="{{ $featuredImageUpload->temporaryUrl() }}" alt="Featured image preview" class="mt-3 aspect-video w-full object-cover">@elseif ($featured_image)<img src="{{ Storage::disk('public')->url($featured_image) }}" alt="Current featured image" class="mt-3 aspect-video w-full object-cover">@endif @error('featuredImageUpload')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>@if ($resource === 'projects')<div><label class="mb-2 block text-sm font-semibold text-[#172033]">Project gallery</label><input wire:model="galleryUploads" multiple type="file" accept="image/jpeg,image/png,image/webp" class="block w-full border border-dashed border-[#b8c4d6] p-3 text-xs text-[#64748b]" />@if (count($gallery))<div class="mt-3 grid grid-cols-3 gap-2">@foreach ($gallery as $image)<img src="{{ Storage::disk('public')->url($image) }}" alt="Gallery image" class="aspect-square w-full object-cover">@endforeach</div>@endif @error('galleryUploads.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>@endif</div></section>
        </div>
        <aside class="space-y-6"><section class="border border-[#e5eaf2] bg-white p-6 shadow-[0_2px_12px_rgba(23,32,51,0.04)]"><h2 class="text-base font-bold text-[#172033]">Publishing</h2><div class="mt-5 space-y-5"><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Status</label><select wire:model="status" class="w-full border border-[#dbe2ed] bg-white px-3 py-2.5 text-sm"><option value="draft">Draft</option><option value="published">Published</option></select></div>@if ($resource === 'projects')<div><label class="mb-2 block text-sm font-semibold text-[#172033]">Project URL</label><input wire:model="project_url" type="url" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Completion date</label><input wire:model="completion_date" type="date" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div>@elseif ($resource === 'blog')<div><label class="mb-2 block text-sm font-semibold text-[#172033]">Category</label><input wire:model="category" type="text" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Tags</label><input wire:model="tags" type="text" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" placeholder="web, strategy, news" /></div>@elseif ($resource === 'services')<div><label class="mb-2 block text-sm font-semibold text-[#172033]">Icon</label><input wire:model="icon" type="text" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" placeholder="code-bracket" /></div><div><label class="mb-2 block text-sm font-semibold text-[#172033]">Sort order</label><input wire:model="sort_order" type="number" min="0" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div>@endif</div></section><section class="border border-[#e5eaf2] bg-white p-6 shadow-[0_2px_12px_rgba(23,32,51,0.04)]"><h2 class="text-base font-bold text-[#172033]">SEO</h2><div class="mt-5 space-y-4"><label class="mb-2 block text-sm font-semibold" for="content-seo-title">SEO title</label><input id="content-seo-title" wire:model.live.debounce.400ms="seo_title" type="text" placeholder="SEO title" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /><p class="text-xs text-[#64748b]">{{ mb_strlen($seo_title) }} characters · Recommended 50–60.</p><label class="mb-2 block text-sm font-semibold" for="content-seo-description">SEO description</label><textarea id="content-seo-description" wire:model.live.debounce.400ms="seo_description" rows="4" maxlength="255" placeholder="SEO description" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm"></textarea><p class="text-xs text-[#64748b]">{{ mb_strlen($seo_description) }} characters · Recommended 150–160; up to 255 allowed.</p>@error('seo_title')<p class="text-xs text-red-600">{{ $message }}</p>@enderror @error('seo_description')<p class="text-xs text-red-600">{{ $message }}</p>@enderror<div class="border-t pt-3"><p class="break-all text-xs text-[#64748b]">{{ app(App\Support\Seo::class)->contentUrl($resource, $slug) }}</p><p class="mt-2 break-words font-semibold text-[#000080]">{{ $seo_title ?: $title }}</p><p class="mt-1 break-words text-xs text-[#64748b]">{{ $seo_description }}</p></div>@if ($resource === 'pages')<input wire:model="seo_keywords" type="text" placeholder="SEO keywords" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" />@endif</div></section></aside>
        <div class="sticky bottom-4 z-20 flex flex-col gap-3 border border-[#dbe2ed] bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-end"><a href="{{ $this->backRoute }}" wire:navigate class="px-4 py-2.5 text-center text-sm font-semibold text-[#64748b]">Cancel</a><button type="submit" wire:loading.attr="disabled" class="border border-[#000080] px-5 py-2.5 text-sm font-semibold text-[#000080]"><span wire:loading.remove wire:target="saveDraft">Save draft</span><span wire:loading wire:target="saveDraft">Saving...</span></button>@can('publish-cms-content')<button type="button" wire:click="publish" wire:loading.attr="disabled" class="bg-[#000080] px-5 py-2.5 text-sm font-semibold text-white"><span wire:loading.remove wire:target="publish">{{ $status === 'published' ? 'Save & publish' : 'Publish' }}</span><span wire:loading wire:target="publish">Publishing...</span></button>@endcan</div>
    </form>
    <section class="border border-[#e5eaf2] bg-white p-6 shadow-[0_2px_12px_rgba(23,32,51,0.04)]"><h2 class="text-base font-bold text-[#172033]">Advanced SEO</h2><div class="mt-5 space-y-4"><div><label class="mb-2 block text-sm font-semibold" for="canonical-url">Canonical URL</label><input id="canonical-url" wire:model="canonical_url" type="url" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" placeholder="https://example.com/page" />@error('canonical_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div><div class="flex flex-wrap gap-5 text-sm"><label class="flex items-center gap-2"><input wire:model="noindex" type="checkbox" class="size-4 accent-[#000080]" /> Discourage search indexing</label><label class="flex items-center gap-2"><input wire:model="nofollow" type="checkbox" class="size-4 accent-[#000080]" /> Discourage link following</label></div></div></section>
 </div>
@endif
</div>
