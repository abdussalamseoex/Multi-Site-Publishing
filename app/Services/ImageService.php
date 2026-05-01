<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class ImageService
{
    /**
     * Upload an image file, convert it to WebP, and save it.
     *
     * @param UploadedFile $file The uploaded file.
     * @param string $directory The directory inside public/uploads.
     * @param int $quality The quality of the WebP image (0-100).
     * @return string|null The relative path to the saved image.
     */
    public static function uploadAndConvert(UploadedFile $file, $directory = 'posts', $quality = 80)
    {
        if (!$file) return null;

        // Ensure it's an image. If not, just move it as is.
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path("uploads/{$directory}"), $filename);
            return "/uploads/{$directory}/{$filename}";
        }

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            
            $filename = time() . '_' . uniqid() . '.webp';
            $destinationPath = public_path("uploads/{$directory}");
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $image->toWebp($quality)->save("{$destinationPath}/{$filename}");
            
            return "/uploads/{$directory}/{$filename}";
            
        } catch (\Throwable $e) {
            Log::error("ImageService Upload Error: " . $e->getMessage());
            // Fallback to normal upload
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path("uploads/{$directory}"), $filename);
            return "/uploads/{$directory}/{$filename}";
        }
    }

    /**
     * Download an image from a URL, convert it to WebP, and save it.
     *
     * @param string $url The URL of the image.
     * @param string $directory The directory inside public/uploads.
     * @param int $quality The quality of the WebP image (0-100).
     * @return string The relative path to the saved image or original URL if failed.
     */
    public static function downloadAndConvert($url, $directory = 'posts', $quality = 80)
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) return $url;

        // If it's already a local relative path, return it
        if (str_starts_with($url, '/')) return $url;

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
                ->timeout(10)
                ->get($url);

            if (!$response->successful()) {
                return $url;
            }

            $content = $response->body();
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read($content);
            
            $filename = time() . '_' . uniqid() . '.webp';
            $destinationPath = public_path("uploads/{$directory}");
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $image->toWebp($quality)->save("{$destinationPath}/{$filename}");
            
            return "/uploads/{$directory}/{$filename}";
            
        } catch (\Throwable $e) {
            Log::error("ImageService Download Error: " . $e->getMessage());
            return $url; // Fallback to returning original URL
        }
    }

    /**
     * Parse HTML content, find base64 embedded images, convert them to WebP, and replace the src.
     *
     * @param string $html The HTML content (from Summernote).
     * @param string $directory The directory to save images.
     * @param int $quality The quality of the WebP image.
     * @return string The updated HTML.
     */
    public static function parseAndConvertHtmlImages($html, $directory = 'posts', $quality = 80)
    {
        if (empty($html)) return $html;

        // Use regex to find all base64 images in src attributes
        $pattern = '/src=["\'](data:image\/[^;]+;base64,([^"\']+)?)["\']/i';
        
        return preg_replace_callback($pattern, function($matches) use ($directory, $quality) {
            $dataUrl = $matches[1];
            $base64Data = $matches[2];
            
            try {
                $imageContent = base64_decode($base64Data);
                if (!$imageContent) return 'src="' . $dataUrl . '"';

                $manager = new ImageManager(new Driver());
                $image = $manager->read($imageContent);
                
                $filename = time() . '_' . uniqid() . '.webp';
                $destinationPath = public_path("uploads/{$directory}");
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                $image->toWebp($quality)->save("{$destinationPath}/{$filename}");
                
                $newSrc = "/uploads/{$directory}/{$filename}";
                return 'src="' . $newSrc . '"';
                
            } catch (\Throwable $e) {
                Log::error("ImageService HTML Parse Error: " . $e->getMessage());
                return 'src="' . $dataUrl . '"'; // Fallback
            }
        }, $html);
    }
}
