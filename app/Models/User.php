<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Role;
use App\Support\CmsPermissions;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    protected $attributes = ['is_active' => true];

    protected static function booted(): void
    {
        static::deleting(fn (self $user) => $user->ensureCanBeDeleted());
    }

    public function ensureCanBeDeleted(): void
    {
        if ($this->role === Role::SUPER_ADMIN) {
            throw ValidationException::withMessages(['password' => 'A Super Admin account cannot be deleted. Another Super Admin must first change its role.']);
        }
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function setRoleAttribute(UserRole|string $role): void
    {
        $this->attributes['role'] = $role instanceof UserRole ? $role->value : $role;
    }

    public function roleRecord(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->roleRecord?->name ?? str($this->role)->replace('_', ' ')->title()->toString();
    }

    public function hasPermission(string $permission): bool
    {
        return CmsPermissions::has($this, $permission);
    }

    public function isCmsUser(): bool
    {
        return $this->hasPermission('dashboard.view');
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_active && $this->role === Role::SUPER_ADMIN;
    }

    public function canPublishCmsContent(): bool
    {
        return $this->hasPermission('content.publish');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
