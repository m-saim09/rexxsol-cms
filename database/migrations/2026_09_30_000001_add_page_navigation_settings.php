<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->boolean('show_in_navbar')->default(false);
            $table->boolean('show_in_footer')->default(false);
            $table->string('navigation_label')->nullable();
            $table->unsignedInteger('menu_order')->default(0);
        });

        Schema::table('navigation_items', function (Blueprint $table): void {
            $table->boolean('is_automatic')->default(false)->index();
            $table->string('automatic_label')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('navigation_items', function (Blueprint $table): void {
            $table->dropColumn(['is_automatic', 'automatic_label']);
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn(['show_in_navbar', 'show_in_footer', 'navigation_label', 'menu_order']);
        });
    }
};