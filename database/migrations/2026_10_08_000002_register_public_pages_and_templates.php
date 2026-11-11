<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SYSTEM_PAGES = [
        ['title' => 'Home', 'slug' => 'home', 'template' => 'system-home'],
        ['title' => 'About Rexxsol', 'slug' => 'about', 'template' => 'system-about'],
        ['title' => 'Services', 'slug' => 'services', 'template' => 'system-services', 'hero_eyebrow' => 'Our services', 'hero_heading' => 'Services built around your business.', 'hero_description' => 'From strategy to software, we help you build what comes next.'],
        ['title' => 'Projects', 'slug' => 'projects', 'template' => 'system-projects'],
        ['title' => 'Blog', 'slug' => 'blog', 'template' => 'system-blog'],
        ['title' => 'Contact', 'slug' => 'contact', 'template' => 'system-contact', 'hero_eyebrow' => 'Let’s build something meaningful', 'hero_heading' => 'Tell us what you have in mind.', 'hero_description' => 'Share a little about your project and our team will be in touch.'],
    ];

    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('template')->default('default');
            $table->boolean('is_system')->default(false)->index();
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_heading')->nullable();
            $table->text('hero_description')->nullable();
            $table->boolean('show_in_navbar')->default(true)->change();
            $table->boolean('show_in_footer')->default(true)->change();
        });

        Schema::create('page_slug_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('old_slug')->unique();
            $table->string('target_slug');
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->index('target_slug');
        });

        DB::transaction(function (): void {
            foreach (self::SYSTEM_PAGES as $page) {
                $existing = DB::table('pages')->where('slug', $page['slug'])->first();
                if ($existing) {
                    DB::table('pages')->where('id', $existing->id)->update([
                        'template' => $page['template'],
                        'is_system' => true,
                        'hero_eyebrow' => $page['hero_eyebrow'] ?? null,
                        'hero_heading' => $page['hero_heading'] ?? null,
                        'hero_description' => $page['hero_description'] ?? null,
                    ]);

                    continue;
                }

                DB::table('pages')->insert([
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'status' => 'published',
                    'published_at' => now(),
                    'template' => $page['template'],
                    'is_system' => true,
                    'show_in_navbar' => false,
                    'show_in_footer' => false,
                    'menu_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_slug_redirects');
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn(['template', 'is_system', 'hero_eyebrow', 'hero_heading', 'hero_description']);
            $table->boolean('show_in_navbar')->default(false)->change();
            $table->boolean('show_in_footer')->default(false)->change();
        });
    }
};
