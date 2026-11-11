<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Support\CmsPermissions;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RoleForm extends Component
{
    #[Locked]
    public ?int $roleId = null;

    public string $name = '';

    public string $description = '';

    public string $moduleSearch = '';

    /** @var list<string> */
    public array $selectedPermissions = [];

    public function mount(?Role $role = null): void
    {
        Gate::authorize($role?->exists ? 'roles.update' : 'roles.create');
        CmsPermissions::register();

        if ($role?->exists) {
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->description = $role->description ?? '';
            $this->selectedPermissions = $role->permissions()->pluck('key')->all();
        }
    }

    public function updatedSelectedPermissions(): void
    {
        $this->selectedPermissions = array_values(array_unique(array_intersect(
            $this->selectedPermissions,
            array_keys(CmsPermissions::DEFINITIONS),
        )));
    }

    public function togglePermission(string $permission): void
    {
        Gate::authorize('roles.manage_permissions');
        abort_unless(isset(CmsPermissions::DEFINITIONS[$permission]), 422);

        if (in_array($permission, $this->selectedPermissions, true)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, [$permission]));
        } else {
            $this->selectedPermissions[] = $permission;
            $this->normalizePermissionDependencies();
        }
    }

    public function toggleModule(string $module): void
    {
        Gate::authorize('roles.manage_permissions');
        $keys = $this->visibleModuleKeys($module);
        $allSelected = $keys !== [] && count(array_intersect($keys, $this->selectedPermissions)) === count($keys);
        $this->selectedPermissions = array_values(array_unique($allSelected
            ? array_diff($this->selectedPermissions, $keys)
            : [...$this->selectedPermissions, ...$keys]));
        $this->normalizePermissionDependencies();
    }

    public function toggleVisible(bool $select): void
    {
        Gate::authorize('roles.manage_permissions');
        $visibleKeys = $this->visibleKeys();
        $this->selectedPermissions = array_values(array_unique($select
            ? [...$this->selectedPermissions, ...$visibleKeys]
            : array_diff($this->selectedPermissions, $visibleKeys)));
        $this->normalizePermissionDependencies();
    }

    public function save(): void
    {
        Gate::authorize($this->roleId ? 'roles.update' : 'roles.create');
        $this->name = trim($this->name);
        $this->description = trim($this->description);
        $this->selectedPermissions = array_values(array_unique(array_intersect(
            $this->selectedPermissions,
            array_keys(CmsPermissions::DEFINITIONS),
        )));
        sort($this->selectedPermissions);

        $this->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($this->roleId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['required', 'string', Rule::exists('permissions', 'key')],
        ]);

        $this->validateDependencies();

        DB::transaction(function (): void {
            auth()->user()->refresh();
            Gate::authorize($this->roleId ? 'roles.update' : 'roles.create');

            $role = $this->roleId
                ? Role::query()->lockForUpdate()->findOrFail($this->roleId)
                : new Role;

            if ($role->slug === Role::SUPER_ADMIN) {
                throw ValidationException::withMessages(['name' => 'The protected Super Admin role cannot be renamed or edited.']);
            }

            $isNew = ! $role->exists;
            $before = [
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions()->orderBy('key')->pluck('key')->all(),
            ];
            sort($before['permissions']);
            if ($before['permissions'] !== $this->selectedPermissions) {
                Gate::authorize('roles.manage_permissions');
                abort_unless(CmsPermissions::canGrantPermissions(auth()->user(), $this->selectedPermissions), 403, 'You cannot grant permissions beyond your own access.');
            }

            if ($isNew) {
                $role->slug = $this->newSlug($this->name);
                $role->is_system = false;
            }

            $role->name = $this->name;
            $role->description = $this->description ?: null;
            $role->save();
            $role->permissions()->sync(Permission::query()->whereIn('key', $this->selectedPermissions)->pluck('id'));
            Role::clearPermissionCache($role->id);

            $after = [
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions()->orderBy('key')->pluck('key')->all(),
            ];
            $changes = [];
            foreach (array_keys($after) as $field) {
                if ($before[$field] !== $after[$field]) {
                    $changes[$field] = ['old' => $before[$field], 'new' => $after[$field]];
                }
            }

            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'action' => $isNew ? 'created' : 'updated',
                'module' => 'Role',
                'subject_type' => Role::class,
                'subject_id' => $role->id,
                'subject_label' => $role->name,
                'changes' => $changes,
            ]);

            $this->roleId = $role->id;
        });

        $this->dispatch('role-saved');
        Flux::toast(variant: 'success', text: 'Role saved successfully.');
        $this->redirectRoute('admin.roles.index', navigate: true);
    }

    private function validateDependencies(): void
    {
        foreach ($this->selectedPermissions as $key) {
            $action = str($key)->afterLast('.')->toString();
            if (! in_array($action, ['create', 'update', 'delete', 'publish', 'upload', 'manage', 'assign_roles', 'manage_permissions'], true)) {
                continue;
            }

            $viewKey = str($key)->beforeLast('.')->append('.view')->toString();
            if (isset(CmsPermissions::DEFINITIONS[$viewKey]) && ! in_array($viewKey, $this->selectedPermissions, true)) {
                throw ValidationException::withMessages(['selectedPermissions' => "The {$action} permission requires {$viewKey}."]);
            }
        }
    }

    private function normalizePermissionDependencies(): void
    {
        foreach ($this->selectedPermissions as $key) {
            $action = str($key)->afterLast('.')->toString();
            if (! in_array($action, ['create', 'update', 'delete', 'publish', 'upload', 'manage', 'assign_roles', 'manage_permissions'], true)) {
                continue;
            }

            $viewKey = str($key)->beforeLast('.')->append('.view')->toString();
            if (isset(CmsPermissions::DEFINITIONS[$viewKey])) {
                $this->selectedPermissions[] = $viewKey;
            }
        }

        $this->selectedPermissions = array_values(array_unique($this->selectedPermissions));
        sort($this->selectedPermissions);
    }

    private function visibleModuleKeys(string $module): array
    {
        $registeredModules = array_unique(array_map(fn (array $definition) => $definition[0], CmsPermissions::DEFINITIONS));
        abort_unless(in_array($module, $registeredModules, true), 422);

        return array_values(array_filter($this->visibleKeys(), function (string $key) use ($module): bool {
            $definition = CmsPermissions::DEFINITIONS[$key] ?? null;

            return $definition && $definition[0] === $module;
        }));
    }

    private function visibleKeys(): array
    {
        $query = mb_strtolower(trim($this->moduleSearch));
        $definitions = CmsPermissions::DEFINITIONS;

        return array_keys(array_filter($definitions, function (array $definition, string $key) use ($query): bool {
            return $query === '' || str_contains(mb_strtolower($definition[0].' '.$definition[1].' '.$key), $query);
        }, ARRAY_FILTER_USE_BOTH));
    }

    private function newSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;
        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function render()
    {
        Gate::authorize($this->roleId ? 'roles.update' : 'roles.create');
        $role = $this->roleId ? Role::query()->withCount('users')->findOrFail($this->roleId) : null;
        $modules = collect(CmsPermissions::MODULES)
            ->map(fn (array $names, string $group) => [
                'group' => $group,
                'modules' => collect($names)->filter(fn (string $module) => collect(CmsPermissions::DEFINITIONS)
                    ->contains(fn (array $definition) => $definition[0] === str($module)->replace('_', ' ')->title()->toString() || $definition[0] === match ($module) {
                        'content' => 'Content',
                        'homepage_builder' => 'Homepage Builder',
                        'contact_messages' => 'Contact Messages',
                        'activity_logs' => 'Activity Logs',
                        default => str($module)->replace('_', ' ')->title()->toString(),
                    }))
                    ->map(function (string $module): array {
                        $label = match ($module) {
                            'homepage_builder' => 'Homepage Builder',
                            'contact_messages' => 'Contact Messages',
                            'activity_logs' => 'Activity Logs',
                            default => str($module)->replace('_', ' ')->title()->toString(),
                        };

                        return [
                            'name' => $label,
                            'keys' => array_values(array_filter($this->visibleKeys(), fn (string $key) => CmsPermissions::DEFINITIONS[$key][0] === $label)),
                        ];
                    })
                    ->filter(fn (array $entry) => $entry['keys'] !== [])
                    ->values(),
            ])
            ->filter(fn (array $section) => $section['modules']->isNotEmpty())
            ->values();

        return view('livewire.admin.role-form', [
            'role' => $role,
            'modules' => $modules,
            'userCount' => $role?->users_count ?? 0,
            'permissionCount' => count($this->selectedPermissions),
            'permissionDefinitions' => CmsPermissions::DEFINITIONS,
            'visiblePermissionKeys' => $this->visibleKeys(),
        ])->layout('layouts.admin')->title($role ? 'Edit Role' : 'Create Role');
    }
}
