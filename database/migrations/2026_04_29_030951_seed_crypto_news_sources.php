<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\AutoNewsSource;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We will add CoinTelegraph for highly reliable Crypto/Web3 news
        // CoinTelegraph provides excellent RSS data, stable feeds, and good quality images
        $sources = [
            ['name' => 'CoinTelegraph Crypto', 'url' => 'https://cointelegraph.com/rss', 'category' => 'Cryptocurrency & Web3'],
        ];

        foreach ($sources as $source) {
            if (!AutoNewsSource::where('source_url', $source['url'])->exists()) {
                
                // Get or create category
                $category = Category::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($source['category'])],
                    ['name' => $source['category'], 'is_active' => true]
                );

                AutoNewsSource::create([
                    'name' => $source['name'],
                    'source_url' => $source['url'],
                    'category_id' => $category->id,
                    'posts_per_run' => 5,
                    'fetch_interval_hours' => 2, // Check every 2 hours
                    'featured_image_source' => 'pexels', // It will still extract the original image from the page/rss first
                    'in_content_images_count' => 1,
                    'in_content_image_source' => 'pexels',
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
