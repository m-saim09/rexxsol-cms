<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SiteSettings
{
    public const CACHE_KEY = 'cms.site-settings.v1';

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, fn () => SiteSetting::all()->mapWithKeys(fn ($row) => [$row->key => $row->media_id ?? $row->value])->all());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function put(array $values, array $mediaKeys = []): void
    {
        DB::transaction(function () use ($values, $mediaKeys) {
            foreach ($values as $key => $value) {
                SiteSetting::updateOrCreate(['key' => $key], [
                    'value' => in_array($key, $mediaKeys, true) ? null : $value,
                    'media_id' => in_array($key, $mediaKeys, true) ? ($value ?: null) : null,
                ]);
            }
        });
        Cache::forget(self::CACHE_KEY);
    }
}
