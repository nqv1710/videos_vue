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
        Schema::create('s2_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255)->nullable();
            $table->string('url', 2048)->nullable();
            $table->timestamp('created_at', 6)->nullable();
            $table->string('hashtag', 255)->nullable();
            $table->json('topic')->nullable();
            $table->text('description')->nullable();
            $table->string('typeVideo', 255)->nullable();
            $table->string('linkShare', 2048)->nullable();
            $table->string('created_by', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s2_videos');
    }
};
