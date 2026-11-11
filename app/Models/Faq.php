<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['question', 'answer', 'category', 'status', 'published_at', 'sort_order'])]
class Faq extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }
}
