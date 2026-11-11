<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CmsActivityObserver
{
    // An allowlist deliberately excludes content, contact messages and all credentials.
    public const SAFE_FIELDS = ['name', 'title', 'label', 'slug', 'status', 'role', 'is_active', 'enabled', 'sort_order', 'published_at', 'media_id', 'content_sha256', 'content_bytes'];

    public function created(Model $model): void
    {
        $this->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        $action = $model instanceof \App\Models\User && $model->wasChanged('role')
            ? 'role-assigned'
            : ($model->wasChanged('status') && $model->status === 'published' ? 'published' : 'updated');
        $this->record($model, $action);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->record($model, 'restored');
    }

    private function record(Model $model, string $action): void
    {
        $changes = [];
        foreach (self::SAFE_FIELDS as $field) {
            if ($model->wasChanged($field)) {
                $changes[$field] = ['old' => $model->getRawOriginal($field), 'new' => $model->getAttributes()[$field] ?? null];
            }
        }
        ActivityLog::create([
            'user_id' => auth()->id(), 'action' => $action,
            'module' => Str::headline(class_basename($model)),
            'subject_type' => $model::class, 'subject_id' => $model->getKey(),
            'subject_label' => Str::limit((string) ($model->title ?: $model->name ?: $model->label ?: $model->key ?: $model->original_name ?: class_basename($model).' #'.$model->getKey()), 250, ''),
            'changes' => $changes,
        ]);
    }
}
