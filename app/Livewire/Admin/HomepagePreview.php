<?php

namespace App\Livewire\Admin;

use App\Support\HomepageContentService;
use App\Support\NavigationService;
use App\Support\PublicSiteSettings;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class HomepagePreview extends Component
{
    public function mount(): void
    {
        Gate::authorize('homepage_builder.view');
    }

    public function render()
    {
        Gate::authorize('homepage_builder.view');

        return view('livewire.admin.homepage-preview', [
            'homepage' => app(HomepageContentService::class)->data(),
            'menus' => app(NavigationService::class)->menus(),
            'settings' => app(PublicSiteSettings::class)->all(),
        ])->layout('layouts.admin')->title('Homepage Preview');
    }
}
