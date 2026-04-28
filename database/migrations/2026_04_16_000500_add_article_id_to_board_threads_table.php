<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('board_threads', function (Blueprint $table) {
            $table->foreignId('article_id')->nullable()->after('id')->constrained('articles')->nullOnDelete();
            $table->unique('article_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_threads', function (Blueprint $table) {
            $table->dropUnique(['article_id']);
            $table->dropConstrainedForeignId('article_id');
        });
    }
};
