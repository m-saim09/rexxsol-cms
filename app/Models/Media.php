<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Support\MediaUploadService;
use App\Models\HomepageSection;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;

#[Fillable([
    'uploaded_by',
    'uploaded_at',
    'disk',
    'path',
    'checksum',
    'original_name',
    'file_name',
    'mime_type',
    'extension',
    'size',
    'width',
    'height',
    'alt_text',
    'alt_text_source',
    'alt_text_status',
    'title',
    'title_source',
    'source_module',
    'source_field',
    'source_record_type',
    'source_record_id',
    'source_record_name',
    'source_label',
])]
class Media extends Model
{
    private const MEDIA_SETTING_KEYS = [
        'branding.logo',
        'branding.dark_logo',
        'branding.favicon',
        'branding.footer_logo',
        'branding.social_image',
    ];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $media) {
            if (SiteSetting::where('media_id', $media->id)->exists()) {
                throw ValidationException::withMessages(['media' => 'This image is used by branding or SEO settings. Replace that selection first.']);
            }
        });
    }

    public static function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#^https?://[^/]+/storage/#', '', $path) ?? $path;
        $path = preg_replace('#^/?(?:public/)?storage/app/public/#', '', $path) ?? $path;
        $path = preg_replace('#^/?storage/#', '', $path) ?? $path;
        $path = preg_replace('#^/?public/#', '', $path) ?? $path;

        return ltrim($path, '/');
    }

    public static function titleFromOriginalName(?string $name): string
    {
        $value = trim((string) ($name ?? ''));
        $value = preg_replace('/\.[^.]+$/', '', $value) ?? $value;
        if (preg_match('/^(?:IMG|IMAGE|PHOTO|PICTURE|SCREENSHOT|SCREEN SHOT|UPLOAD|DOWNLOAD|CAPTURE|FILE|PIC|UNTITLED|MEDIA|PREVIEW)[-_ ]?\d+$/i', $value)
            || preg_match('/^[a-f0-9]{8}-[a-f0-9-]{27,}$/i', $value)
            || preg_match('/^[a-f0-9]{32,}$/i', $value)
            || preg_match('/^[a-z0-9]{32,}$/i', $value)
            || preg_match('/^\d{8}[-_ ]\d{4,6}$/', $value)) {
            return '';
        }
        $value = preg_replace('/[-_]+/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        if ($value === '') {
            return 'Untitled image';
        }

        if (preg_match('#^(?:img|image|photo|picture|screenshot|screen(?:\s+shot)?|upload|download|capture|file|pic|untitled|media|preview)(?:[ _-]?\d+|[a-f0-9]{8,})*$#i', $value)) {
            return '';
        }

        if (preg_match('#^(?:[a-f0-9]{8,}|\d{8,}|\d{2,4}[-_/]\d{1,2}[-_/]\d{1,4}|[a-z0-9]{32,})$#i', $value)) {
            return '';
        }

        return ucfirst($value);
    }

    public static function suggestedAltText(?string $titleOrName): ?string
    {
        $value = trim((string) ($titleOrName ?? ''));
        if ($value === '') {
            return null;
        }
        if (static::titleFromOriginalName($value) === '') {
            return null;
        }

        $value = preg_replace('/\.[^.]+$/', '', $value) ?? $value;
        if (preg_match('/^(?:IMG|IMAGE|PHOTO|PICTURE|SCREENSHOT|SCREEN SHOT|UPLOAD|DOWNLOAD|CAPTURE|FILE|PIC|UNTITLED|MEDIA|PREVIEW)[-_ ]?\d+$/i', $value)
            || preg_match('/^[a-f0-9]{8}-[a-f0-9-]{27,}$/i', $value)
            || preg_match('/^[a-f0-9]{32,}$/i', $value)
            || preg_match('/^[a-z0-9]{32,}$/i', $value)
            || preg_match('/^\d{8}[-_ ]\d{4,6}$/', $value)) {
            return null;
        }
        $value = preg_replace('/[-_]+/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('#^(?:img|image|photo|picture|screenshot|screen(?:\s+shot)?|upload|download|capture|file|pic|untitled|media|preview)(?:\s+[0-9A-Fa-f]+)?$#i', $value)) {
            return null;
        }

        if (preg_match('#^(?:[A-Fa-f0-9]{8,}|\d{4,}[-_/ ]\d{1,2}[-_/ ]\d{1,4}|\d{8,}|[A-Fa-f0-9]{8,}(?:[-_/ ][0-9A-Fa-f]+)*)$#', $value)) {
            return null;
        }

        return trim($value);
    }

    public static function fromUpload(UploadedFile $file, array $meta = []): self
    {
        return app(MediaUploadService::class)->register($file, $meta);
    }

    public static function registerUpload(UploadedFile $file, array $meta = []): self
    {
        $disk = $meta['disk'] ?? config('filesystems.default');
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (! in_array($mime, $allowed, true)) {
            throw ValidationException::withMessages(['media' => 'Only JPG, PNG, WebP, and GIF images are allowed.']);
        }

        $size = $file->getSize();
        if ($size > 5 * 1024 * 1024) {
            throw ValidationException::withMessages(['media' => 'The uploaded file exceeds the 5MB size limit.']);
        }

        $realPath = $file->getRealPath();
        if (! is_string($realPath) || ! is_file($realPath)) {
            throw ValidationException::withMessages(['media' => 'The file could not be read for upload.']);
        }

        $dimensions = @getimagesize($realPath) ?: [];
        if (empty($dimensions) || ! isset($dimensions[0], $dimensions[1]) || $dimensions[0] <= 0 || $dimensions[1] <= 0) {
            throw ValidationException::withMessages(['media' => 'The uploaded file is not a valid image.']);
        }

        $checksum = hash_file('sha256', $realPath);
        $existing = static::query()->where('disk', $disk)->where('checksum', $checksum)->first();
        if ($existing) {
            return $existing;
        }

        $originalName = (string) $file->getClientOriginalName();
        $title = static::titleFromOriginalName($originalName);
        $suggestedAlt = static::suggestedAltText($title !== '' ? $title : $originalName);
        $altText = $meta['alt_text'] ?? $suggestedAlt;
        $altStatus = $altText ? 'suggested' : 'needs_description';
        $altSource = array_key_exists('alt_text', $meta) && $meta['alt_text'] !== null ? 'manual' : ($suggestedAlt ? 'suggested' : null);

        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'png');
        $fileName = Str::uuid()->toString().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'media', '-').'.'.$extension;
        $relativePath = $file->storeAs('cms/media', $fileName, $disk);

        if (! is_string($relativePath) || $relativePath === '') {
            throw ValidationException::withMessages(['media' => 'The image could not be stored in the Media Library.']);
        }

        try {
            $record = static::create([
            'uploaded_by' => $meta['uploaded_by'] ?? auth()->id(),
            'disk' => $disk,
            'path' => $relativePath,
            'checksum' => $checksum,
            'original_name' => $originalName,
            'file_name' => $fileName,
            'mime_type' => $mime,
            'extension' => $extension,
            'size' => $size,
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'alt_text' => $altText,
            'alt_text_source' => $altSource,
            'alt_text_status' => $altStatus,
            'title' => $title !== '' ? $title : ($meta['title'] ?? null),
            'title_source' => ($title !== '' ? 'filename' : 'manual'),
            'source_module' => $meta['source_module'] ?? 'Media Library',
            'source_field' => $meta['source_field'] ?? 'Direct upload',
            'source_record_type' => $meta['source_record_type'] ?? null,
            'source_record_id' => $meta['source_record_id'] ?? null,
            'source_record_name' => $meta['source_record_name'] ?? null,
            'source_label' => $meta['source_label'] ?? null,
            'uploaded_at' => now(),
            ]);
        } catch (QueryException $exception) {
            Storage::disk($disk)->delete($relativePath);
            $duplicate = static::query()->where('disk', $disk)->where('checksum', $checksum)->first();
            if ($duplicate) {
                return $duplicate;
            }

            throw $exception;
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($relativePath);
            throw $exception;
        }

        return $record;
    }

    public static function hashForPath(string $disk, string $path): ?string
    {
        $normalizedPath = static::normalizePath($path);
        $storage = Storage::disk($disk);
        if (! $storage->exists($normalizedPath)) {
            return null;
        }

        return hash_file('sha256', $storage->path($normalizedPath));
    }

    public function sourceLabel(): string
    {
        if (empty($this->source_module) && empty($this->source_field)) {
            return 'Original upload source unknown';
        }

        $module = trim((string) ($this->source_module ?: 'Media Library'));
        $field = trim((string) ($this->source_field ?: 'Direct upload'));

        if ($this->source_record_name && $this->source_record_id) {
            $deleted = ! $this->sourceRecordExists();

            return $module.' → '.$this->source_record_name.' → '.$field.($deleted ? ' (Record deleted)' : '');
        }

        if ($this->source_record_name) {
            return $module.' → '.$this->source_record_name;
        }

        if ($this->source_field) {
            return $module.' → '.$field;
        }

        return $module;
    }

    public function currentUsage(): array
    {
        $normalized = self::normalizePath($this->path);
        $items = [];

        $scan = function (string $label, $query, string $field, ?string $route = null, ?string $permission = null) use (&$items, $normalized): void {
            foreach ($query->withTrashed()->get() as $record) {
                $value = $record->{$field} ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                $pathValue = is_array($value) ? $value : [$value];
                $matches = collect($pathValue)->map(fn ($item) => self::normalizePath((string) $item))->contains($normalized);
                if (! $matches) {
                    continue;
                }

                $items[] = [
                    'label' => $label.' → '.($record->title ?? $record->name ?? $record->slug ?? 'Record').(method_exists($record, 'trashed') && $record->trashed() ? ' (archived)' : ''),
                    'route' => $route && Route::has($route) && $permission && ! (method_exists($record, 'trashed') && $record->trashed()) && auth()->user()?->can($permission)
                        ? route($route, ['model' => $record->id])
                        : null,
                    'permission' => $permission,
                    'record' => $record,
                ];
            }
        };

        $scan('Pages → Featured image', Page::withTrashed()->whereNotNull('featured_image'), 'featured_image', 'admin.pages.edit', 'pages.view');
        $scan('Services → Featured image', Service::withTrashed()->whereNotNull('featured_image'), 'featured_image', 'admin.services.edit', 'services.view');
        $scan('Projects → Featured image', Project::withTrashed()->whereNotNull('featured_image'), 'featured_image', 'admin.projects.edit', 'projects.view');
        $scan('Projects → Gallery image', Project::withTrashed()->whereNotNull('gallery'), 'gallery', 'admin.projects.edit', 'projects.view');
        $scan('Blog → Featured image', BlogPost::withTrashed()->whereNotNull('featured_image'), 'featured_image', 'admin.blog.edit', 'blog.view');
        $scan('Team → Profile photo', TeamMember::withTrashed()->whereNotNull('profile_image'), 'profile_image', 'admin.team.edit', 'team.view');
        $scan('Testimonials → Avatar', Testimonial::withTrashed()->whereNotNull('avatar'), 'avatar', 'admin.testimonials.edit', 'testimonials.view');
        foreach (BlogPost::withTrashed()->whereNotNull('content')->get(['id', 'title', 'content']) as $post) {
            if (in_array($normalized, self::inlineImagePaths((string) $post->content), true)) {
                $items[] = [
                    'label' => 'Blog → '.($post->title ?: 'Article').' → Article content image',
                    'route' => auth()->user()?->can('blog.view') && Route::has('admin.blog.edit') ? route('admin.blog.edit', ['model' => $post->id]) : null,
                    'permission' => 'blog.view',
                    'record' => $post,
                ];
            }
        }

        $settings = SiteSetting::query()->get()->filter(function (SiteSetting $setting) use ($normalized): bool {
            return (int) $setting->media_id === (int) $this->id
                || (in_array($setting->key, self::MEDIA_SETTING_KEYS, true) && is_numeric($setting->value) && (int) $setting->value === (int) $this->id)
                || (is_string($setting->value) && self::normalizePath($setting->value) === $normalized);
        });
        foreach ($settings as $setting) {
            $items[] = [
                'label' => 'Settings → '.str_replace(['branding.', 'site.', 'seo.', 'social.', 'contact.'], '', (string) $setting->key),
                'route' => auth()->user()?->can('settings.view') && Route::has('admin.site-settings') ? route('admin.site-settings') : null,
                'permission' => 'settings.view',
                'record' => $setting,
            ];
        }
        foreach (HomepageSection::query()->get() as $section) {
            foreach (['hero_image', 'about_image', 'cta_background'] as $field) {
                $value = data_get($section->settings, $field);
                if ((string) $value === (string) $this->id || (is_string($value) && self::normalizePath($value) === $normalized)) {
                    $items[] = [
                        'label' => 'Homepage → '.$section->name.' → '.str_replace('_', ' ', $field),
                        'route' => auth()->user()?->can('homepage_builder.view') && Route::has('admin.homepage') ? route('admin.homepage') : null,
                        'permission' => 'homepage_builder.view',
                        'record' => $section,
                    ];
                }
            }
        }

        usort($items, fn ($a, $b) => strcmp((string) $a['label'], (string) $b['label']));

        return $items;
    }

    public static function usageCountsFor(iterable $assets): array
    {
        $pathIds = [];
        $counts = [];
        $ids = [];
        foreach ($assets as $asset) {
            $pathIds[self::normalizePath($asset->path)] = $asset->id;
            $counts[$asset->id] = 0;
            $ids[] = $asset->id;
        }

        $add = static function (?string $path) use (&$counts, $pathIds): void {
            $id = $pathIds[self::normalizePath((string) $path)] ?? null;
            if ($id) {
                $counts[$id]++;
            }
        };
        foreach (Page::withTrashed()->whereNotNull('featured_image')->get(['featured_image']) as $record) $add($record->featured_image);
        foreach (Service::withTrashed()->whereNotNull('featured_image')->get(['featured_image']) as $record) $add($record->featured_image);
        foreach (Project::withTrashed()->whereNotNull('featured_image')->get(['featured_image']) as $record) $add($record->featured_image);
        foreach (Project::withTrashed()->whereNotNull('gallery')->get(['gallery']) as $record) foreach (($record->gallery ?? []) as $path) $add($path);
        foreach (BlogPost::withTrashed()->whereNotNull('featured_image')->get(['featured_image']) as $record) $add($record->featured_image);
        foreach (TeamMember::withTrashed()->whereNotNull('profile_image')->get(['profile_image']) as $record) $add($record->profile_image);
        foreach (Testimonial::withTrashed()->whereNotNull('avatar')->get(['avatar']) as $record) $add($record->avatar);
        foreach (SiteSetting::query()->get(['key', 'media_id', 'value']) as $setting) {
            $mediaId = $setting->media_id;
            if (! $mediaId && in_array($setting->key, self::MEDIA_SETTING_KEYS, true) && is_numeric($setting->value)) {
                $mediaId = (int) $setting->value;
            }
            if ($mediaId && isset($counts[$mediaId])) {
                $counts[$mediaId]++;
            } elseif (is_string($setting->value)) {
                $path = self::normalizePath($setting->value);
                if (isset($pathIds[$path])) $counts[$pathIds[$path]]++;
            }
        }
        foreach (HomepageSection::query()->whereNotNull('settings')->get(['settings']) as $section) {
            foreach (['hero_image', 'about_image', 'cta_background'] as $field) {
                $value = data_get($section->settings, $field);
                if (isset($counts[(int) $value])) {
                    $counts[(int) $value]++;
                } elseif (is_string($value)) {
                    $path = self::normalizePath($value);
                    if (isset($pathIds[$path])) $counts[$pathIds[$path]]++;
                }
            }
        }
        foreach (BlogPost::withTrashed()->whereNotNull('content')->get(['content']) as $post) {
            foreach (array_unique(self::inlineImagePaths((string) $post->content)) as $path) {
                if (isset($pathIds[$path])) $counts[$pathIds[$path]]++;
            }
        }

        return $counts;
    }

    public static function inlineImagePaths(string $html): array
    {
        preg_match_all('/<img\b[^>]*\bsrc\s*=\s*(?:"([^"]+)"|\'([^\']+)\'|([^\s>]+))/i', $html, $matches, PREG_SET_ORDER);
        $paths = [];

        foreach ($matches as $match) {
            $value = html_entity_decode($match[1] ?: ($match[2] ?: ($match[3] ?? '')), ENT_QUOTES | ENT_HTML5);
            if ($value !== '' && ! preg_match('#^https?://[^/]+/(?!storage/)#i', $value)) {
                $paths[] = self::normalizePath($value);
            } elseif (preg_match('#^https?://[^/]+/storage/(.+)$#i', $value, $urlMatch)) {
                $paths[] = self::normalizePath($urlMatch[1]);
            }
        }

        return array_values(array_unique(array_filter($paths)));
    }

    public function hasSuggestedAltText(): bool
    {
        return $this->alt_text_status === 'suggested' || $this->alt_text_status === 'needs_description';
    }

    public function uploadSourceUrl(): ?string
    {
        if (! $this->source_record_id || ! $this->source_record_type) {
            return null;
        }

        $map = [
            BlogPost::class => ['admin.blog.edit', 'blog.view'],
            Page::class => ['admin.pages.edit', 'pages.view'],
            Project::class => ['admin.projects.edit', 'projects.view'],
            Service::class => ['admin.services.edit', 'services.view'],
            TeamMember::class => ['admin.team.edit', 'team.view'],
            Testimonial::class => ['admin.testimonials.edit', 'testimonials.view'],
            HomepageSection::class => ['admin.homepage', 'homepage_builder.view'],
        ];

        [$routeName, $permission] = $map[$this->source_record_type] ?? [null, null];
        if (! $routeName || ! Route::has($routeName) || ! auth()->user()?->can($permission) || ! $this->sourceRecordExists()) {
            return null;
        }

        return $this->source_record_type === HomepageSection::class
            ? route($routeName)
            : route($routeName, ['model' => $this->source_record_id]);
    }

    private function sourceRecordExists(): bool
    {
        return $this->source_record_type
            && class_exists($this->source_record_type)
            && is_subclass_of($this->source_record_type, Model::class)
            && $this->source_record_type::query()->whereKey($this->source_record_id)->exists();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
