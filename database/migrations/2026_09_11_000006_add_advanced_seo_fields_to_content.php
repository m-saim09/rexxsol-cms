<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pages', 'services', 'projects', 'blog_posts'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->string('canonical_url', 500)->nullable();
                $table->boolean('noindex')->default(false);
                $table->boolean('nofollow')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (['pages', 'services', 'projects', 'blog_posts'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropColumn(['canonical_url', 'noindex', 'nofollow']);
            });
        }
    }
};
