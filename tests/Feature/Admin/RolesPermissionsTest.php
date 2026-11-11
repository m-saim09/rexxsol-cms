<?php

use App\Enums\UserRole;
use App\Livewire\Admin\RoleForm;
use App\Livewire\Admin\RoleIndex;
use App\Livewire\Admin\UserForm;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\CmsPermissions;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;

function permissionsRole(string $slug, array $permissionKeys, bool $system = false): Role
{
    CmsPermissions::register();
    $role = Role::query()->create([
        'slug' => $slug,
        'name' => str($slug)->replace('-', ' ')->title()->toString(),
        'is_system' => $system,
    ]);
    $role->permissions()->sync(Permission::query()->whereIn('key', $permissionKeys)->pluck('id'));

    return $role;
}

function permissionsUser(Role|UserRole $role, array $attributes = []): User
{
    return User::factory()->create([
        'role' => $role instanceof Role ? $role->slug : $role,
        ...$attributes,
    ]);
}

it('creates and edits database-backed roles and records safe audit changes', function () {
    $superAdmin = permissionsUser(UserRole::SuperAdmin);
    $this->actingAs($superAdmin);

    Livewire::test(RoleForm::class)
        ->set('name', 'Content Reviewer')
        ->set('description', 'Reviews content without publishing.')
        ->set('selectedPermissions', ['dashboard.view', 'pages.view'])
        ->call('save')
        ->assertHasNoErrors();

    $role = Role::query()->where('name', 'Content Reviewer')->firstOrFail();
    expect($role->slug)->toBe('content-reviewer')
        ->and($role->permissions()->pluck('key')->sort()->values()->all())->toBe(['dashboard.view', 'pages.view']);

    Livewire::test(RoleForm::class, ['role' => $role])
        ->set('description', 'Can also review blog content.')
        ->set('selectedPermissions', ['dashboard.view', 'pages.view', 'blog.view'])
        ->call('save')
        ->assertHasNoErrors();

    expect($role->fresh()->description)->toBe('Can also review blog content.')
        ->and(ActivityLog::query()->where('module', 'Role')->where('subject_id', $role->id)->count())->toBe(2)
        ->and(ActivityLog::query()->where('module', 'Role')->where('subject_id', $role->id)->latest('id')->first()->changes)->toHaveKey('permissions');
});

it('denies role routes and Livewire actions without registered authority', function () {
    $editor = permissionsUser(UserRole::Editor);
    $this->actingAs($editor);

    $this->get(route('admin.roles.index'))->assertForbidden();
    $this->get(route('admin.roles.create'))->assertForbidden();
    Livewire::test(RoleIndex::class)->assertForbidden();
    Livewire::test(RoleForm::class)->assertForbidden();
});

it('uses granular module permissions for content routes and direct actions', function () {
    $role = permissionsRole('page-reader', ['dashboard.view', 'pages.view']);
    $editor = permissionsUser($role);
    $this->actingAs($editor);

    $this->get(route('admin.pages.index'))->assertOk();
    $this->get(route('admin.pages.create'))->assertForbidden();
    Livewire::test('pages::admin.content-list', ['resource' => 'pages'])
        ->call('delete')
        ->assertForbidden();

    $superAdmin = permissionsUser(UserRole::SuperAdmin);
    $this->actingAs($superAdmin);
    Livewire::test(RoleForm::class, ['role' => $role])
        ->set('selectedPermissions', ['dashboard.view', 'pages.view', 'pages.create'])
        ->call('save')
        ->assertHasNoErrors();

    expect($editor->fresh()->hasPermission('pages.create'))->toBeTrue();
    $this->actingAs($editor);
    $this->get(route('admin.pages.create'))->assertOk();
});

it('preserves registered role assignments when permission definitions are seeded repeatedly', function () {
    $role = permissionsRole('custom-auditor', ['pages.view']);
    $this->seed(PermissionSeeder::class);
    $this->seed(PermissionSeeder::class);

    expect($role->fresh()->permissions()->pluck('key')->all())->toBe(['pages.view'])
        ->and(Permission::query()->count())->toBe(count(CmsPermissions::DEFINITIONS));
});

