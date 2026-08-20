<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('board_posts', function (Blueprint $table): void {
            $table->unsignedInteger('empathy_count')->default(0)->after('reports_count');
            $table->unsignedInteger('perspective_count')->default(0)->after('empathy_count');
        });
    }

    public function down(): void
    {
        Schema::table('board_posts', function (Blueprint $table): void {
            $table->dropColumn(['empathy_count', 'perspective_count']);
        });
    }
};
