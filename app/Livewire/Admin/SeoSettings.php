<?php

namespace App\Livewire\Admin;

class SeoSettings extends SettingsEditor
{
    public function mount(): void
    {
        $this->area = 'seo';
        parent::mount();
    }
}
