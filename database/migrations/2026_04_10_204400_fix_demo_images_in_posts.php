<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix random images from initial seeder
        $posts = DB::table('posts')->where('featured_image', 'like', '%?random=%')->get();
        
        foreach ($posts as $post) {
            $newImage = 'https://picsum.photos/seed/demo-content-' . $post->id . '/1200/800';
            DB::table('posts')->where('id', $post->id)->update([
                'featured_image' => $newImage
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse
    }
};
