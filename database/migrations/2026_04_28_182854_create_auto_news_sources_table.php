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
        Schema::create('auto_news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source_url');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('posts_per_run')->default(5);
            $table->integer('fetch_interval_hours')->default(24);
            $table->timestamp('last_run_at')->nullable();
            $table->string('featured_image_source')->default('pexels'); // none, dalle, pexels, unsplash
            $table->integer('in_content_images_count')->default(1);
            $table->string('in_content_image_source')->default('pexels'); // none, dalle, pexels, unsplash
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_news_sources');
    }
};
