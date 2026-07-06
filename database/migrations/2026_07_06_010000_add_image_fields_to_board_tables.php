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
                $table->text('image_url')->nullable()->after('body');
                $table->string('image_public_id')->nullable()->after('image_url');
            });
        }
    }

    public function down(): void
    {
        foreach (['board_threads', 'board_posts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['image_url', 'image_public_id']);
            });
        }
    }
};
