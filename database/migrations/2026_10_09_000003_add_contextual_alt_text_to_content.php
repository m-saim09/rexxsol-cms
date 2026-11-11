<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', fn (Blueprint $table) => $table->string('featured_image_alt')->nullable()->after('featured_image'));
        Schema::table('services', fn (Blueprint $table) => $table->string('featured_image_alt')->nullable()->after('featured_image'));
        Schema::table('projects', fn (Blueprint $table) => $table->string('featured_image_alt')->nullable()->after('featured_image'));
        Schema::table('team_members', fn (Blueprint $table) => $table->string('profile_image_alt')->nullable()->after('profile_image'));
        Schema::table('testimonials', fn (Blueprint $table) => $table->string('avatar_alt')->nullable()->after('avatar'));
    }

    public function down(): void
    {
        Schema::table('pages', fn (Blueprint $table) => $table->dropColumn('featured_image_alt'));
        Schema::table('services', fn (Blueprint $table) => $table->dropColumn('featured_image_alt'));
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn('featured_image_alt'));
        Schema::table('team_members', fn (Blueprint $table) => $table->dropColumn('profile_image_alt'));
        Schema::table('testimonials', fn (Blueprint $table) => $table->dropColumn('avatar_alt'));
    }
};
