<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'short_description', 'description', 'icon', 'featured_image', 'featured_image_alt', 'status', 'published_at', 'sort_order', 'seo_title', 'seo_description', 'is_active', 'canonical_url', 'noindex', 'nofollow'])]
class Service extends Model
{
    use SoftDeletes;

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where('is_active', true)
            ->where(fn (Builder $query): Builder => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'is_active' => 'boolean', 'noindex' => 'boolean', 'nofollow' => 'boolean'];
    }
}
