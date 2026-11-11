<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['key', 'value', 'media_id'])]
class SiteSetting extends Model
{
    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
