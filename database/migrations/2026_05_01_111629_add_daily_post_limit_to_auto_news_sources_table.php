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
        Schema::table('auto_news_sources', function (Blueprint $table) {
            $table->integer('daily_post_limit')->nullable()->after('fetch_interval_hours');
            $table->boolean('use_smart_schedule')->default(false)->after('daily_post_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_news_sources', function (Blueprint $table) {
            $table->dropColumn(['daily_post_limit', 'use_smart_schedule']);
        });
    }
};
