<?php

namespace App\Models;

use App\Support\SiteSettings;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['menu', 'label', 'type', 'target_type', 'target_id', 'url', 'target', 'enabled', 'sort_order', 'parent_id', 'is_automatic', 'automatic_label'])]
class NavigationItem extends Model
{
    public const MENUS = ['primary', 'footer'];

    public const TYPES = ['page', 'service', 'project', 'blog', 'contact', 'system', 'custom', 'column'];

    public static function allMenuKeys(): array
    {
        $menus = array_merge(self::MENUS, self::query()->select('menu')->distinct()->pluck('menu')->filter()->all());

        $settings = app(SiteSettings::class)->get('cms.navigation.menus', []);
        if (is_array($settings)) {
            $menus = array_merge($menus, $settings);
        }

        return array_values(array_unique(array_filter(array_map('strval', $menus), fn (string $menu): bool => $menu !== '')));
    }

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'is_automatic' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function isColumn(): bool
    {
        return $this->menu === 'footer' && $this->parent_id === null;
    }

    public function isTargetUnavailable(): bool
    {
        if (in_array($this->type, ['custom', 'system', 'column', 'contact'], true)) {
            return false;
        }

        if (! $this->target_id) {
            return true;
        }

        $modelClass = match ($this->type) {
            'page' => Page::class,
            'service' => Service::class,
            'project' => Project::class,
            'blog' => BlogPost::class,
            default => null,
        };

        if (! $modelClass) {
            return false;
        }

        $record = $modelClass::find($this->target_id);
        if (! $record) {
            return true;
        }

        if ($this->type === 'service') {
            return $record->status !== 'published' || ! $record->is_active;
        }

        if ($record->status !== 'published') {
            return true;
        }

        if (isset($record->published_at) && $record->published_at && $record->published_at->isFuture()) {
            return true;
        }

        return false;
    }
}
