<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'client', 'short_description', 'description', 'featured_image', 'featured_image_alt', 'gallery', 'category', 'technologies', 'project_url', 'completion_date', 'status', 'published_at', 'sort_order', 'seo_title', 'seo_description', 'canonical_url', 'noindex', 'nofollow'])]
class Project extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'completion_date' => 'date', 'gallery' => 'array', 'technologies' => 'array', 'noindex' => 'boolean', 'nofollow' => 'boolean'];
    }
}
