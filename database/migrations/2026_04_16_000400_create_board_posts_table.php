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
        Schema::create('board_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_thread_id')->constrained('board_threads')->cascadeOnDelete();
            $table->string('name', 40)->nullable();
            $table->text('body');
            $table->string('created_ip', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_posts');
    }
};
