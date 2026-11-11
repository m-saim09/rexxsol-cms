<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public string $search = '';

    public string $role = '';

    public string $status = '';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public int $perPage = 10;

    public array $selected = [];

    public string $bulkAction = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'role', 'status', 'perPage'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->role = '';
        $this->status = '';
        $this->resetPage();
        $this->selected = [];
    }

    public function sortBy(string $field): void
    {
        $sortableFields = ['id', 'name', 'role', 'is_active', 'created_at'];

        if (! in_array($field, $sortableFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function toggleSelectAll(): void
    {
        $visibleIds = $this->getUsersQuery()->paginate($this->perPage)->pluck('id')->all();

        if (count(array_intersect($visibleIds, $this->selected)) === count($visibleIds)) {
            $this->selected = array_values(array_diff($this->selected, $visibleIds));

            return;
        }

        $this->selected = array_values(array_unique(array_merge($this->selected, $visibleIds)));
    }

    public function toggleSelected(int $id): void
    {
        $this->selected = in_array($id, $this->selected, true)
            ? array_values(array_diff($this->selected, [$id]))
            : [...$this->selected, $id];
    }

    public function applyBulkAction(?string $action = null): void
    {
        Gate::authorize('users.update');

        $actionToRun = $action ?: $this->bulkAction;
        if (! in_array($actionToRun, ['activate', 'deactivate'], true) || empty($this->selected)) {
            return;
        }

        DB::transaction(function () use ($actionToRun): void {
            $superAdmins = User::query()
                ->where('role', Role::SUPER_ADMIN)
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $users = User::query()->whereIn('id', $this->selected)->orderBy('id')->lockForUpdate()->get();

            if ($actionToRun === 'deactivate') {
                if ($users->contains('id', auth()->id())) {
                    throw ValidationException::withMessages(['bulkAction' => 'You cannot deactivate your own account.']);
                }

                $selectedSuperAdminCount = $users->where('is_active', true)
                    ->where('role', Role::SUPER_ADMIN)
                    ->count();

                if ($selectedSuperAdminCount > 0 && $superAdmins->count() - $selectedSuperAdminCount < 1) {
                    throw ValidationException::withMessages(['bulkAction' => 'At least one active Super Admin must remain.']);
                }
            }

            foreach ($users as $user) {
                $user->is_active = $actionToRun === 'activate';
                $user->save();
            }
        });

        $this->selected = [];
        $this->bulkAction = '';
        $this->resetPage();
        Flux::toast(variant: 'success', text: $actionToRun === 'activate'
            ? 'Selected users have been activated.'
            : 'Selected users have been deactivated.');
    }

    protected function getUsersQuery(): Builder
    {
        return User::query()
            ->when($this->search, function (Builder $query) {
                $term = '%'.$this->search.'%';
                $query->where(function (Builder $sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($this->role !== '', fn (Builder $q) => $q->where('role', $this->role))
            ->when(in_array($this->status, ['active', 'inactive'], true), fn (Builder $q) => $q->where('is_active', $this->status === 'active'))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        Gate::authorize('users.view');

        $items = $this->getUsersQuery()->with('roleRecord')->paginate($this->perPage);

        return view('livewire.admin.users', [
            'items' => $items,
            'roles' => Role::query()->withCount('users')->orderBy('name')->get(),
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'roleCounts' => User::query()->selectRaw('role, count(*) as aggregate')->groupBy('role')->pluck('aggregate', 'role'),
        ])->layout('layouts.admin')->title('Users & Roles');
    }
}
