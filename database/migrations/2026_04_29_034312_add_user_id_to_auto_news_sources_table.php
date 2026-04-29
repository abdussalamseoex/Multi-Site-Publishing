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
            $table->unsignedBigInteger('user_id')->nullable()->after('category_id')->comment('The author to assign these posts to');
            // Adding a foreign key is optional but good practice, though not strictly required if users can be hard-deleted.
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_news_sources', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
