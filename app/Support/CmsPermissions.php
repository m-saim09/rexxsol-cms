<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CmsPermissions
{
    public const DEFINITIONS = [
        'dashboard.view' => ['Dashboard', 'View dashboard'],
        'pages.view' => ['Pages', 'View pages'],
        'pages.create' => ['Pages', 'Create pages'],
        'pages.update' => ['Pages', 'Edit pages'],
        'pages.delete' => ['Pages', 'Delete pages'],
        'pages.publish' => ['Pages', 'Publish pages'],
        'homepage_builder.view' => ['Homepage Builder', 'View homepage builder'],
        'homepage_builder.update' => ['Homepage Builder', 'Edit homepage'],
        'blog.view' => ['Blog', 'View blog'],
        'blog.create' => ['Blog', 'Create blog content'],
        'blog.update' => ['Blog', 'Edit blog content'],
        'blog.delete' => ['Blog', 'Delete blog content'],
        'blog.publish' => ['Blog', 'Publish blog content'],
        'services.view' => ['Services', 'View services'],
        'services.create' => ['Services', 'Create services'],
        'services.update' => ['Services', 'Edit services'],
        'services.delete' => ['Services', 'Delete services'],
        'services.publish' => ['Services', 'Publish services'],
        'projects.view' => ['Projects', 'View projects'],
        'projects.create' => ['Projects', 'Create projects'],
        'projects.update' => ['Projects', 'Edit projects'],
        'projects.delete' => ['Projects', 'Delete projects'],
        'projects.publish' => ['Projects', 'Publish projects'],
        'team.view' => ['Team', 'View team members'],
        'team.create' => ['Team', 'Create team members'],
        'team.update' => ['Team', 'Edit team members'],
        'team.delete' => ['Team', 'Delete team members'],
        'team.publish' => ['Team', 'Publish team members'],
        'testimonials.view' => ['Testimonials', 'View testimonials'],
        'testimonials.create' => ['Testimonials', 'Create testimonials'],
        'testimonials.update' => ['Testimonials', 'Edit testimonials'],
        'testimonials.delete' => ['Testimonials', 'Delete testimonials'],
        'testimonials.publish' => ['Testimonials', 'Publish testimonials'],
        'faqs.view' => ['FAQs', 'View FAQs'],
        'faqs.create' => ['FAQs', 'Create FAQs'],
        'faqs.update' => ['FAQs', 'Edit FAQs'],
        'faqs.delete' => ['FAQs', 'Delete FAQs'],
        'faqs.publish' => ['FAQs', 'Publish FAQs'],
        'media.view' => ['Media Library', 'View media'],
        'media.upload' => ['Media Library', 'Upload media'],
        'media.update' => ['Media Library', 'Edit media details'],
        'media.delete' => ['Media Library', 'Delete media'],
        'contact_messages.view' => ['Contact Messages', 'View contact messages'],
        'contact_messages.update' => ['Contact Messages', 'Update message status'],
        'navigation.view' => ['Navigation', 'View navigation'],
        'navigation.manage' => ['Navigation', 'Manage navigation'],
        'appearance.view' => ['Appearance / Themes', 'View appearance settings'],
        'appearance.manage' => ['Appearance / Themes', 'Manage themes and appearance'],
        'seo.view' => ['SEO', 'View SEO settings'],
        'seo.update' => ['SEO', 'Edit SEO settings'],
        'analytics.view' => ['Analytics', 'View analytics'],
        'users.view' => ['Users', 'View users'],
        'users.create' => ['Users', 'Create users'],
        'users.update' => ['Users', 'Edit users'],
        'users.delete' => ['Users', 'Delete users'],
        'users.assign_roles' => ['Users', 'Assign roles'],
        'roles.view' => ['Roles & Permissions', 'View roles'],
        'roles.create' => ['Roles & Permissions', 'Create roles'],
        'roles.update' => ['Roles & Permissions', 'Edit roles'],
        'roles.delete' => ['Roles & Permissions', 'Delete roles'],
        'roles.manage_permissions' => ['Roles & Permissions', 'Manage permissions'],
        'activity_logs.view' => ['Activity Logs', 'View activity logs'],
        'settings.view' => ['Settings', 'View settings'],
        'settings.update' => ['Settings', 'Edit settings'],
        'content.publish' => ['Content', 'Publish CMS content'],
        'content.delete' => ['Content', 'Delete CMS content'],
    ];

    public const MODULES = [
        'Content' => ['content', 'pages', 'services', 'projects', 'blog', 'team', 'testimonials', 'faqs'],
        'Platform' => ['dashboard', 'homepage_builder', 'media', 'contact_messages', 'navigation', 'appearance', 'seo', 'analytics', 'users', 'roles', 'activity_logs', 'settings'],
    ];

    public static function register(): void
    {
        foreach (self::DEFINITIONS as $key => [$module, $label]) {
            Permission::query()->firstOrCreate(
                ['key' => $key],
                ['module' => $module, 'action' => str($key)->after('.')->toString(), 'label' => $label],
            );
        }
    }

    public static function has(User $user, string $key): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->role === Role::SUPER_ADMIN) {
            return true;
        }

        $roleId = $user->roleRecord()->value('id');
        if (! $roleId) {
            return false;
        }

        $granted = Cache::rememberForever("cms-role-permissions:{$roleId}", fn () => Role::query()
            ->find($roleId)
            ?->permissions()
            ->pluck('key')
            ->all() ?? []);

        return in_array($key, $granted, true);
    }

    /** @return list<string> */
    public static function grantedKeysFor(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return array_keys(self::DEFINITIONS);
        }

        $roleId = $user->roleRecord()->value('id');
        if (! $roleId) {
            return [];
        }

        return Cache::rememberForever("cms-role-permissions:{$roleId}", fn () => Role::query()
            ->find($roleId)
            ?->permissions()
            ->pluck('key')
            ->all() ?? []);
    }

    public static function canAssignRole(User $actor, Role $target): bool
    {
        if (! $actor->is_active || ! $actor->hasPermission('users.assign_roles')) {
            return false;
        }
        if ($target->slug === Role::SUPER_ADMIN) {
            return $actor->isSuperAdmin();
        }
        if ($actor->isSuperAdmin()) {
            return true;
        }

        $allowed = self::grantedKeysFor($actor);
        $targetPermissions = $target->permissions()->pluck('key')->all();

        return array_diff($targetPermissions, $allowed) === [];
    }

    /**
     * A role administrator can delegate only permissions they personally hold.
     *
     * @param list<string> $permissionKeys
     */
    public static function canGrantPermissions(User $actor, array $permissionKeys): bool
    {
        if (! $actor->is_active || ! $actor->hasPermission('roles.manage_permissions')) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        return array_diff($permissionKeys, self::grantedKeysFor($actor)) === [];
    }

    public static function resourcePermission(string $resource, string $action): string
    {
        $key = "{$resource}.{$action}";
        abort_unless(isset(self::DEFINITIONS[$key]), 403, "The {$action} action is not registered for {$resource}.");

        return $key;
    }
}
