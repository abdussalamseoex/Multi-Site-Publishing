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
        Schema::table('ai_bulk_campaigns', function (Blueprint $table) {
            $table->boolean('enable_outbound_links')->default(false)->after('interval_minutes');
            $table->integer('outbound_links_count')->default(1)->after('enable_outbound_links');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_bulk_campaigns', function (Blueprint $table) {
            $table->dropColumn(['enable_outbound_links', 'outbound_links_count']);
        });
    }
};
