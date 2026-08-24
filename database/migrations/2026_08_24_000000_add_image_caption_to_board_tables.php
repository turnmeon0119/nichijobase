<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['board_threads', 'board_posts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('image_caption', 160)->nullable()->after('image_public_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['board_threads', 'board_posts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('image_caption');
            });
        }
    }
};
