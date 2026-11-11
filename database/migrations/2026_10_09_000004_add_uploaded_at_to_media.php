<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->timestamp('uploaded_at')->nullable()->after('created_at');
        });

        DB::table('media')->whereNull('uploaded_at')->update([
            'uploaded_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn('uploaded_at');
        });
    }
};
