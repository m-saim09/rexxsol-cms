<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('title_source')->nullable()->default('filename')->after('title');
            $table->string('alt_text_source')->nullable()->after('alt_text');
            $table->string('alt_text_status')->nullable()->default('suggested')->after('alt_text_source');
            $table->string('source_module')->nullable()->after('alt_text_status');
            $table->string('source_field')->nullable()->after('source_module');
            $table->string('source_record_type')->nullable()->after('source_field');
            $table->unsignedBigInteger('source_record_id')->nullable()->after('source_record_type');
            $table->string('source_record_name')->nullable()->after('source_record_id');
            $table->string('source_label')->nullable()->after('source_record_name');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn([
                'title_source',
                'alt_text_source',
                'alt_text_status',
                'source_module',
                'source_field',
                'source_record_type',
                'source_record_id',
                'source_record_name',
                'source_label',
            ]);
        });
    }
};