it('invalidates role permission caches after permission changes', function () {
    $role = permissionsRole('cache-check', ['dashboard.view', 'pages.view']);
    $user = permissionsUser($role);
    expect($user->hasPermission('media.delete'))->toBeFalse();

    $superAdmin = permissionsUser(UserRole::SuperAdmin);
    $this->actingAs($superAdmin);
    Livewire::test(RoleForm::class, ['role' => $role])
        ->set('selectedPermissions', ['dashboard.view', 'pages.view', 'media.view', 'media.delete'])
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->hasPermission('media.delete'))->toBeTrue();
});

it('blocks permission escalation beyond the acting administrator scope', function () {
    $delegator = permissionsRole('limited-delegator', [
        'dashboard.view',
        'roles.view',
        'roles.create',
        'roles.update',
        'roles.manage_permissions',
        'users.view',
    ]);
    $actor = permissionsUser($delegator);
    $this->actingAs($actor);

    Livewire::test(RoleForm::class, ['role' => $delegator])
        ->set('selectedPermissions', [
            'dashboard.view',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.manage_permissions',
            'users.view',
            'users.assign_roles',
        ])
        ->call('save')
        ->assertForbidden();

    expect($delegator->fresh()->permissions()->pluck('key')->contains('users.assign_roles'))->toBeFalse();
});

it('does not allow a non-super-admin to assign the protected Super Admin role', function () {
    $assigner = permissionsRole('restricted-assigner', [
        'dashboard.view',
        'users.view',
        'users.create',
        'users.update',
        'users.assign_roles',
    ]);
    $this->actingAs(permissionsUser($assigner));

    Livewire::test(UserForm::class)
        ->set(['name' => 'New Operator', 'email' => 'operator@example.test', 'role' => Role::SUPER_ADMIN])
        ->call('save')
        ->assertHasErrors('role');

    expect(User::query()->where('email', 'operator@example.test')->exists())->toBeFalse();
});

it('protects the last active Super Admin from deactivation', function () {
    $operatorRole = permissionsRole('user-editor', ['dashboard.view', 'users.view', 'users.update']);
    $actor = permissionsUser($operatorRole);
    $superAdmin = permissionsUser(UserRole::SuperAdmin);
    $this->actingAs($actor);
    expect($actor->hasPermission('users.update'))->toBeTrue();

    $form = Livewire::test(UserForm::class, ['user' => $superAdmin]);
    $form->assertStatus(200)
        ->set('is_active', false)
        ->assertStatus(200)
        ->call('save')
        ->assertHasErrors('role');

    expect($superAdmin->fresh()->is_active)->toBeTrue();
});

it('blocks deletion of roles that still have assigned users', function () {
    $role = permissionsRole('assigned-reviewer', ['dashboard.view']);
    permissionsUser($role);
    $this->actingAs(permissionsUser(UserRole::SuperAdmin));

    Livewire::test(RoleIndex::class)
        ->call('delete', $role->id)
        ->assertHasErrors('delete')
        ->assertSee('This role is assigned to 1 users. Reassign them before deleting it.');

    expect(Role::query()->find($role->id))->not->toBeNull();
});

it('shows only roles within the user assignment scope', function () {
    $assigner = permissionsRole('role-assigner', [
        'dashboard.view',
        'users.view',
        'users.create',
        'users.assign_roles',
        'pages.view',
    ]);
    permissionsRole('assignable-viewer', ['pages.view']);
    permissionsRole('not-assignable-editor', ['pages.view', 'pages.update']);
    $this->actingAs(permissionsUser($assigner));

    Livewire::test(UserForm::class)
        ->assertSee('Assignable Viewer')
        ->assertDontSee('Not Assignable Editor')
        ->assertDontSee('Super Admin</option>');
});

it('preserves the canonical system roles and user role mappings', function () {
    $superAdmin = permissionsUser(UserRole::SuperAdmin);
    $admin = permissionsUser(UserRole::Admin);
    $editor = permissionsUser(UserRole::Editor);

    expect($superAdmin->roleRecord->slug)->toBe(Role::SUPER_ADMIN)
        ->and($admin->roleRecord->slug)->toBe('admin')
        ->and($editor->roleRecord->slug)->toBe('editor')
        ->and($superAdmin->hasPermission('settings.update'))->toBeTrue()
        ->and($admin->hasPermission('pages.publish'))->toBeTrue()
        ->and($editor->hasPermission('pages.publish'))->toBeFalse();
});
