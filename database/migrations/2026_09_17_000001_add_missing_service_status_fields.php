<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            if (! Schema::hasColumn('services', 'status')) {
                $table->string('status')->default('draft')->index();
            }

            if (! Schema::hasColumn('services', 'published_at')) {
                $table->timestamp('published_at')->nullable();
            }

            if (! Schema::hasColumn('services', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->index();
            }

            if (! Schema::hasColumn('services', 'seo_title')) {
                $table->string('seo_title')->nullable();
            }

            if (! Schema::hasColumn('services', 'seo_description')) {
                $table->text('seo_description')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            if (Schema::hasColumn('services', 'seo_description')) {
                $table->dropColumn('seo_description');
            }

            if (Schema::hasColumn('services', 'seo_title')) {
                $table->dropColumn('seo_title');
            }

            if (Schema::hasColumn('services', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('services', 'published_at')) {
                $table->dropColumn('published_at');
            }

            if (Schema::hasColumn('services', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
