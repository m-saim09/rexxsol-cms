<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'designation', 'company', 'quote', 'avatar', 'avatar_alt', 'rating', 'status', 'published_at', 'sort_order'])]
class Testimonial extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'rating' => 'integer'];
    }
}
