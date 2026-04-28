<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_news_sources', function (Blueprint $table) {
            // How many days to keep running (null = unlimited)
            $table->unsignedTinyInteger('duration_days')->nullable()->after('fetch_interval_hours');
            // Auto-calculated expiry date based on duration_days + created_at
            $table->timestamp('expires_at')->nullable()->after('duration_days');
        });
    }

    public function down(): void
    {
        Schema::table('auto_news_sources', function (Blueprint $table) {
            $table->dropColumn(['duration_days', 'expires_at']);
        });
    }
};
