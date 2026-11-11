<?php

namespace App\Livewire\Admin;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\Media;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Support\HomepageContentService;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class HomepageBuilder extends Component
{
    use WithFileUploads;
    use WithPagination;

    public array $sections = [];

    #[Locked]
    public string $selectedKey = 'hero';

    public array $form = [];

    public string $statusMessage = '';

    public string $mediaSearch = '';

    public $mediaUpload;

    #[Locked]
    public ?int $pendingUploadId = null;

    #[Locked]
    public ?string $mediaField = null;

    public function mount(): void
    {
        Gate::authorize('homepage_builder.view');
        HomepageSection::ensureDefaults();
        $this->loadSections();
        $this->select($this->selectedKey);
    }

    public function select(string $key): void
    {
        Gate::authorize('homepage_builder.view');
        $section = HomepageSection::where('key', $key)->firstOrFail();
        if ($this->selectedKey !== $key) $this->pendingUploadId = null;
        $this->selectedKey = $key;
        $this->statusMessage = '';
        $this->form = array_merge($this->defaults($key), $section->settings ?? []);
    }

    public function save(): void
    {
        Gate::authorize('homepage_builder.update');
        $section = HomepageSection::where('key', $this->selectedKey)->firstOrFail();
        $this->validate($this->rules($this->selectedKey));
        $this->form['selected_ids'] = array_values(array_unique(array_map('intval', $this->form['selected_ids'] ?? [])));
        $section->settings = $this->form;
        $section->save();
        if ($this->pendingUploadId) {
            Media::query()->whereKey($this->pendingUploadId)->where('source_module', 'Homepage Builder')->update([
                'source_record_type' => HomepageSection::class,
                'source_record_id' => $section->id,
                'source_record_name' => $section->name,
                'source_field' => $this->mediaField ?? 'Section image',
            ]);
            $this->pendingUploadId = null;
        }
        HomepageContentService::forget();
        $this->loadSections();
        $this->statusMessage = $section->name.' section saved successfully.';
        Flux::toast(variant: 'success', text: $this->statusMessage);
    }

    public function toggle(string $key): void
    {
        Gate::authorize('homepage_builder.update');
        $section = HomepageSection::where('key', $key)->firstOrFail();
        $section->enabled = ! $section->enabled;
        $section->save();
        HomepageContentService::forget();
        $this->loadSections();
        $this->statusMessage = $section->name.' section '.($section->enabled ? 'enabled.' : 'disabled.');
        Flux::toast(variant: 'success', text: $this->statusMessage);
    }

    public function move(string $key, string $direction): void
    {
        Gate::authorize('homepage_builder.update');
        $sections = HomepageSection::orderBy('sort_order')->orderBy('id')->get()->values();
        $index = $sections->search(fn (HomepageSection $section): bool => $section->key === $key);
        $swap = $direction === 'up' ? $index - 1 : $index + 1;
        if ($index === false || ! $sections->has($swap)) {
            return;
        }
        [$sections[$index], $sections[$swap]] = [$sections[$swap], $sections[$index]];
        foreach ($sections as $order => $section) {
            $section->update(['sort_order' => $order]);
        }
        HomepageContentService::forget();
        $this->loadSections();
    }

    public function addItem(): void
    {
        Gate::authorize('homepage_builder.update');
        $this->form['items'][] = ['title' => '', 'description' => '', 'icon' => ''];
    }

    public function removeItem(int $index): void
    {
        Gate::authorize('homepage_builder.update');
        unset($this->form['items'][$index]);
        $this->form['items'] = array_values($this->form['items']);
    }

    public function chooseMedia(string $field): void
    {
        Gate::authorize('homepage_builder.update');
        abort_unless(in_array($field, ['hero_image', 'about_image', 'cta_background'], true), 422);
        $this->pendingUploadId = null;
        $this->mediaField = $field;
        $this->mediaSearch = '';
        $this->resetPage('mediaPage');
        Flux::modal('homepage-media')->show();
    }

