<?php
use App\Models\Media;
use App\Support\CmsContent;
use App\Support\CmsPermissions;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
new #[Layout('layouts.admin')] #[Title('Edit content')] class extends Component {
    use WithFileUploads;
    public string $resource = 'team'; public ?int $modelId = null; public string $name = ''; public string $slug = ''; public string $designation = ''; public string $short_bio = ''; public string $bio = ''; public string $email = ''; public string $phone = ''; public string $linkedin_url = ''; public string $github_url = ''; public string $twitter_url = ''; public string $question = ''; public string $answer = ''; public string $category = ''; public string $company = ''; public string $quote = ''; public int $rating = 5; public string $status = 'draft'; public int $sort_order = 0; public ?string $image = null; public $imageUpload; public bool $showMediaPicker = false; public string $mediaSearch = ''; public ?int $selectedMediaId = null; public bool $imageRemoved = false;
    public function mount(string $resource, ?string $model = null): void { abort_unless(in_array($resource, ['team', 'testimonials', 'faqs'], true), 404); abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($resource, $model ? 'update' : 'create')), 403); $this->resource = $resource; if ($model) $this->fillFromModel(CmsContent::model($resource)::query()->findOrFail($model)); }
    public function openMediaPicker(): void { abort_unless(auth()->user()->can('media.view'), 403); $this->showMediaPicker = true; $this->mediaSearch = ''; }
    public function closeMediaPicker(): void { $this->showMediaPicker = false; }
    public function selectMedia(int $id): void { abort_unless(auth()->user()->can('media.view'), 403); $media = Media::query()->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])->findOrFail($id); $this->selectedMediaId = $media->id; $this->image = Media::normalizePath($media->path); $this->imageUpload = null; $this->imageRemoved = false; $this->showMediaPicker = false; }
    public function removeImage(): void { abort_unless(auth()->user()->can('media.update'), 403); $this->selectedMediaId = null; $this->image = null; $this->imageUpload = null; $this->imageRemoved = true; }
    public function updatedName(string $value): void { if (! $this->modelId) $this->slug = Str::slug($value); }
    protected function rules(): array { return match ($this->resource) { 'team' => ['name' => ['required','string','max:255'], 'slug' => ['required','string','max:255',Rule::unique('team_members','slug')->ignore($this->modelId)], 'designation' => ['required','string','max:255'], 'email' => ['nullable','email','max:255'], 'linkedin_url' => ['nullable','url','max:500'], 'github_url' => ['nullable','url','max:500'], 'twitter_url' => ['nullable','url','max:500'], 'imageUpload' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120']], 'testimonials' => ['name' => ['required','string','max:255'], 'quote' => ['required','string'], 'rating' => ['required','integer','between:1,5'], 'imageUpload' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120']], 'faqs' => ['question' => ['required','string','max:500'], 'answer' => ['required','string'], 'category' => ['nullable','string','max:255']], }; }
    public function saveDraft(): void { $this->save(false); }
    public function saveAndExit(): void { $this->save(false, true); }
    public function publish(): void { abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, 'publish')), 403); $this->save(true); }
    protected function save(bool $publish, bool $exit = false): void
    {
        abort_unless(auth()->user()->can(CmsPermissions::resourcePermission($this->resource, $this->modelId ? 'update' : 'create')), 403);
        if ($this->imageUpload) abort_unless(auth()->user()->can('media.upload'), 403);
        $this->validate($this->rules());
        $class = CmsContent::model($this->resource);
        $model = $this->modelId ? $class::query()->findOrFail($this->modelId) : new $class;
        $model->fill($this->payload());
        $model->status = $publish ? 'published' : 'draft';
        $model->published_at = $publish ? ($model->published_at ?: now()) : null;

        if ($this->imageUpload) {
            $media = Media::fromUpload($this->imageUpload, ['uploaded_by' => auth()->id(), 'disk' => 'public', 'source_module' => $this->resource === 'team' ? 'Team' : 'Testimonials', 'source_field' => $this->resource === 'team' ? 'Profile photo' : 'Client photo', 'source_record_name' => $this->name ?: 'New record', 'source_label' => $this->resource === 'team' ? 'Profile photo' : 'Client photo']);
            $this->selectedMediaId = $media->id;
            $this->image = Media::normalizePath($media->path);
            $model->{$this->resource === 'team' ? 'profile_image' : 'avatar'} = Media::normalizePath($media->path);
        } elseif ($this->selectedMediaId) {
            $media = Media::query()->where('disk', 'public')->findOrFail($this->selectedMediaId);
            $model->{$this->resource === 'team' ? 'profile_image' : 'avatar'} = Media::normalizePath($media->path);
        } elseif ($this->imageRemoved) {
            $model->{$this->resource === 'team' ? 'profile_image' : 'avatar'} = null;
        }

        $model->save();

        if ($this->imageUpload) {
            $media->update([
                'source_module' => $this->resource === 'team' ? 'Team' : 'Testimonials',
                'source_field' => $this->resource === 'team' ? 'Profile photo' : 'Client photo',
                'source_record_type' => $model::class,
                'source_record_id' => $model->id,
                'source_record_name' => $this->name ?: $model->name ?? 'Record',
                'source_label' => $this->resource === 'team' ? 'Profile photo' : 'Client photo',
            ]);
        } elseif ($this->selectedMediaId) {
            $media = Media::query()->findOrFail($this->selectedMediaId);
            if (blank($media->source_module)) {
                $media->update([
                    'source_module' => $this->resource === 'team' ? 'Team' : 'Testimonials',
                    'source_field' => $this->resource === 'team' ? 'Profile photo' : 'Client photo',
                    'source_record_type' => $model::class,
                    'source_record_id' => $model->id,
                    'source_record_name' => $this->name ?: $model->name ?? 'Record',
                    'source_label' => $this->resource === 'team' ? 'Profile photo' : 'Client photo',
                ]);
            }
        }

        $this->modelId = $model->id;

        if ($exit) {
            session()->flash('cms_toast', [
                'heading' => CmsContent::singular($this->resource).' saved',
                'text' => 'Changes saved successfully. Returning to the '.Str::plural(CmsContent::singular($this->resource)).'.',
                'variant' => 'success',
                'duration' => 4000,
            ]);
            $this->redirectRoute("admin.{$this->resource}.index", navigate: true);
            return;
        }

        Flux::toast(variant: 'success', text: CmsContent::singular($this->resource).' '.($publish ? 'published' : 'saved').' successfully.');
        $this->redirectRoute("admin.{$this->resource}.edit", $model->id, navigate: true);
    }
    protected function payload(): array { return match ($this->resource) { 'team' => ['name'=>$this->name,'slug'=>$this->slug,'designation'=>$this->designation,'short_bio'=>$this->short_bio,'bio'=>$this->bio,'email'=>$this->email,'phone'=>$this->phone,'linkedin_url'=>$this->linkedin_url,'github_url'=>$this->github_url,'twitter_url'=>$this->twitter_url,'sort_order'=>$this->sort_order], 'testimonials' => ['name'=>$this->name,'designation'=>$this->designation,'company'=>$this->company,'quote'=>$this->quote,'rating'=>$this->rating,'sort_order'=>$this->sort_order], 'faqs' => ['question'=>$this->question,'answer'=>$this->answer,'category'=>$this->category,'sort_order'=>$this->sort_order], }; }
    protected function fillFromModel($model): void { foreach (array_keys($this->payload()) as $field) if (isset($model->{$field})) $this->{$field} = $model->{$field}; $this->modelId=$model->id; $this->status=$model->status; $this->image=$this->resource === 'team' ? $model->profile_image : ($model->avatar ?? null); $selected = Media::query()->where('disk', 'public')->where('path', Media::normalizePath((string) ($this->image ?? '')))->first(); $this->selectedMediaId = $selected?->id; }
    public function getHeadingProperty(): string { return ($this->modelId ? 'Edit ' : 'Create ').CmsContent::singular($this->resource); }
}; ?>
<div class="mx-auto max-w-[1100px] space-y-6"><x-admin.page-header :title="$this->heading" description="Keep this content clear, useful, and ready for the public website." /><form wire:submit="saveDraft" class="space-y-6"><section class="border border-[#e5eaf2] bg-white p-6 shadow-[0_2px_12px_rgba(23,32,51,0.04)]"><h2 class="text-base font-bold text-[#172033]">{{ $resource === 'faqs' ? 'Question and answer' : ($resource === 'team' ? 'Basic information' : 'Client story') }}</h2><div class="mt-5 grid gap-5 sm:grid-cols-2">@if ($resource === 'faqs')<div class="sm:col-span-2"><label class="mb-2 block text-sm font-semibold">Question</label><input wire:model="question" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" />@error('question')<p class="text-xs text-red-600">{{ $message }}</p>@enderror</div><div class="sm:col-span-2"><label class="mb-2 block text-sm font-semibold">Answer</label><textarea wire:model="answer" rows="8" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm"></textarea>@error('answer')<p class="text-xs text-red-600">{{ $message }}</p>@enderror</div>@else<div><label class="mb-2 block text-sm font-semibold">Name</label><input wire:model.live="name" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" />@error('name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror</div>@if ($resource === 'team')<div><label class="mb-2 block text-sm font-semibold">Designation</label><input wire:model="designation" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div><div class="sm:col-span-2"><label class="mb-2 block text-sm font-semibold">Short bio</label><textarea wire:model="short_bio" rows="3" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm"></textarea></div><div class="sm:col-span-2"><label class="mb-2 block text-sm font-semibold">Full bio</label><textarea wire:model="bio" rows="7" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm"></textarea></div>@else<div><label class="mb-2 block text-sm font-semibold">Designation</label><input wire:model="designation" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div><div><label class="mb-2 block text-sm font-semibold">Company</label><input wire:model="company" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div><div class="sm:col-span-2"><label class="mb-2 block text-sm font-semibold">Testimonial</label><textarea wire:model="quote" rows="7" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm"></textarea>@error('quote')<p class="text-xs text-red-600">{{ $message }}</p>@enderror</div>@endif@endif</div></section><section class="border border-[#e5eaf2] bg-white p-6"><h2 class="text-base font-bold text-[#172033]">Publishing and profile</h2><div class="mt-5 grid gap-5 sm:grid-cols-2"><div><label class="mb-2 block text-sm font-semibold">Status</label><select wire:model="status" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm"><option value="draft">Draft</option><option value="published">Published</option></select></div><div><label class="mb-2 block text-sm font-semibold">Sort order</label><input wire:model="sort_order" type="number" min="0" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div>@if ($resource === 'faqs')<div><label class="mb-2 block text-sm font-semibold">Category</label><input wire:model="category" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div>@elseif ($resource === 'testimonials')<div><label class="mb-2 block text-sm font-semibold">Rating</label><input wire:model="rating" type="number" min="1" max="5" class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div>@else<div><label class="mb-2 block text-sm font-semibold">Profile image</label><div class="flex gap-2"><input wire:model="imageUpload" type="file" accept="image/jpeg,image/png,image/webp" class="w-full border border-dashed border-[#b8c4d6] p-3 text-xs" /><button type="button" wire:click="openMediaPicker" class="border border-[#dbe2ed] bg-white px-3 py-2 text-xs font-semibold text-[#206bc4]">Library</button></div>@if ($imageUpload)<img src="{{ $imageUpload->temporaryUrl() }}" class="mt-3 size-24 rounded-full object-cover" alt="Profile preview">@elseif ($image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" class="mt-3 size-24 rounded-full object-cover" alt="Current profile image">@endif @if ($image || $imageUpload || $selectedMediaId)<button type="button" wire:click="removeImage" class="mt-2 text-xs font-semibold text-red-600 hover:underline">Remove image</button>@endif</div>@endif</div></section><div class="flex justify-end gap-3"><a href="{{ route("admin.{$resource}.index") }}" wire:navigate class="px-4 py-2.5 text-sm text-[#64748b]">Cancel</a><button class="border border-[#000080] px-5 py-2.5 text-sm font-semibold text-[#000080]">Save draft</button>@can(CmsPermissions::resourcePermission($resource, 'publish'))<button type="button" wire:click="publish" class="bg-[#000080] px-5 py-2.5 text-sm font-semibold text-white">Publish</button>@endcan</div></form>@if ($showMediaPicker)<div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4" role="dialog" aria-modal="true"><div class="w-full max-w-3xl rounded-[8px] border border-[#e5eaf2] bg-white p-4 shadow-xl"><div class="mb-4 flex items-center justify-between gap-3"><div><h3 class="text-lg font-bold text-[#172033]">Choose from Media Library</h3><p class="text-xs text-[#64748b]">Select an existing image from the shared CMS library.</p></div><button type="button" wire:click="closeMediaPicker" class="text-sm text-[#64748b] hover:text-[#172033]">Close</button></div><div class="mb-4"><input type="search" wire:model.live.debounce.300ms="mediaSearch" placeholder="Search media..." class="w-full border border-[#dbe2ed] px-3 py-2.5 text-sm" /></div><div class="grid max-h-[420px] grid-cols-2 gap-3 overflow-y-auto md:grid-cols-4">@php $items = \App\Models\Media::query()->where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])->when($mediaSearch, fn($query) => $query->where('original_name', 'like', '%'.$mediaSearch.'%')->orWhere('title', 'like', '%'.$mediaSearch.'%'))->latest()->limit(24)->get(); @endphp @forelse ($items as $media)<button type="button" wire:click="selectMedia({{ $media->id }})" class="overflow-hidden rounded-[8px] border {{ $selectedMediaId === $media->id ? 'border-[#206bc4] bg-[#f0f4ff]' : 'border-[#e5eaf2] bg-white' }} text-left"><img src="{{ \Illuminate\Support\Facades\Storage::disk($media->disk)->url($media->path) }}" alt="{{ $media->alt_text ?: $media->original_name }}" class="aspect-[4/3] w-full object-cover" /><div class="p-2 text-xs text-[#172033]"><div class="truncate font-semibold">{{ $media->title ?: $media->original_name }}</div><div class="mt-1 text-[#64748b]">{{ strtoupper($media->extension) }}</div></div></button>@empty<div class="col-span-full rounded-[8px] border border-dashed border-[#dbe2ed] bg-[#f8fafc] p-8 text-center text-sm text-[#64748b]">No media found.</div>@endforelse</div></div></div>@endif</div>