<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\HomepageSection;
use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaReferenceBackfill
{
    /**
     * Compare known CMS image references with public Media Library records.
     *
     * @return array{references:int, registered:int, created:int, missing_files:array, ambiguous:array, unsupported:array, dangling_media_ids:array}
     */
    public function run(bool $apply = false): array
    {
        $references = [];
        $add = null;
        $add = static function (mixed $value, string $label) use (&$add, &$references): void {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $add($item, $label);
                }

                return;
            }

            if (! is_string($value) || trim($value) === '') {
                return;
            }

            $value = html_entity_decode(trim($value), ENT_QUOTES | ENT_HTML5);
            $path = Media::normalizePath($value);
            if ($path === '' || str_contains($path, '://') || str_starts_with($path, 'data:') || preg_match('#(^|/)\.\.?(/|$)#', $path)) {
                return;
            }

            $lower = strtolower($path);
            if (str_contains($lower, 'livewire-tmp') || str_contains($lower, '/thumbnail') || str_contains($lower, '/thumbnails/')) {
                return;
            }

            $references[$path][] = $label;
        };

        foreach (Page::withTrashed()->whereNotNull('featured_image')->get(['title', 'featured_image']) as $record) {
            $add($record->featured_image, 'Pages → '.($record->title ?: 'Untitled').' → Featured image');
        }
        foreach (Service::withTrashed()->whereNotNull('featured_image')->get(['title', 'featured_image']) as $record) {
            $add($record->featured_image, 'Services → '.($record->title ?: 'Untitled').' → Featured image');
        }
        foreach (Project::withTrashed()->whereNotNull('featured_image')->get(['name', 'featured_image', 'gallery']) as $record) {
            $label = 'Projects → '.($record->name ?: 'Untitled');
            $add($record->featured_image, $label.' → Featured image');
            $add($record->gallery ?? [], $label.' → Gallery image');
        }
        foreach (BlogPost::withTrashed()->whereNotNull('featured_image')->get(['title', 'featured_image', 'content']) as $record) {
            $label = 'Blog → '.($record->title ?: 'Untitled');
            $add($record->featured_image, $label.' → Featured image');
            foreach (Media::inlineImagePaths((string) $record->content) as $path) {
                $add($path, $label.' → Article content image');
            }
        }
        foreach (BlogPost::withTrashed()->whereNull('featured_image')->whereNotNull('content')->get(['title', 'content']) as $record) {
            $label = 'Blog → '.($record->title ?: 'Untitled');
            foreach (Media::inlineImagePaths((string) $record->content) as $path) {
                $add($path, $label.' → Article content image');
            }
        }
        foreach (TeamMember::withTrashed()->whereNotNull('profile_image')->get(['name', 'profile_image']) as $record) {
            $add($record->profile_image, 'Team → '.($record->name ?: 'Untitled').' → Profile photo');
        }
        foreach (Testimonial::withTrashed()->whereNotNull('avatar')->get(['client_name', 'avatar']) as $record) {
            $add($record->avatar, 'Testimonials → '.($record->client_name ?: 'Untitled').' → Client photo');
        }
        foreach (SiteSetting::query()->whereNull('media_id')->get(['key', 'value']) as $setting) {
            if (in_array($setting->key, ['branding.logo', 'branding.dark_logo', 'branding.favicon', 'branding.footer_logo', 'branding.social_image'], true)
                && is_numeric($setting->value)
                && ! Media::query()->whereKey((int) $setting->value)->exists()) {
                $danglingIds[] = 'Settings → '.$setting->key.' → missing media ID '.$setting->value;
            } elseif (str_starts_with((string) $setting->key, 'branding.') && is_string($setting->value) && ! is_numeric($setting->value)) {
                $add($setting->value, 'Settings → '.str_replace('branding.', '', (string) $setting->key));
            }
        }
        foreach (HomepageSection::query()->get(['name', 'settings']) as $section) {
            foreach (['hero_image', 'about_image', 'cta_background'] as $field) {
                $value = data_get($section->settings, $field);
                if (is_string($value) && ! is_numeric($value)) {
                    $add($value, 'Homepage → '.$section->name.' → '.str_replace('_', ' ', $field));
                }
            }
        }

        $danglingIds = [];
        foreach (SiteSetting::query()->whereNotNull('media_id')->get(['key', 'media_id']) as $setting) {
            if (! Media::query()->whereKey($setting->media_id)->exists()) {
                $danglingIds[] = 'Settings → '.$setting->key.' → missing media ID '.$setting->media_id;
            }
        }
        foreach (HomepageSection::query()->get(['name', 'settings']) as $section) {
            foreach (['hero_image', 'about_image', 'cta_background'] as $field) {
                $value = data_get($section->settings, $field);
                if (is_numeric($value) && ! Media::query()->whereKey((int) $value)->exists()) {
                    $danglingIds[] = 'Homepage → '.$section->name.' → '.$field.' → missing media ID '.$value;
                }
            }
        }

        $report = [
            'references' => count($references),
            'registered' => 0,
            'created' => 0,
            'would_register' => [],
            'checksums_populated' => 0,
            'would_populate_checksums' => [],
            'checksum_shared' => [],
            'checksum_ambiguous' => [],
            'media_files_missing' => [],
            'missing_files' => [],
            'ambiguous' => [],
            'unsupported' => [],
            'dangling_media_ids' => $danglingIds,
        ];

        $disk = Storage::disk('public');
        $checksumCandidates = [];
        foreach (Media::query()->where('disk', 'public')->whereNull('checksum')->get(['id', 'path']) as $asset) {
            if (! $disk->exists($asset->path)) {
                $report['media_files_missing'][] = $asset->path.' (Media Library record '.$asset->id.')';
                continue;
            }

            $checksum = hash_file('sha256', $disk->path($asset->path));
            if (is_string($checksum)) {
                $checksumCandidates[$checksum][] = $asset;
            }
        }

        foreach ($checksumCandidates as $checksum => $assets) {
            $knownDuplicate = Media::query()->where('disk', 'public')->where('checksum', $checksum)->exists();
            if (count($assets) !== 1 || $knownDuplicate) {
                foreach ($assets as $asset) {
                    $report['checksum_shared'][] = $asset->path.' (identical content is registered under another path; checksum left unset)';
                }

                continue;
            }

            $asset = $assets[0];
            if ($apply) {
                $asset->update(['checksum' => $checksum]);
                $report['checksums_populated']++;
            } else {
                $report['would_populate_checksums'][] = $asset->path;
            }
        }

        foreach ($references as $path => $labels) {
            $existing = Media::query()->where('disk', 'public')->where('path', $path)->get();
            if ($existing->count() === 1) {
                $report['registered']++;
                continue;
            }
            if ($existing->count() > 1) {
                $report['ambiguous'][] = $path.' (multiple Media Library records)';
                continue;
            }
            if (! $disk->exists($path)) {
                $report['missing_files'][] = $path.' — '.implode('; ', array_unique($labels));
                continue;
            }

            $mime = $disk->mimeType($path) ?: 'application/octet-stream';
            if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                $report['unsupported'][] = $path.' ('.$mime.') — '.implode('; ', array_unique($labels));
                continue;
            }

            $absolutePath = $disk->path($path);
            $dimensions = @getimagesize($absolutePath) ?: [];
            if (! isset($dimensions[0], $dimensions[1]) || $dimensions[0] <= 0 || $dimensions[1] <= 0) {
                $report['unsupported'][] = $path.' (invalid image) — '.implode('; ', array_unique($labels));
                continue;
            }

            $storedName = basename($path);
            $likelyGenerated = preg_match('/^[a-f0-9]{8}-[a-f0-9-]{27,}_/i', $storedName) === 1;
            $title = $likelyGenerated ? '' : Media::titleFromOriginalName($storedName);
            $suggestedAlt = $title === '' ? null : Media::suggestedAltText($title);
            $record = [
                'uploaded_by' => null,
                'disk' => 'public',
                'path' => $path,
                'checksum' => hash_file('sha256', $absolutePath),
                'original_name' => $storedName,
                'file_name' => $storedName,
                'mime_type' => $mime,
                'extension' => strtolower(pathinfo($storedName, PATHINFO_EXTENSION)),
                'size' => $disk->size($path),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'title' => $title !== '' ? $title : null,
                'title_source' => $title !== '' ? 'filename' : null,
                'alt_text' => $suggestedAlt,
                'alt_text_source' => $suggestedAlt ? 'suggested' : null,
                'alt_text_status' => $suggestedAlt ? 'suggested' : 'needs_description',
                'source_module' => null,
                'source_field' => null,
                'source_record_type' => null,
                'source_record_id' => null,
                'source_record_name' => null,
                'source_label' => null,
                'uploaded_at' => null,
            ];

            if ($apply) {
                $result = DB::transaction(function () use ($record): string {
                    $samePath = Media::query()->where('disk', 'public')->where('path', $record['path'])->exists();
                    if ($samePath) {
                        return 'registered';
                    }

                    $duplicate = Media::query()->where('disk', $record['disk'])->where('checksum', $record['checksum'])->first();
                    if ($duplicate) {
                        $record['checksum'] = null;
                        Media::query()->create($record);

                        return 'created_shared';
                    }
                    Media::query()->create($record);

                    return 'created';
                });
                if ($result === 'created') {
                    $report['created']++;
                } elseif ($result === 'created_shared') {
                    $report['created']++;
                    $report['checksum_shared'][] = $path.' (registered as a separate existing file with identical content)';
                } elseif ($result === 'registered') {
                    $report['registered']++;
                } else {
                    $report['ambiguous'][] = $path.' (same file content is registered under a different path)';
                }
            } else {
                $report['would_register'][] = $path.' — '.implode('; ', array_unique($labels));
            }
        }

        return $report;
    }
}