    public function selectMedia(int $id): void
    {
        Gate::authorize('homepage_builder.update');
        abort_unless($this->mediaField, 422);
        Media::where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])->findOrFail($id);
        $this->form[$this->mediaField] = $id;
        $this->pendingUploadId = null;
        Flux::modal('homepage-media')->close();
    }

    public function removeMedia(string $field): void
    {
        Gate::authorize('homepage_builder.update');
        abort_unless(in_array($field, ['hero_image', 'about_image', 'cta_background'], true), 422);
        $this->form[$field] = null;
        $this->form[$field.'_alt'] = '';
        $this->form[$field.'_alt_mode'] = 'default';
    }

    public function uploadMedia(): void
    {
        Gate::authorize('homepage_builder.update');
        Gate::authorize('media.upload');
        abort_unless($this->mediaField && in_array($this->mediaField, ['hero_image', 'about_image', 'cta_background'], true), 422);
        $this->validate(['mediaUpload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120']]);

        $section = HomepageSection::where('key', $this->selectedKey)->firstOrFail();
        $fieldName = str_replace('_', ' ', $this->mediaField);
        $media = Media::fromUpload($this->mediaUpload, [
            'uploaded_by' => auth()->id(),
            'disk' => 'public',
            'source_module' => 'Homepage Builder',
            'source_field' => $fieldName,
            'source_record_name' => $section->name,
            'source_label' => $fieldName,
        ]);
        $this->form[$this->mediaField] = $media->id;
        $this->pendingUploadId = $media->wasRecentlyCreated ? $media->id : null;
        $this->mediaUpload = null;
        Flux::toast(variant: 'success', text: 'Image uploaded and selected. Save this section to apply it.');
    }

    public function updatedMediaSearch(): void
    {
        $this->resetPage('mediaPage');
    }

    public function render()
    {
        Gate::authorize('homepage_builder.view');
        $section = HomepageSection::where('key', $this->selectedKey)->first();
        $model = ['services' => Service::class, 'projects' => Project::class, 'testimonials' => Testimonial::class, 'team' => TeamMember::class, 'blog' => BlogPost::class, 'faqs' => Faq::class][$this->selectedKey] ?? null;
        $options = $model ? $this->published($model)->get() : collect();
        $mediaItems = $this->mediaField ? Media::query()->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])->when($this->mediaSearch, fn ($query) => $query->where(fn ($search) => $search->where('original_name', 'like', '%'.$this->mediaSearch.'%')->orWhere('title', 'like', '%'.$this->mediaSearch.'%')->orWhere('alt_text', 'like', '%'.$this->mediaSearch.'%')))->latest()->paginate(8, ['*'], 'mediaPage') : null;

        return view('livewire.admin.homepage-builder', [
            'current' => $section,
            'options' => $options,
            'mediaItems' => $mediaItems,
        ])->layout('layouts.admin')->title('Homepage Builder');
    }

    private function loadSections(): void
    {
        $this->sections = HomepageSection::orderBy('sort_order')->orderBy('id')->get()->map(fn (HomepageSection $section): array => [
            'key' => $section->key,
            'name' => $section->name,
            'enabled' => $section->enabled,
            'summary' => $this->summary($section),
        ])->all();
    }

    private function defaults(string $key): array
    {
        $defaults = ['eyebrow' => '', 'heading' => '', 'description' => '', 'cta_label' => '', 'cta_url' => '', 'selected_ids' => [], 'limit' => 6];
        if ($key === 'hero') {
            return array_merge($defaults, ['headline' => '', 'highlighted_text' => '', 'primary_cta_label' => '', 'primary_cta_url' => '', 'secondary_cta_label' => '', 'secondary_cta_url' => '', 'hero_image' => null, 'hero_image_alt' => '', 'hero_image_alt_mode' => 'default', 'trust_text' => '']);
        }
        if ($key === 'stats' || $key === 'why_choose_us') {
            $defaults['items'] = [];
        }
        if ($key === 'about') {
            $defaults['about_image'] = null;
            $defaults['about_image_alt'] = '';
            $defaults['about_image_alt_mode'] = 'default';
            $defaults['experience_years'] = '';
            $defaults['story_heading'] = '';
            $defaults['story_close'] = '';
            $defaults['mission_title'] = '';
            $defaults['mission_description'] = '';
            $defaults['vision_title'] = '';
            $defaults['vision_description'] = '';
            $defaults['thinking_eyebrow'] = '';
            $defaults['thinking_heading'] = '';
            $defaults['items'] = [
                ['title' => 'Understand the business', 'description' => 'We listen, learn and take time to understand your goals and challenges.', 'icon' => 'people'],
                ['title' => 'Build with purpose', 'description' => 'We design and develop practical solutions that solve real needs.', 'icon' => 'code'],
                ['title' => 'Support what comes next', 'description' => 'We stay involved to help you adapt, improve and grow.', 'icon' => 'support'],
            ];
        }
        if ($key === 'process') {
            $defaults['steps'] = [
                ['title' => 'Discover', 'description' => 'We learn about your business, goals and requirements.'],
                ['title' => 'Design', 'description' => 'We create a tailored solution and plan the details.'],
                ['title' => 'Develop', 'description' => 'We bring your project to life with regular updates.'],
                ['title' => 'Deliver', 'description' => 'We launch and support your next steps.'],
            ];
        }
        if ($key === 'cta') {
            $defaults['cta_background'] = null;
            $defaults['cta_background_alt'] = '';
            $defaults['cta_background_alt_mode'] = 'default';
            $defaults['secondary_cta_label'] = '';
            $defaults['secondary_cta_url'] = '';
        }

        return $defaults;
    }

    private function rules(string $key): array
    {
        $rules = ['form.eyebrow' => ['nullable', 'string', 'max:255'], 'form.heading' => ['nullable', 'string', 'max:255'], 'form.description' => ['nullable', 'string', 'max:2000'], 'form.cta_label' => ['nullable', 'string', 'max:100'], 'form.cta_url' => ['nullable', 'url:http,https', 'max:500'], 'form.limit' => ['required', 'integer', 'min:1', 'max:12'], 'form.selected_ids' => ['array', 'max:12'], 'form.selected_ids.*' => ['integer']];
        if (in_array($key, ['services', 'projects', 'testimonials', 'team', 'blog', 'faqs'], true)) {
            $model = ['services' => Service::class, 'projects' => Project::class, 'testimonials' => Testimonial::class, 'team' => TeamMember::class, 'blog' => BlogPost::class, 'faqs' => Faq::class][$key];
            $rules['form.selected_ids.*'][] = Rule::exists((new $model)->getTable(), 'id')->where('status', 'published');
        }
        if ($key === 'hero') {
            $rules += ['form.headline' => ['nullable', 'string', 'max:255'], 'form.highlighted_text' => ['nullable', 'string', 'max:255'], 'form.primary_cta_label' => ['nullable', 'string', 'max:100'], 'form.primary_cta_url' => ['nullable', 'url:http,https', 'max:500'], 'form.secondary_cta_label' => ['nullable', 'string', 'max:100'], 'form.secondary_cta_url' => ['nullable', 'url:http,https', 'max:500'], 'form.trust_text' => ['nullable', 'string', 'max:255']];
        }
        if (in_array($key, ['stats', 'why_choose_us', 'about'], true)) {
            $rules += ['form.items' => ['array', 'max:6'], 'form.items.*.title' => ['required', 'string', 'max:255'], 'form.items.*.description' => ['nullable', 'string', 'max:500'], 'form.items.*.icon' => ['nullable', 'string', 'max:100']];
        }
        if ($key === 'about') {
            $rules += [
                'form.experience_years' => ['nullable', 'string', 'max:12'],
                'form.story_heading' => ['nullable', 'string', 'max:255'],
                'form.story_close' => ['nullable', 'string', 'max:255'],
                'form.mission_title' => ['nullable', 'string', 'max:100'],
                'form.mission_description' => ['nullable', 'string', 'max:500'],
                'form.vision_title' => ['nullable', 'string', 'max:100'],
                'form.vision_description' => ['nullable', 'string', 'max:500'],
                'form.thinking_eyebrow' => ['nullable', 'string', 'max:100'],
                'form.thinking_heading' => ['nullable', 'string', 'max:255'],
            ];
        }
        if ($key === 'process') {
            $rules += ['form.steps' => ['required', 'array', 'size:4'], 'form.steps.*.title' => ['required', 'string', 'max:100'], 'form.steps.*.description' => ['nullable', 'string', 'max:500']];
        }
        if ($key === 'cta') {
            $rules += ['form.secondary_cta_label' => ['nullable', 'string', 'max:100'], 'form.secondary_cta_url' => ['nullable', 'url:http,https', 'max:500']];
        }
        foreach (['hero_image', 'about_image', 'cta_background'] as $field) {
            $rules['form.'.$field] = ['nullable', 'integer', Rule::exists('media', 'id')->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])];
            $rules['form.'.$field.'_alt'] = ['nullable', 'string', 'max:255'];
            $rules['form.'.$field.'_alt_mode'] = ['nullable', 'in:default,custom,decorative'];
        }

        return $rules;
    }

    private function published(string $model)
    {
        return $model::query()->where('status', 'published')->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))->when($model === Service::class, fn ($query) => $query->where('is_active', true))->orderBy('sort_order')->orderByDesc('published_at');
    }

    private function summary(HomepageSection $section): string
    {
        $settings = $section->settings ?? [];

        return $settings['heading'] ?? $settings['headline'] ?? (! empty($settings['selected_ids']) ? count($settings['selected_ids']).' selected records' : 'No content configured yet');
    }
}
