<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PluginManager extends Component
{
    public string $page = 'index';

    public function mount(string $page = 'index'): void
    {
        Gate::authorize('manage-site-settings');
        $this->page = $page;
    }

    public function render()
    {
        Gate::authorize('manage-site-settings');
        return view('livewire.admin.plugin-manager', ['plugins' => []])->layout('layouts.admin')->title($this->page === 'create' ? 'Add New Plugin' : 'Installed Plugins');
    }
}
