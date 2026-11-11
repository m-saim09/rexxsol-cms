<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('featured_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status')->default('draft')->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->string('client')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('category')->nullable()->index();
            $table->json('technologies')->nullable();
            $table->string('project_url')->nullable();
            $table->date('completion_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('category')->nullable()->index();
            $table->json('tags')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropForeign(['author_id']);
            $table->dropColumn(['excerpt', 'content', 'featured_image', 'category', 'tags', 'author_id', 'seo_title', 'seo_description']);
        });
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['client', 'short_description', 'description', 'featured_image', 'gallery', 'category', 'technologies', 'project_url', 'completion_date', 'sort_order', 'seo_title', 'seo_description']);
        });
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn(['short_description', 'description', 'icon', 'featured_image', 'sort_order', 'status', 'seo_title', 'seo_description']);
        });
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn(['excerpt', 'content', 'featured_image', 'seo_title', 'seo_description', 'seo_keywords']);
        });
    }
};
