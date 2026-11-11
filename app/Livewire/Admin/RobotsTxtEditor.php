<?php

namespace App\Livewire\Admin;

use App\Support\RobotsTxtConfiguration;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class RobotsTxtEditor extends Component
{
    use WithFileUploads;

    public string $content = '';

    public $upload;

    public bool $blockingConfirmed = false;

    #[Locked]
    public string $baselineHash = '';

    public function mount(): void
    {
        Gate::authorize('seo.view');
        $this->content = app(RobotsTxtConfiguration::class)->content();
        $this->baselineHash = hash('sha256', $this->content);
    }

    public function updatedContent(): void
    {
        $this->blockingConfirmed = false;
        $this->resetValidation();
    }

    public function loadUpload(): void
    {
        Gate::authorize('seo.update');
        Validator::make(['upload' => $this->upload], [
            'upload' => ['required', 'file', 'extensions:txt', 'mimetypes:text/plain', 'max:100'],
        ])->validate();

        $uploadedContent = $this->upload->get();
        if (! is_string($uploadedContent) || str_contains($uploadedContent, "\0") || ! mb_check_encoding($uploadedContent, 'UTF-8')) {
            $this->addError('upload', 'The selected file must be UTF-8 plain text without binary data.');

            return;
        }

        $uploadedContent = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $uploadedContent, -1, $replacements);
        if ($replacements > 0 || ! is_string($uploadedContent)) {
            $this->addError('upload', 'The selected file contains unsupported binary or control characters.');

            return;
        }

        if (strlen($uploadedContent) > 102400) {
            $this->addError('upload', 'Robots.txt content cannot exceed 100 KB.');

            return;
        }

        $this->content = str_replace(["\r\n", "\r"], "\n", preg_replace('/^\xEF\xBB\xBF/', '', $uploadedContent) ?? $uploadedContent);
        $this->blockingConfirmed = false;
        $this->upload = null;
        $this->resetValidation();
        $this->dispatch('robots-content-changed');
        Flux::toast(variant: 'success', text: 'File loaded for review. Save changes to publish it.');
    }

    public function insertSitemap(): void
    {
        Gate::authorize('seo.update');
        abort_unless(\Illuminate\Support\Facades\Route::has('sitemap'), 404);

        $sitemapUrl = app(\App\Support\Seo::class)->baseUrl().'/sitemap.xml';
        if (filter_var($sitemapUrl, FILTER_VALIDATE_URL) === false || ! in_array(parse_url($sitemapUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            $this->addError('content', 'The configured canonical website URL is invalid; the sitemap URL was not inserted.');

            return;
        }

        $existing = collect(preg_split('/\R/', $this->content) ?: [])
            ->map(fn (string $line): string => trim(explode('#', $line, 2)[0]))
            ->contains(fn (string $line): bool => preg_match('/\ASitemap\s*:\s*'.preg_quote($sitemapUrl, '/').'\s*\z/i', $line) === 1);

        if (! $existing) {
            $this->content = rtrim($this->content).($this->content === '' ? '' : "\n")."Sitemap: {$sitemapUrl}\n";
            $this->blockingConfirmed = false;
            $this->resetValidation();
            $this->dispatch('robots-content-changed');
        }
    }

    public function restoreDefaults(): void
    {
        Gate::authorize('seo.update');
        $this->content = app(RobotsTxtConfiguration::class)->defaultContent();
        $this->blockingConfirmed = false;
        $this->resetValidation();
        $this->dispatch('robots-content-changed');
    }

    public function reloadLatest(): void
    {
        Gate::authorize('seo.view');
        $this->content = app(RobotsTxtConfiguration::class)->content();
        $this->baselineHash = hash('sha256', $this->content);
        $this->blockingConfirmed = false;
        $this->resetValidation();
        $this->dispatch('robots-content-changed');
    }

    public function save(): void
    {
        Gate::authorize('seo.update');
        $this->resetValidation();
        $this->content = str_replace(["\r\n", "\r"], "\n", $this->content);

        Validator::make(['content' => $this->content], [
            'content' => ['present', 'string', 'max:102400'],
        ])->validate();
        if (strlen($this->content) > 102400) {
            $this->addError('content', 'Robots.txt content cannot exceed 100 KB.');

            return;
        }

        $analysis = app(RobotsTxtConfiguration::class)->analyze($this->content);
        foreach ($analysis['errors'] as $error) {
            $this->addError('content', "Line {$error['line']}: {$error['message']}");
        }
        if ($analysis['errors'] !== []) {
            return;
        }

        if ($analysis['blocks_all'] && ! $this->blockingConfirmed) {
            $this->addError('blockingConfirmed', 'Confirm that you intend to block crawling across the entire site before saving.');

            return;
        }

        try {
            $saved = app(RobotsTxtConfiguration::class)->saveIfUnchanged(
                $this->baselineHash,
                $this->content,
                (int) auth()->id(),
            );
        } catch (QueryException $exception) {
            report($exception);
            $this->addError('save', 'The robots.txt content could not be saved. The previously published version remains live.');

            return;
        }

        if (! $saved) {
            $this->addError('save', 'Another administrator saved a newer version. Your edits are preserved; reload the latest version before saving again.');

            return;
        }

        $this->baselineHash = hash('sha256', $this->content);
        $this->blockingConfirmed = false;
        Flux::toast(variant: 'success', text: 'Robots.txt changes saved and published.');
    }

    public function render()
    {
        Gate::authorize('seo.view');
        $configuration = app(RobotsTxtConfiguration::class);
        $audit = $configuration->savedBy();

        return view('livewire.admin.robots-txt-editor', [
            'analysis' => $configuration->analyze($this->content),
            'savedAt' => $configuration->savedAt(),
            'savedBy' => $audit?->name,
            'sitemapAvailable' => \Illuminate\Support\Facades\Route::has('sitemap'),
        ])->layout('layouts.admin')->title('Robots.txt Editor');
    }
}
