<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class ImageService
{
    /**
     * Check if WebP conversion is enabled in settings.
     */
    public static function isEnabled()
    {
        return Setting::get('enable_auto_webp', 'off') === 'on';
    }

    /**
     * Upload an image file, convert it to WebP if enabled, and save it.
     */
    public static function uploadAndConvert(UploadedFile $file, $directory = 'posts', $quality = 80)
    {
        if (!$file) return null;

        // If disabled or not an image, just move it as is
        if (!self::isEnabled() || !str_starts_with($file->getMimeType(), 'image/')) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path("uploads/{$directory}"), $filename);
            return "/uploads/{$directory}/{$filename}";
        }

        try {
            $filename = time() . '_' . uniqid() . '.webp';
            $destinationPath = public_path("uploads/{$directory}");
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $sourcePath = $file->getRealPath();
            if (self::nativeConvertToWebp($sourcePath, "{$destinationPath}/{$filename}", $quality)) {
                return "/uploads/{$directory}/{$filename}";
            }
            
            // Fallback if native conversion fails
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            return "/uploads/{$directory}/{$filename}";
            
        } catch (\Throwable $e) {
            Log::error("ImageService Upload Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Download an image from a URL, convert it to WebP if enabled, and save it.
     */
    public static function downloadAndConvert($url, $directory = 'posts', $quality = 80)
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) return $url;
        if (str_starts_with($url, '/')) return $url;

        if (!self::isEnabled()) return $url;

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->timeout(10)
                ->get($url);

            if (!$response->successful()) return $url;

            $tempPath = tempnam(sys_get_temp_dir(), 'img');
            file_put_contents($tempPath, $response->body());
            
            $filename = time() . '_' . uniqid() . '.webp';
            $destinationPath = public_path("uploads/{$directory}");
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            if (self::nativeConvertToWebp($tempPath, "{$destinationPath}/{$filename}", $quality)) {
                @unlink($tempPath);
                return "/uploads/{$directory}/{$filename}";
            }
            
            @unlink($tempPath);
            return $url;
            
        } catch (\Throwable $e) {
            Log::error("ImageService Download Error: " . $e->getMessage());
            return $url;
        }
    }

    /**
     * Parse HTML content, find base64 images, convert to WebP if enabled.
     */
    public static function parseAndConvertHtmlImages($html, $directory = 'posts', $quality = 80)
    {
        if (empty($html) || !self::isEnabled()) return $html;

        $pattern = '/src=["\'](data:image\/([^;]+);base64,([^"\']+)?)["\']/i';
        
        return preg_replace_callback($pattern, function($matches) use ($directory, $quality) {
            $mime = $matches[2];
            $base64Data = $matches[3];
            
            try {
                $imageContent = base64_decode($base64Data);
                if (!$imageContent) return $matches[0];

                $tempPath = tempnam(sys_get_temp_dir(), 'b64');
                file_put_contents($tempPath, $imageContent);

                $filename = time() . '_' . uniqid() . '.webp';
                $destinationPath = public_path("uploads/{$directory}");
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                if (self::nativeConvertToWebp($tempPath, "{$destinationPath}/{$filename}", $quality)) {
                    @unlink($tempPath);
                    return 'src="/uploads/' . $directory . '/' . $filename . '"';
                }
                
                @unlink($tempPath);
                return $matches[0];
                
            } catch (\Throwable $e) {
                return $matches[0];
            }
        }, $html);
    }

    /**
     * Native PHP GD conversion to WebP.
     */
    private static function nativeConvertToWebp($sourcePath, $destinationPath, $quality = 80)
    {
        if (!function_exists('imagewebp')) return false;

        $info = getimagesize($sourcePath);
        if (!$info) return false;

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg': $image = imagecreatefromjpeg($sourcePath); break;
            case 'image/png': $image = imagecreatefrompng($sourcePath); break;
            case 'image/gif': $image = imagecreatefromgif($sourcePath); break;
            case 'image/webp': $image = imagecreatefromwebp($sourcePath); break;
            default: return false;
        }

        if (!$image) return false;

        // Preserve transparency for PNG/WebP
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $result = imagewebp($image, $destinationPath, $quality);
        imagedestroy($image);

        return $result;
    }
}
