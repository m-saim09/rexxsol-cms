<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('contact_info_heading')->nullable();
            $table->text('contact_info_description')->nullable();
            $table->string('contact_form_heading')->nullable();
            $table->text('contact_form_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn([
                'contact_info_heading',
                'contact_info_description',
                'contact_form_heading',
                'contact_form_description',
            ]);
        });
    }
};
