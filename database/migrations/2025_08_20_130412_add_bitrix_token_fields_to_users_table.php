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
        Schema::table('users', function (Blueprint $table) {
            $table->string('bitrix_access_token')->nullable();
            $table->string('bitrix_refresh_token')->nullable();
            $table->timestamp('bitrix_expires',6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bitrix_access_token');
            $table->dropColumn('bitrix_refresh_token');
            $table->dropColumn('bitrix_expires');
        });
    }
};
