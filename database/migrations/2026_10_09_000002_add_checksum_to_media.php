<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->char('checksum', 64)->nullable()->after('path');
            $table->unique(['disk', 'checksum'], 'media_disk_checksum_unique');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropUnique('media_disk_checksum_unique');
            $table->dropColumn('checksum');
        });
    }
};
