<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Role;
use App\Support\CmsPermissions;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UserForm extends Component
{
    #[Locked]
    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public bool $is_active = true;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(?User $user = null): void
    {
        Gate::authorize($user?->exists ? 'users.update' : 'users.create');
        if ($user?->exists) {
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role;
            $this->is_active = $user->is_active;
        }
    }

    public function save(): void
    {
        Gate::authorize($this->userId ? 'users.update' : 'users.create');
        $this->email = strtolower(trim($this->email));
        $allowedRoleSlugs = $this->assignableRoles()->pluck('slug')->all();
        if ($this->userId && $this->role === User::query()->whereKey($this->userId)->value('role')) {
            $allowedRoleSlugs[] = $this->role;
        }
        $rules = ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->userId)], 'role' => ['required', Rule::in(array_unique($allowedRoleSlugs))], 'is_active' => ['required', 'boolean']];
        if (! $this->userId) {
            $rules['password'] = ['required', 'string', 'max:255', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()];
        }
        $this->validate($rules);
        DB::transaction(function () {
            // Lock the same active Super Admin set in all edits to serialize concurrent demotions.
            $superAdmins = User::where('role', Role::SUPER_ADMIN)->where('is_active', true)->orderBy('id')->lockForUpdate()->get(['id']);
            auth()->user()->refresh();
            Gate::authorize($this->userId ? 'users.update' : 'users.create');
            $user = $this->userId ? User::lockForUpdate()->findOrFail($this->userId) : new User;
            $roleChanged = ! $user->exists || $this->role !== $user->role;
            $assignedRole = $roleChanged
                ? Role::query()->where('slug', $this->role)->lockForUpdate()->firstOrFail()
                : $user->roleRecord;
            if ($roleChanged) {
                Gate::authorize('users.assign_roles');
                abort_unless(CmsPermissions::canAssignRole(auth()->user(), $assignedRole), 403);
            }
            if ($user->id === auth()->id() && (! $this->is_active || $roleChanged)) {
                throw ValidationException::withMessages(['role' => 'You cannot deactivate or demote your own Super Admin account.']);
            }
            if ($user->exists && $superAdmins->contains('id', $user->id) && $superAdmins->count() <= 1 && (! $this->is_active || $roleChanged)) {
                throw ValidationException::withMessages(['role' => 'At least one active Super Admin must remain.']);
            }
            $user->name = $this->name;
            if ($user->email !== $this->email) {
                $user->email_verified_at = null;
            }
            $user->email = $this->email;
            $user->role = $assignedRole->slug;
            $user->is_active = $this->is_active;
            if (! $user->exists) {
                $user->password = $this->password;
            }
            $user->save();
            $this->userId = $user->id;
        });
        $this->reset('password', 'password_confirmation');
        Flux::toast(variant: 'success', text: 'User saved successfully.');
        $this->redirectRoute('admin.users.index', navigate: true);
    }

    public function render()
    {
        Gate::authorize($this->userId ? 'users.update' : 'users.create');

        return view('livewire.admin.user-form', [
            'assignableRoles' => $this->assignableRoles(),
            'currentRole' => $this->userId ? User::query()->with('roleRecord')->findOrFail($this->userId)->roleRecord : null,
        ])->layout('layouts.admin')->title($this->userId ? 'Edit User' : 'Create User');
    }

    private function assignableRoles()
    {
        $actor = auth()->user();
        $roles = Role::query()->with('permissions')->orderBy('name')->get();

        return $roles->filter(fn (Role $role) => CmsPermissions::canAssignRole($actor, $role))->values();
    }
}
