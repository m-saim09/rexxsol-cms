<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\Media;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class HomepageContentService
{
    public const CACHE_KEY = 'cms.public-homepage.v2';

    private const TESTIMONIAL_FALLBACKS = [
        [
            'title' => 'Erin Hall',
            'designation' => 'Operations Director',
            'company' => 'Northlane',
            'quote' => 'The project gave us a better system and a calmer delivery rhythm. It felt practical from day one.',
        ],
        [
            'title' => 'Samir Khan',
            'designation' => 'Founder',
            'company' => 'Harbor Labs',
            'quote' => 'We needed alignment, not more complexity. The process made our next steps much clearer.',
        ],
        [
            'title' => 'Sofia Chen',
            'designation' => 'VP Product',
            'company' => 'Summit Group',
            'quote' => 'The experience was structured, transparent, and focused on real operational outcomes.',
        ],
    ];

    private const COLLECTIONS = [
        'services' => Service::class,
        'projects' => Project::class,
        'testimonials' => Testimonial::class,
        'team' => TeamMember::class,
        'blog' => BlogPost::class,
        'faqs' => Faq::class,
    ];

    public function data(): array
    {
        $data = Cache::remember(self::CACHE_KEY, 3600, fn (): array => $this->build());
        $servicesSection = HomepageSection::query()->where('key', 'services')->where('enabled', true)->first();

        if ($servicesSection) {
            $data['services'] = [
                'key' => $servicesSection->key,
                'name' => $servicesSection->name,
                'settings' => $this->settings($servicesSection),
                'items' => $this->items($servicesSection),
            ];
        } else {
            unset($data['services']);
        }

        return $data;
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function build(): array
    {
        HomepageSection::ensureDefaults();

        return HomepageSection::query()->where('enabled', true)->orderBy('sort_order')->orderBy('id')->get()
            ->mapWithKeys(fn (HomepageSection $section): array => [$section->key => [
                'key' => $section->key,
                'name' => $section->name,
                'settings' => $this->settings($section),
                'items' => $this->items($section),
            ]])->all();
    }

    private function settings(HomepageSection $section): array
    {
        $settings = $section->settings ?? [];
        unset($settings['selected_ids']);
        foreach (['hero_image', 'about_image', 'cta_background'] as $field) {
            if (! empty($settings[$field])) {
                $value = $settings[$field];
                $media = is_numeric($value)
                    ? Media::whereKey((int) $value)->where('disk', 'public')->first()
                    : Media::where('path', Media::normalizePath((string) $value))->where('disk', 'public')->first();
                $mode = $settings[$field.'_alt_mode'] ?? 'default';
                $settings[$field.'_alt'] = match ($mode) {
                    'decorative' => '',
                    'custom' => (string) ($settings[$field.'_alt'] ?? ''),
                    default => (string) ($media?->alt_text ?? ''),
                };
                $path = $media?->path ?? (is_numeric($value) ? null : Media::normalizePath((string) $value));
                $settings[$field] = $path ? \App\Support\ContentImage::url($path) : null;
            }
        }

        return $settings;
    }

    private function items(HomepageSection $section): array
    {
        $model = self::COLLECTIONS[$section->key] ?? null;
        if (! $model) {
            return [];
        }

        $settings = $section->settings ?? [];
        $query = $model === Service::class
            ? $model::query()->publiclyVisible()
            : $model::query()->where('status', 'published');
        if ($model !== Service::class) {
            $query->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
        }

        if ($section->key === 'testimonials') {
            $realItems = $query
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($record): array => $this->publicRecord($record, $section->key))
                ->values();
            $fallbackItems = array_slice(self::TESTIMONIAL_FALLBACKS, 0, max(0, count(self::TESTIMONIAL_FALLBACKS) - $realItems->count()));

            return array_merge($realItems->all(), $fallbackItems);
        }

        $selected = array_values(array_filter(array_map('intval', $settings['selected_ids'] ?? [])));
        if ($section->key === 'services') {
            $records = $query->orderBy('sort_order')->orderBy('id')->get();
        } elseif ($selected) {
            $query->whereIn('id', $selected);
            $records = $query->get()->sortBy(fn ($record) => array_search($record->id, $selected, true));
        } else {
            $records = $query->orderBy('sort_order')->orderByDesc('published_at')->limit(min(12, max(1, (int) ($settings['limit'] ?? 6))))->get();
        }

        if ($section->key !== 'services') {
            $records = $records->take(min(12, max(1, (int) ($settings['limit'] ?? 6))));
        }

        return $records->map(fn ($record): array => $this->publicRecord($record, $section->key))->values()->all();
    }

    private function publicRecord(object $record, string $key): array
    {
        $title = in_array($key, ['services', 'projects', 'team', 'testimonials'], true) ? $record->name : ($key === 'faqs' ? $record->question : $record->title);
        $imagePath = $record->featured_image ?? $record->profile_image ?? $record->avatar ?? null;
        $imageAltField = match ($key) {
            'team' => 'profile_image_alt',
            'testimonials' => 'avatar_alt',
            default => 'featured_image_alt',
        };
        $imageAlt = ContentImage::alt($imagePath, $record->{$imageAltField} ?? null, $title);

        return array_filter([
            'id' => $record->id,
            'title' => $title,
            'slug' => $record->slug ?? null,
            'description' => $record->short_description ?? $record->excerpt ?? $record->description ?? null,
            'icon' => $key === 'services' ? ($record->icon ?? null) : null,
            'image' => $this->imageUrl($imagePath),
            'image_alt' => $imageAlt,
            'quote' => $record->quote ?? null,
            'designation' => $record->designation ?? null,
            'company' => $record->company ?? null,
            'answer' => $record->answer ?? null,
        ], fn ($value, $field): bool => $field === 'image_alt' || ($value !== null && $value !== ''), ARRAY_FILTER_USE_BOTH);
    }

    private function imageUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
