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
            $table->boolean('is_hidden')->default(false)->after('created_ip')->index();
            $table->unsignedInteger('reports_count')->default(0)->after('is_hidden');
        });

        Schema::table('board_posts', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('created_ip')->index();
            $table->unsignedInteger('reports_count')->default(0)->after('is_hidden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_posts', function (Blueprint $table) {
            $table->dropColumn(['is_hidden', 'reports_count']);
        });

        Schema::table('board_threads', function (Blueprint $table) {
            $table->dropColumn(['is_hidden', 'reports_count']);
        });
    }
};
