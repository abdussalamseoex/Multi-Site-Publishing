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
        // 1. Enhance visits table with user session & journey columns
        Schema::table('visits', function (Blueprint $table) {
            if (!Schema::hasColumn('visits', 'session_id')) {
                $table->string('session_id')->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('visits', 'page_title')) {
                $table->string('page_title')->nullable()->after('url');
            }
            if (!Schema::hasColumn('visits', 'city')) {
                $table->string('city')->nullable()->after('country');
            }
            if (!Schema::hasColumn('visits', 'device_type')) {
                $table->string('device_type')->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('visits', 'browser')) {
                $table->string('browser')->nullable()->after('device_type');
            }
            if (!Schema::hasColumn('visits', 'os')) {
                $table->string('os')->nullable()->after('browser');
            }
            if (!Schema::hasColumn('visits', 'dwell_seconds')) {
                $table->integer('dwell_seconds')->default(0)->after('os');
            }
        });

        // 2. Create active_sessions table for real-time live visitor tracking
        if (!Schema::hasTable('active_sessions')) {
            Schema::create('active_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_id')->unique();
                $table->string('ip_address')->nullable();
                $table->string('country')->nullable();
                $table->string('country_code', 10)->nullable();
                $table->string('city')->nullable();
                $table->string('device_type')->nullable(); // desktop, mobile, tablet
                $table->string('browser')->nullable();
                $table->string('os')->nullable();
                $table->text('referrer')->nullable();
                $table->string('referrer_domain')->nullable();
                $table->text('landing_page')->nullable();
                $table->text('current_page')->nullable();
                $table->string('current_page_title')->nullable();
                $table->integer('page_views')->default(1);
                $table->integer('dwell_seconds')->default(0);
                $table->boolean('is_bot')->default(false);
                $table->string('status')->default('active'); // active, idle, left
                $table->timestamp('started_at')->nullable();
                $table->timestamp('last_activity_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_sessions');

        Schema::table('visits', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                'session_id', 'page_title', 'city', 'device_type', 'browser', 'os', 'dwell_seconds'
            ], fn($col) => Schema::hasColumn('visits', $col));

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
