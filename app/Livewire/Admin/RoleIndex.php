<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Role;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class RoleIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public ?int $deletingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function reload(): void
    {
        Gate::authorize('roles.view');
        $this->resetPage();
    }

    public function delete(int $roleId): void
    {
        Gate::authorize('roles.delete');

        DB::transaction(function () use ($roleId): void {
            $role = Role::query()->lockForUpdate()->findOrFail($roleId);
            abort_if($role->is_system || $role->slug === Role::SUPER_ADMIN, 403, 'System roles cannot be deleted.');
            $assignedUsers = $role->users()->lockForUpdate()->count();
            if ($assignedUsers > 0) {
                $this->addError('delete', "This role is assigned to {$assignedUsers} users. Reassign them before deleting it.");

                return;
            }

            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'deleted',
                'module' => 'Role',
                'subject_type' => Role::class,
                'subject_id' => $role->id,
                'subject_label' => $role->name,
                'changes' => ['name' => ['old' => $role->name, 'new' => null]],
            ]);
            $role->delete();
            Role::clearPermissionCache($role->id);
        });

        if (! $this->getErrorBag()->has('delete')) {
            $this->deletingId = null;
            Flux::toast(variant: 'success', text: 'Role deleted successfully.');
        }
    }

    private function rolesQuery(): Builder
    {
        return Role::query()
            ->withCount(['users', 'permissions'])
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(fn (Builder $nested) => $nested->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->orderByDesc('is_system')
            ->orderBy('name');
    }

    public function render()
    {
        Gate::authorize('roles.view');

        return view('livewire.admin.role-index', [
            'roles' => $this->rolesQuery()->paginate($this->perPage),
        ])->layout('layouts.admin')->title('Roles & Permissions');
    }
}
