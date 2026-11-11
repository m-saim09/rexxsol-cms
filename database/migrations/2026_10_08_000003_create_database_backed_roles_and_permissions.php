<?php

use App\Models\Role;
use App\Support\CmsPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $roleNames = [
            'super_admin' => ['Super Admin', 'Protected system role with full CMS access.'],
            'admin' => ['Admin', 'CMS administrator with content publishing access.'],
            'editor' => ['Editor', 'CMS editor with content access and no publishing privileges.'],
        ];

        $existingSlugs = DB::table('users')->distinct()->pluck('role')->filter()->unique()->values();
        $unknownSlugs = $existingSlugs->diff(array_keys($roleNames));
        if ($unknownSlugs->isNotEmpty()) {
            throw new RuntimeException(
                'Roles & Permissions migration stopped: users have unmapped role value(s): '.
                $unknownSlugs->implode(', ').
                '. Map each value to a registered role before retrying this migration.',
            );
        }

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('module');
            $table->string('action');
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        CmsPermissions::register();

        foreach ($roleNames as $slug => [$name, $description]) {
            Role::query()->create([
                'slug' => $slug,
                'name' => $name,
                'description' => $description,
                'is_system' => true,
            ]);
        }

        $baseline = [
            'super_admin' => array_keys(CmsPermissions::DEFINITIONS),
            'admin' => [
                'dashboard.view', 'pages.view', 'pages.create', 'pages.update', 'pages.delete', 'pages.publish',
                'homepage_builder.view', 'homepage_builder.update',
                'blog.view', 'blog.create', 'blog.update', 'blog.delete', 'blog.publish',
                'services.view', 'services.create', 'services.update', 'services.delete', 'services.publish',
                'projects.view', 'projects.create', 'projects.update', 'projects.delete', 'projects.publish',
                'team.view', 'team.create', 'team.update', 'team.delete', 'team.publish',
                'testimonials.view', 'testimonials.create', 'testimonials.update', 'testimonials.delete', 'testimonials.publish',
                'faqs.view', 'faqs.create', 'faqs.update', 'faqs.delete', 'faqs.publish',
                'media.view', 'media.upload', 'media.update', 'media.delete',
                'contact_messages.view', 'contact_messages.update',
                'navigation.view', 'navigation.manage',
                'appearance.view', 'appearance.manage', 'seo.view', 'seo.update',
                'content.publish', 'content.delete',
            ],
            'editor' => [
                'dashboard.view', 'pages.view', 'pages.create', 'pages.update',
                'homepage_builder.view',
                'blog.view', 'blog.create', 'blog.update',
                'services.view', 'services.create', 'services.update',
                'projects.view', 'projects.create', 'projects.update',
                'team.view', 'team.create', 'team.update',
                'testimonials.view', 'testimonials.create', 'testimonials.update',
                'faqs.view', 'faqs.create', 'faqs.update',
                'media.view', 'media.upload', 'media.update',
                'contact_messages.view',
                'navigation.view', 'appearance.view', 'seo.view',
            ],
        ];

        foreach ($baseline as $slug => $keys) {
            $role = Role::query()->where('slug', $slug)->firstOrFail();
            $permissionIds = DB::table('permissions')->whereIn('key', $keys)->pluck('id');
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('role', 'users_role_slug_foreign')->references('slug')->on('roles')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign('users_role_slug_foreign');
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
