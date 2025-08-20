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
        Schema::create('s2_comment', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 255)->nullable();
            $table->text('message')->nullable();
            $table->timestamp('created_at',6)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('video_id')->nullable();
            $table->string('type', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s2_comments');
    }
};
