<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'designation', 'short_bio', 'bio', 'profile_image', 'profile_image_alt', 'email', 'phone', 'linkedin_url', 'github_url', 'twitter_url', 'status', 'published_at', 'sort_order'])]
class TeamMember extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }
}
