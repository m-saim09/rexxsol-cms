<?php

use App\Models\Media;
use App\Support\CmsContent;
use App\Support\CmsPermissions;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] #[Title('Edit testimonial')] class extends Component {
    use WithFileUploads;
    use WithPagination;

    #[\Livewire\Attributes\Locked]
    public string $resource = 'testimonials';
    #[\Livewire\Attributes\Locked]
    public ?int $modelId = null;
    public string $name = '';
    public string $designation = '';
    public string $company = '';
    public string $quote = '';
    public int $rating = 5;
    public string $status = 'draft';
    public int $sort_order = 0;
    public ?string $image = null;
    public string $imageAlt = '';
    public string $imageAltMode = 'default';
    public $imageUpload;
    public bool $showMediaPicker = false;
    public string $mediaSearch = '';
    public ?int $selectedMediaId = null;
    public bool $imageRemoved = false;

    public function mount(?string $model = null): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, $model ? 'update' : 'create')), 403);
        if ($model) {
            $this->fillFromModel(CmsContent::model($this->resource)::query()->findOrFail($model));
        }
    }

    public function openMediaPicker(): void
    {
        abort_unless(auth()->user()->can('media.view'), 403);
        $this->showMediaPicker = true;
        $this->mediaSearch = '';
    }

    public function closeMediaPicker(): void
    {
        $this->showMediaPicker = false;
    }

    public function updatedMediaSearch(): void
    {
        $this->resetPage('mediaPage');
    }

    public function selectMedia(int $id): void
    {
        abort_unless(auth()->user()->can('media.view'), 403);
        $media = Media::query()->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])->findOrFail($id);
        $this->selectedMediaId = $media->id;
        $this->image = Media::normalizePath($media->path);
        $this->imageUpload = null;
        $this->imageRemoved = false;
        $this->showMediaPicker = false;
    }

    public function removeImage(): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, $this->modelId ? 'update' : 'create')), 403);
        $this->selectedMediaId = null;
        $this->image = null;
        $this->imageAlt = '';
        $this->imageAltMode = 'default';
        $this->imageUpload = null;
        $this->imageRemoved = true;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'quote' => ['required', 'string'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'imageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'imageAlt' => ['nullable', 'string', 'max:255'],
            'imageAltMode' => ['required', 'in:default,custom,decorative'],
        ];
    }

    public function saveDraft(): void
    {
        $this->save(false);
    }

    public function saveAndExit(): void
    {
        $this->save(false, true);
    }

    public function publish(): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'publish')), 403);
        $this->save(true);
    }

    protected function save(bool $publish, bool $exit = false): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, $this->modelId ? 'update' : 'create')), 403);
        if ($this->imageUpload) abort_unless(auth()->user()->can('media.upload'), 403);
        $this->validate($this->rules());
        $model = $this->modelId ? CmsContent::model($this->resource)::query()->findOrFail($this->modelId) : new (CmsContent::model($this->resource));
        $model->fill([
            'name' => $this->name,
            'designation' => $this->designation,
            'company' => $this->company,
            'quote' => $this->quote,
            'rating' => $this->rating,
            'sort_order' => $this->sort_order,
        ]);
        $model->status = $publish ? 'published' : 'draft';
        $model->published_at = $publish ? ($model->published_at ?: now()) : null;

        if ($this->imageUpload) {
            $media = Media::fromUpload($this->imageUpload, ['uploaded_by' => auth()->id(), 'disk' => 'public', 'source_module' => 'Testimonials', 'source_field' => 'Client photo', 'source_record_name' => $this->name, 'source_label' => 'Client photo']);
            $this->selectedMediaId = $media->id;
            $this->image = Media::normalizePath($media->path);
            $this->imageAlt = '';
            $this->imageAltMode = 'default';
            $model->avatar = Media::normalizePath($media->path);
        } elseif ($this->selectedMediaId) {
            $media = Media::query()->where('disk', 'public')->findOrFail($this->selectedMediaId);
            $model->avatar = Media::normalizePath($media->path);
        } elseif ($this->imageRemoved) {
            $model->avatar = null;
        }
        $model->avatar_alt = $model->avatar
            ? ($this->imageAltMode === 'decorative' ? '' : ($this->imageAltMode === 'custom' ? trim($this->imageAlt) : null))
            : null;

        $model->save();

        if ($this->imageUpload && $media->wasRecentlyCreated) {
            $media->update([
                'source_module' => 'Testimonials',
                'source_field' => 'Client photo',
                'source_record_type' => $model::class,
                'source_record_id' => $model->id,
                'source_record_name' => $model->name,
                'source_label' => 'Client photo',
            ]);
        }

        $this->modelId = $model->id;

        if ($exit) {
            session()->flash('cms_toast', [
                'heading' => 'Testimonial saved',
                'text' => 'Changes saved successfully. Returning to the Testimonials.',
                'variant' => 'success',
                'duration' => 4000,
            ]);
            $this->redirectRoute('admin.testimonials.index', navigate: true);

            return;
        }

        Flux::toast(variant: 'success', text: 'Testimonial '.($publish ? 'published' : 'saved').' successfully.');
        $this->redirectRoute('admin.testimonials.edit', $model->id, navigate: true);
    }

    protected function fillFromModel($model): void
    {
        foreach (['name', 'designation', 'company', 'quote', 'rating', 'sort_order'] as $field) {
            if (isset($model->{$field})) {
                $this->{$field} = $model->{$field};
            }
        }

        $this->modelId = $model->id;
        $this->status = $model->status;
        $this->image = $model->avatar;
        $this->imageAlt = (string) ($model->avatar_alt ?? '');
        $this->imageAltMode = $model->avatar_alt === '' ? 'decorative' : (filled($model->avatar_alt) ? 'custom' : 'default');
        $selected = Media::query()->where('disk', 'public')->where('path', Media::normalizePath((string) ($model->avatar ?? '')))->first();
        $this->selectedMediaId = $selected?->id;
    }
}; ?>
<div class="rex-content-form-root">
    <div class="rex-page-edit-page mx-auto w-full max-w-[1440px] space-y-5">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#64748b]">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.testimonials.index') }}" wire:navigate class="text-[#2563eb] hover:text-[#1d4ed8]">Testimonials</a>
            <span>/</span>
            <span class="text-[#172033]">{{ $modelId ? 'Edit' : 'Create' }} {{ $name ?: 'Testimonial' }}</span>
        </nav>

        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#2563eb]">Detail</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#172033]">{{ $modelId ? 'Edit Testimonial' : 'Create Testimonial' }}</h1>
                <p class="mt-1 text-sm text-[#64748b]">Update the testimonial content and publishing settings.</p>
            </div>
            <span class="rex-page-edit-state {{ $status === 'published' ? 'rex-page-edit-state--published' : 'rex-page-edit-state--draft' }}">{{ ucfirst($status) }}</span>
        </div>

        <form wire:submit.prevent="saveDraft" class="rex-page-edit-grid" style="display:grid;grid-template-columns:minmax(0,3fr) minmax(260px,1fr);gap:16px;align-items:start">
            <main class="space-y-4">
                <section class="rex-page-edit-card">
                    <div class="rex-page-edit-card-heading"><div><h2>Testimonial details</h2><p>Capture the client story and attribution shown on the website.</p></div></div>
                    <div class="rex-page-edit-fields">
                        <div>
                            <label for="testimonial-name">Client name <span>*</span></label>
                            <input id="testimonial-name" wire:model.live="name" type="text" placeholder="Client name" />
                            @error('name')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="testimonial-designation">Designation</label>
                            <input id="testimonial-designation" wire:model="designation" type="text" placeholder="Product Director" />
                        </div>
                        <div>
                            <label for="testimonial-company">Company</label>
                            <input id="testimonial-company" wire:model="company" type="text" placeholder="Company name" />
                        </div>
                        <div>
                            <label for="testimonial-rating">Rating <span>*</span></label>
                            <select id="testimonial-rating" wire:model="rating">
                                @foreach (range(1, 5) as $score)<option value="{{ $score }}">{{ $score }} / 5</option>@endforeach
                            </select>
                            @error('rating')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <div class="rex-page-edit-editor-label"><label for="testimonial-quote">Client quote <span>*</span></label><span>Public testimonial copy</span></div>
                            <textarea id="testimonial-quote" wire:model="quote" rows="12" class="rex-page-edit-content" placeholder="Write the client testimonial here..."></textarea>
                            @error('quote')<p class="rex-page-edit-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </main>

            <aside class="rex-page-edit-sidebar" style="position:sticky;top:1rem;display:grid;gap:16px">
                <section class="rex-page-edit-card rex-page-edit-publish">
                    <div class="rex-page-edit-card-heading"><h2>Publish</h2></div>
                    <div class="space-y-3">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveDraft" class="rex-page-edit-button rex-page-edit-button--primary"><span wire:loading.remove wire:target="saveDraft">Save</span><span wire:loading wire:target="saveDraft">Saving...</span></button>
                        <button type="button" wire:click="saveAndExit" wire:loading.attr="disabled" wire:target="saveAndExit" class="rex-page-edit-button rex-page-edit-button--secondary"><span wire:loading.remove wire:target="saveAndExit">Save &amp; Exit</span><span wire:loading wire:target="saveAndExit">Saving...</span></button>
                        @can(CmsPermissions::resourcePermission($resource, 'publish'))<button type="button" wire:click="publish" wire:loading.attr="disabled" wire:target="publish" class="rex-page-edit-button rex-page-edit-button--secondary"><span wire:loading.remove wire:target="publish">Publish</span><span wire:loading wire:target="publish">Publishing...</span></button>@endcan
                    </div>
                    <a href="{{ route('admin.testimonials.index') }}" wire:navigate class="rex-page-edit-cancel">Cancel</a>
                </section>
                <section class="rex-page-edit-card"><div class="rex-page-edit-card-heading"><h2>Status</h2></div><label for="testimonial-status" class="sr-only">Status</label><select id="testimonial-status" wire:model="status"><option value="draft">Draft</option><option value="published">Published</option></select></section>
                <section class="rex-page-edit-card"><div class="rex-page-edit-card-heading"><h2>Client image</h2></div><div class="space-y-3"><div class="flex gap-2"><label for="testimonial-image" class="rex-page-edit-upload flex-1">Upload image<input id="testimonial-image" wire:model="imageUpload" type="file" accept="image/jpeg,image/png,image/webp" /></label><button type="button" wire:click="openMediaPicker" class="rex-page-edit-button rex-page-edit-button--secondary">Library</button></div>@if ($imageUpload)<img src="{{ $imageUpload->temporaryUrl() }}" alt="Client image preview" class="rex-page-edit-image">@elseif ($image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="Current client image" class="rex-page-edit-image">@else<div class="rex-page-edit-image-empty"><flux:icon name="photo" class="size-7" /><span>No image selected</span></div>@endif @if ($image || $imageUpload || $selectedMediaId)<button type="button" wire:click="removeImage" class="text-xs font-semibold text-red-600 hover:underline">Remove image</button>@endif<label class="block text-xs font-semibold">Alt text behavior<select wire:model="imageAltMode" class="mt-1 w-full"><option value="default">Use library alt-text default</option><option value="custom">Custom description</option><option value="decorative">Decorative (empty alt)</option></select></label>@if ($imageAltMode === 'custom')<textarea wire:model="imageAlt" rows="2" maxlength="255" class="w-full border border-[#dbe2ed] px-3 py-2 text-sm" placeholder="Briefly describe what the image communicates."></textarea>@endif @error('imageUpload')<p class="rex-page-edit-error">{{ $message }}</p>@enderror</div></section>
            </aside>
        </form>
    </div>
    @if ($showMediaPicker)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4" role="dialog" aria-modal="true">
            <div class="w-full max-w-3xl rounded-[8px] border border-[#e5eaf2] bg-white p-4 shadow-xl">
                <div class="mb-4 flex items-center justify-between gap-3"><div><h3 class="text-lg font-bold text-[#172033]">Choose from Media Library</h3><p class="text-xs text-[#64748b]">Select an existing image from the shared CMS library.</p></div><button type="button" wire:click="closeMediaPicker" class="text-sm text-[#64748b] hover:text-[#172033]">Close</button></div>
                <div class="mb-4 flex items-center gap-3"><input type="search" wire:model.live.debounce.300ms="mediaSearch" placeholder="Search media..." class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div>
                <div class="grid max-h-[420px] grid-cols-2 gap-3 overflow-y-auto md:grid-cols-4">
                    @php $items = \App\Models\Media::query()->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])->when($mediaSearch, fn($query) => $query->where(fn($search) => $search->where('original_name', 'like', '%'.$mediaSearch.'%')->orWhere('title', 'like', '%'.$mediaSearch.'%')->orWhere('alt_text', 'like', '%'.$mediaSearch.'%')))->latest()->paginate(12, ['*'], 'mediaPage'); @endphp
                    @forelse ($items as $media)
                        <button type="button" wire:click="selectMedia({{ $media->id }})" class="overflow-hidden rounded-[8px] border {{ $selectedMediaId === $media->id ? 'border-[#206bc4] bg-[#f0f4ff]' : 'border-[#e5eaf2] bg-white' }} text-left">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk($media->disk)->url($media->path) }}" alt="{{ $media->alt_text ?: $media->original_name }}" class="aspect-[4/3] w-full object-cover" />
                            <div class="p-2 text-xs text-[#172033]"><div class="truncate font-semibold">{{ $media->title ?: 'Untitled image' }}</div><div class="mt-1 text-[#64748b]">{{ strtoupper($media->extension) }}</div></div>
                        </button>
                    @empty
                        <div class="col-span-full rounded-[8px] border border-dashed border-[#dbe2ed] bg-[#f8fafc] p-8 text-center text-sm text-[#64748b]">No media found.</div>
                    @endforelse
                </div>
                <div class="mt-4">{{ $items->links() }}</div>
            </div>
        </div>
    @endif
</div>
