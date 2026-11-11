<?php

namespace App\Livewire\Admin;

use App\Support\SiteSettings;
use App\Support\CustomHtmlConfiguration;
use App\Support\CustomCssConfiguration;
use App\Support\ThemeOptionsConfiguration;
use App\Support\ThemeRegistry;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AppearanceManager extends Component
{
    public string $page = 'themes';

    public string $customCss = '';

    public bool $customCssEnabled = true;

    public string $customCssFingerprint = '';

    public string $customCssSavedAt = '';

    public string $customCssSavedBy = '';

    public string $headerHtml = '';

    public bool $headerHtmlEnabled = false;

    public string $bodyHtml = '';

    public bool $bodyHtmlEnabled = false;

    public string $footerHtml = '';

    public bool $footerHtmlEnabled = false;

    public string $customHtmlFingerprint = '';

    public string $savedAt = '';

    public string $savedBy = '';

    public string $copyright = '';

    public string $brandColor = '#000080';

    public string $headingFont = 'Georgia';

    public string $bodyFont = 'Instrument Sans';

    public string $themeSlug = '';

    public array $themeOptions = [];

    public string $themeOptionsFingerprint = '';

    public string $themeOptionsSavedAt = '';

    public string $themeOptionsSavedBy = '';

    public function mount(string $page = 'themes'): void
    {
        Gate::authorize('appearance.view');
        $this->page = $page;
        $settings = app(SiteSettings::class);
        $this->customCss = (string) $settings->get('appearance.custom_css', '');
        if ($this->page === 'custom-css') {
            $this->loadCustomCssState();
        }
        if ($this->page === 'custom-html') {
            $this->loadCustomHtmlState();
        }
        if ($this->page === 'theme-options') {
            $this->themeSlug = app(ThemeRegistry::class)->active()['slug'];
            $this->loadThemeOptions();
        }
        $this->copyright = (string) $settings->get('theme.copyright', '');
        $this->brandColor = (string) $settings->get('appearance.brand_color', '#000080');
        $this->headingFont = (string) $settings->get('appearance.heading_font', 'Georgia');
        $this->bodyFont = (string) $settings->get('appearance.body_font', 'Instrument Sans');
    }

    public function activateTheme(string $theme): void
    {
        Gate::authorize('appearance.manage');
        abort_unless(app(ThemeRegistry::class)->isRegistered($theme), 422);
        app(SiteSettings::class)->put(['appearance.active_theme' => $theme]);
        Flux::toast(variant: 'success', text: 'Theme activated.');
    }

    public function saveCss(): void
    {
        Gate::authorize('appearance.manage');
        $this->resetValidation();
        $this->validate([
            'customCss' => ['nullable', 'string', 'max:200000'],
            'customCssEnabled' => ['boolean'],
        ]);
        $configuration = app(CustomCssConfiguration::class);
        if (($error = $configuration->validate($this->customCss)) !== null) {
            $this->addError('customCss', $error);

            return;
        }
        if (! $configuration->save($this->customCss, $this->customCssEnabled, $this->customCssFingerprint)) {
            $this->addError('cssConflict', 'Custom CSS changed in another session. Reload the saved version before saving your edits.');

            return;
        }
        $this->customCssFingerprint = $configuration->fingerprint();
        $this->updateCustomCssSaveMetadata($configuration);
        $this->dispatch('custom-css-saved');
        Flux::toast(variant: 'success', text: 'Custom CSS saved.');
    }

    public function reloadCss(): void
    {
        Gate::authorize('appearance.manage');
        $this->resetValidation();
        $this->loadCustomCssState();
        $this->dispatch('custom-css-reloaded', content: $this->customCss);
    }

    private function loadCustomCssState(): void
    {
        $configuration = app(CustomCssConfiguration::class);
        $state = $configuration->state();
        $this->customCss = $state['content'];
        $this->customCssEnabled = $state['enabled'];
        $this->customCssFingerprint = $configuration->fingerprint();
        $this->updateCustomCssSaveMetadata($configuration);
    }

    private function updateCustomCssSaveMetadata(CustomCssConfiguration $configuration): void
    {
        $saved = $configuration->lastSaved();
        $this->customCssSavedAt = $saved?->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? '';
        $this->customCssSavedBy = $saved?->user?->name ?? '';
    }

    public function saveHtml(): void
    {
        Gate::authorize('appearance.manage');
        $this->resetValidation();
        $this->validate([
            'headerHtml' => ['nullable', 'string', 'max:50000'],
            'headerHtmlEnabled' => ['boolean'],
            'bodyHtml' => ['nullable', 'string', 'max:50000'],
            'bodyHtmlEnabled' => ['boolean'],
            'footerHtml' => ['nullable', 'string', 'max:50000'],
            'footerHtmlEnabled' => ['boolean'],
        ]);

        $configuration = app(CustomHtmlConfiguration::class);
        foreach (['header', 'body', 'footer'] as $section) {
            $content = $this->{$section.'Html'};
            $error = $configuration->validationError($section, $content);
            if ($error !== null) {
                $this->addError($section.'Html', $error);
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $sections = [
            'header' => ['content' => $this->headerHtml, 'enabled' => $this->headerHtmlEnabled],
            'body' => ['content' => $this->bodyHtml, 'enabled' => $this->bodyHtmlEnabled],
            'footer' => ['content' => $this->footerHtml, 'enabled' => $this->footerHtmlEnabled],
        ];
        if (! $configuration->save($sections, $this->customHtmlFingerprint)) {
            $this->addError('htmlConflict', 'These settings changed in another session. Reload the saved version before saving your edits.');

            return;
        }

        $this->customHtmlFingerprint = $configuration->fingerprint();
        $this->updateCustomHtmlSaveMetadata($configuration);
        $this->dispatch('custom-html-saved');
        Flux::toast(variant: 'success', text: 'Custom HTML saved.');
    }

    public function reloadCustomHtml(): void
    {
        Gate::authorize('appearance.manage');
        $this->resetValidation();
        $this->loadCustomHtmlState();
    }

    private function loadCustomHtmlState(): void
    {
        $configuration = app(CustomHtmlConfiguration::class);
        $sections = $configuration->editableSections();
        $this->headerHtml = $sections['header']['content'];
        $this->headerHtmlEnabled = $sections['header']['enabled'];
        $this->bodyHtml = $sections['body']['content'];
        $this->bodyHtmlEnabled = $sections['body']['enabled'];
        $this->footerHtml = $sections['footer']['content'];
        $this->footerHtmlEnabled = $sections['footer']['enabled'];
        $this->customHtmlFingerprint = $configuration->fingerprint();
        $this->updateCustomHtmlSaveMetadata($configuration);
    }

    private function updateCustomHtmlSaveMetadata(CustomHtmlConfiguration $configuration): void
    {
        $saved = $configuration->lastSaved();
        $this->savedAt = $saved?->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? '';
        $this->savedBy = $saved?->user?->name ?? '';
    }

    public function saveThemeOptions(): void
    {
        Gate::authorize('appearance.manage');
        $this->resetValidation();
        $theme = app(ThemeRegistry::class)->find($this->themeSlug);
        abort_unless($theme !== null, 422, 'The selected theme is not registered.');

        $configuration = app(ThemeOptionsConfiguration::class);
        $errors = $configuration->validate($theme, $this->themeOptions);
        foreach ($errors as $key => $message) {
            $this->addError('themeOptions.'.$key, $message);
        }
        if ($errors !== []) {
            return;
        }
        if (! $configuration->save($theme, $this->themeOptions, $this->themeOptionsFingerprint)) {
            $this->addError('themeOptionsConflict', 'Theme options changed in another session. Reload the saved version before saving your edits.');

            return;
        }

        $this->themeOptionsFingerprint = $configuration->fingerprint($theme);
        $this->updateThemeOptionsSaveMetadata();
        $this->dispatch('theme-options-saved');
        Flux::toast(variant: 'success', text: 'Theme options saved.');
    }

    public function selectTheme(string $slug): void
    {
        Gate::authorize('appearance.view');
        $theme = app(ThemeRegistry::class)->find($slug);
        abort_unless($theme !== null, 404);
        $this->themeSlug = $slug;
        $this->resetValidation();
        $this->loadThemeOptions();
        $this->dispatch('theme-options-loaded', values: $this->themeOptions);
    }

    public function restoreThemeDefaults(): void
    {
        Gate::authorize('appearance.manage');
        $theme = app(ThemeRegistry::class)->find($this->themeSlug);
        abort_unless($theme !== null, 404);
        $configuration = app(ThemeOptionsConfiguration::class);
        $this->themeOptions = $configuration->defaults($theme);
        $this->resetValidation();
        $this->dispatch('theme-options-defaults-loaded', values: $this->themeOptions);
    }

    private function loadThemeOptions(): void
    {
        $theme = app(ThemeRegistry::class)->find($this->themeSlug);
        abort_unless($theme !== null, 404);
        $configuration = app(ThemeOptionsConfiguration::class);
        $this->themeOptions = $configuration->values($theme);
        $this->themeOptionsFingerprint = $configuration->fingerprint($theme);
        $this->updateThemeOptionsSaveMetadata();
    }

    private function updateThemeOptionsSaveMetadata(): void
    {
        $saved = \App\Models\ActivityLog::query()
            ->with('user')
            ->where('subject_type', 'theme_options')
            ->where('module', 'Appearance')
            ->where('subject_label', app(ThemeRegistry::class)->find($this->themeSlug)['name'].' Theme Options')
            ->latest('id')
            ->first();
        $this->themeOptionsSavedAt = $saved?->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? '';
        $this->themeOptionsSavedBy = $saved?->user?->name ?? '';
    }

    public function render()
    {
        Gate::authorize('appearance.view');
        $themes = $this->themeDetails();
        $selectedTheme = app(ThemeRegistry::class)->find($this->themeSlug ?: app(ThemeRegistry::class)->active()['slug']);

        return view('livewire.admin.appearance-manager', [
            'themes' => $themes,
            'selectedTheme' => $selectedTheme,
            'themeOptionSchema' => $selectedTheme ? app(ThemeOptionsConfiguration::class)->schema($selectedTheme) : [],
            'availableFonts' => $selectedTheme ? app(ThemeOptionsConfiguration::class)->availableFonts($selectedTheme) : [],
            'activeThemeSlug' => app(ThemeRegistry::class)->active()['slug'],
        ])->layout('layouts.admin')->title($this->title());
    }

    public function title(): string
    {
        return ['themes' => 'Themes', 'theme-options' => 'Theme Options', 'custom-css' => 'Custom CSS', 'custom-html' => 'Custom HTML'][$this->page] ?? 'Appearance';
    }

    private function themeDetails(): array
    {
        $active = app(ThemeRegistry::class)->active()['slug'];

        return collect(app(ThemeRegistry::class)->all())->map(fn (array $theme): array => [
            ...$theme,
            'key' => $theme['slug'],
            'active' => $theme['slug'] === $active,
        ])->values()->all();
    }

}
