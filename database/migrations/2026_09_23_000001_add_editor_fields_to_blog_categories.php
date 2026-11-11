<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_categories', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->after('description')->constrained('blog_categories')->nullOnDelete();
            $table->string('icon', 100)->nullable()->after('parent_id');
            $table->boolean('is_default')->default(false)->after('icon');
            $table->boolean('is_featured')->default(false)->after('is_default');
            $table->string('status', 30)->default('published')->index()->after('is_featured');
            $table->string('seo_title')->nullable()->after('status');
            $table->string('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('blog_categories', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'icon', 'is_default', 'is_featured', 'status', 'seo_title', 'seo_description']);
        });
    }
};
