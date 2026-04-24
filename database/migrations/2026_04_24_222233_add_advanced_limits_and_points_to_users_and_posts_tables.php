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
            $table->integer('daily_post_limit')->nullable();
            $table->integer('total_post_limit')->nullable();
            $table->boolean('is_unlimited')->default(false);
            $table->boolean('dofollow_default')->nullable();
            $table->integer('points')->default(0);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_dofollow')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['daily_post_limit', 'total_post_limit', 'is_unlimited', 'dofollow_default', 'points']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('is_dofollow');
        });
    }
};
