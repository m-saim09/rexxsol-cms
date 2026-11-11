<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Support\SettingsFields;
use App\Support\SiteSettings;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SettingsEditor extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Locked]
    public string $area = 'site';

    public array $values = [];

    public string $section = 'General';

    public string $mediaSearch = '';

    public $mediaUpload;

    #[Locked]
    public ?string $mediaField = null;

    public function mount(): void
    {
        $this->authorizeArea();
        $this->section = array_key_first(SettingsFields::groups($this->area));
        foreach (SettingsFields::fields($this->area) as $name => $field) {
            $this->values[$name] = app(SiteSettings::class)->get($field[0], $field[3] ?? ($field[2] === 'media' ? null : ''));
        }
    }

    protected function authorizeArea(bool $updating = false): void
    {
        $ability = $this->area === 'seo'
            ? ($updating ? 'seo.update' : 'seo.view')
            : ($updating ? 'settings.update' : 'settings.view');

        Gate::authorize($ability);
    }

    public function save(): void
    {
        $this->authorizeArea(true);

        try {
            $validated = $this->validate(SettingsFields::rules($this->area));
        } catch (ValidationException $e) {
            $groups = SettingsFields::groups($this->area);
            $errorKeys = array_keys($e->validator->errors()->toArray());
            foreach ($errorKeys as $errorKey) {
                $fieldName = str_replace('values.', '', $errorKey);
                foreach ($groups as $groupName => $fields) {
                    if (array_key_exists($fieldName, $fields)) {
                        $this->section = $groupName;
                        break 2;
                    }
                }
            }

            throw $e;
        }

        $values = [];
        $mediaKeys = [];
        foreach (SettingsFields::fields($this->area) as $name => $field) {
            $values[$field[0]] = $validated['values'][$name] ?? null;
            if ($field[2] === 'media') {
                $mediaKeys[] = $field[0];
            }
        }
        app(SiteSettings::class)->put($values, $mediaKeys);
        Flux::toast(variant: 'success', text: $this->area === 'seo' ? 'SEO settings saved successfully.' : 'Settings saved successfully.');
    }

    public function uploadMedia(): void
    {
        $this->authorizeArea(true);
        Gate::authorize('media.upload');
        abort_unless($this->mediaField && (SettingsFields::fields($this->area)[$this->mediaField][2] ?? null) === 'media', 422);
        $this->validate(['mediaUpload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120']]);

        $field = SettingsFields::fields($this->area)[$this->mediaField];
        $media = Media::fromUpload($this->mediaUpload, [
            'uploaded_by' => auth()->id(),
            'disk' => 'public',
            'source_module' => 'Settings',
            'source_field' => $field[1],
            'source_record_name' => $field[1],
            'source_label' => $field[1],
        ]);
        $this->values[$this->mediaField] = $media->id;
        $this->mediaUpload = null;
        Flux::toast(variant: 'success', text: 'Image uploaded and selected.');
    }

    public function chooseMedia(string $field): void
    {
        $this->authorizeArea();
        Gate::authorize('media.view');
        abort_unless((SettingsFields::fields($this->area)[$field][2] ?? null) === 'media', 422);
        $this->mediaField = $field;
        $this->mediaSearch = '';
        $this->resetPage('mediaPage');
        Flux::modal('settings-media')->show();
    }

    public function selectMedia(int $id): void
    {
        $this->authorizeArea();
        Gate::authorize('media.view');
        abort_unless($this->mediaField && (SettingsFields::fields($this->area)[$this->mediaField][2] ?? null) === 'media', 422);
        Media::where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])->findOrFail($id);
        $this->values[$this->mediaField] = $id;
        Flux::modal('settings-media')->close();
    }

    public function updatedMediaSearch(): void
    {
        $this->resetPage('mediaPage');
    }

    public function render()
    {
        $this->authorizeArea();
        $groups = SettingsFields::groups($this->area);
        if (! isset($groups[$this->section])) {
            $this->section = array_key_first($groups);
        }
        $ids = collect(SettingsFields::fields($this->area))->filter(fn ($f) => $f[2] === 'media')->keys()->map(fn ($name) => $this->values[$name] ?? null)->filter();

        return view('livewire.admin.settings-editor', [
            'groups' => $groups,
            'selectedMedia' => Media::whereIn('id', $ids)->get()->keyBy('id'),
            'mediaItems' => $this->mediaField ? Media::where('disk', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])->when($this->mediaSearch, fn ($q) => $q->where(fn ($search) => $search->where('original_name', 'like', '%'.$this->mediaSearch.'%')->orWhere('title', 'like', '%'.$this->mediaSearch.'%')->orWhere('alt_text', 'like', '%'.$this->mediaSearch.'%')))->latest()->paginate(8, ['*'], 'mediaPage') : null,
        ])->layout('layouts.admin')->title($this->area === 'seo' ? 'SEO Management' : 'Global Settings');
    }
}
