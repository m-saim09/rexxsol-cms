<?php

namespace App\Models;

use App\Observers\CmsActivityObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'action', 'module', 'subject_type', 'subject_id', 'subject_label', 'changes'])]
class ActivityLog extends Model
{
    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function safeChanges(): array
    {
        return array_intersect_key($this->getAttribute('changes') ?? [], array_flip(CmsActivityObserver::SAFE_FIELDS));
    }
}
