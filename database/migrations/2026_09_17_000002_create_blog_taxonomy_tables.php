<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('blog_category_post', function (Blueprint $table): void {
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_category_id', 'blog_post_id']);
        });

        Schema::create('blog_post_tag', function (Blueprint $table): void {
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_post_id', 'blog_tag_id']);
        });

        DB::table('blog_posts')->select(['id', 'category', 'tags'])->orderBy('id')->each(function (object $post): void {
            if (filled($post->category)) {
                $categoryId = $this->taxonomyId('blog_categories', (string) $post->category, true);
                DB::table('blog_category_post')->insertOrIgnore(['blog_category_id' => $categoryId, 'blog_post_id' => $post->id]);
            }

            foreach ((array) json_decode($post->tags ?: '[]', true) as $tag) {
                if (! filled($tag)) {
                    continue;
                }

                $tagId = $this->taxonomyId('blog_tags', (string) $tag);
                DB::table('blog_post_tag')->insertOrIgnore(['blog_post_id' => $post->id, 'blog_tag_id' => $tagId]);
            }
        });
    }

    protected function taxonomyId(string $table, string $name, bool $withDescription = false): int
    {
        $slug = Str::slug($name);
        $existing = DB::table($table)->where('slug', $slug)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $attributes = ['name' => trim($name), 'slug' => $slug, 'created_at' => now(), 'updated_at' => now()];
        if ($withDescription) {
            $attributes['description'] = null;
        }

        return (int) DB::table($table)->insertGetId($attributes);
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_category_post');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
