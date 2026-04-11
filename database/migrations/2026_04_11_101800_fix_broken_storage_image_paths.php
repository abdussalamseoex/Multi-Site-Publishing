<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copy physically existing files from the broken storage directory to the public directory
        $oldStoragePath = storage_path('app/public/featured_images');
        $newPublicPath = public_path('uploads/posts');

        if (File::exists($oldStoragePath)) {
            if (!File::exists($newPublicPath)) {
                File::makeDirectory($newPublicPath, 0755, true);
            }
            
            // Loop through all files and copy them
            $files = File::files($oldStoragePath);
            foreach ($files as $file) {
                $destination = $newPublicPath . '/' . $file->getFilename();
                if (!File::exists($destination)) {
                    File::copy($file->getPathname(), $destination);
                }
            }
        }

        // Update the database records to point to the new path
        $posts = DB::table('posts')->where('featured_image', 'like', '/storage/featured_images/%')->get();
        
        foreach ($posts as $post) {
            $newImage = str_replace('/storage/featured_images/', '/uploads/posts/', $post->featured_image);
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
        //
    }
};
