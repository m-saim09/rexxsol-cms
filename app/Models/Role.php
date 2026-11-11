<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['slug', 'name', 'description', 'is_system'])]
class Role extends Model
{
    public const SUPER_ADMIN = 'super_admin';

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public static function clearPermissionCache(?int $roleId = null): void
    {
        $ids = $roleId ? [$roleId] : static::query()->pluck('id')->all();

        foreach ($ids as $id) {
            \Illuminate\Support\Facades\Cache::forget("cms-role-permissions:{$id}");
        }
    }
}
