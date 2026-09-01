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
        Schema::create('hitokoto_posts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->nullable();
            $table->string('body', 140);
            $table->string('created_ip', 45)->nullable();
            $table->boolean('is_hidden')->default(false)->index();
            $table->unsignedInteger('reports_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hitokoto_posts');
    }
};
