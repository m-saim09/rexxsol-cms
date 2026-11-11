<?php

namespace App\Livewire\Admin;

use App\Models\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class DataTransfer extends Component
{
    use WithFileUploads;

    public string $tab = 'export';

    public string $type = 'pages';

    public $file;

    public function mount(): void
    {
        Gate::authorize('manage-site-settings');
    }

    public function export(): void
    {
        Gate::authorize('manage-site-settings');
        $rows = match ($this->type) { 'pages' => Page::query()->select(['title', 'slug', 'status', 'published_at'])->get(), default => collect() };
        $csv = $rows->map(fn ($row) => collect($row->toArray())->map(fn ($value) => '="'.str_replace('"', '""', (string) $value).'"')->implode(','))->prepend($rows->first() ? collect($rows->first()->toArray())->keys()->implode(',') : '')->implode("\n");
        $this->dispatch('download-export', content: base64_encode($csv), filename: $this->type.'.csv');
    }

    public function import(): void
    {
        Gate::authorize('manage-site-settings');
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);
        session()->flash('data-import-message', 'File validated. Review the preview before committing imported records.');
    }

    public function render()
    {
        Gate::authorize('manage-site-settings');
        return view('livewire.admin.data-transfer')->layout('layouts.admin')->title('Export/Import Data');
    }
}
