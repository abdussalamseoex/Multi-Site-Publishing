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

    /**
     * Upload an OG image, resize exactly to 1200x630, and convert to WebP.
     */
    public static function uploadAndConvertOgImage(UploadedFile $file, $directory = 'posts', $quality = 85)
    {
        if (!$file) return null;

        try {
            $filename = time() . '_og_' . uniqid() . '.webp';
            $destinationPath = public_path("uploads/{$directory}");
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $sourcePath = $file->getRealPath();
            
            if (!function_exists('imagewebp')) throw new \Exception('GD WebP not supported');

            $info = getimagesize($sourcePath);
            if (!$info) throw new \Exception('Invalid image');

            $mime = $info['mime'];
            switch ($mime) {
                case 'image/jpeg': $image = imagecreatefromjpeg($sourcePath); break;
                case 'image/png': $image = imagecreatefrompng($sourcePath); break;
                case 'image/gif': $image = imagecreatefromgif($sourcePath); break;
                case 'image/webp': $image = imagecreatefromwebp($sourcePath); break;
                default: throw new \Exception('Unsupported image type');
            }

            if (!$image) throw new \Exception('Failed to create image from source');

            $targetW = 1200;
            $targetH = 630;
            $width = imagesx($image);
            $height = imagesy($image);
            
            $newImage = imagecreatetruecolor($targetW, $targetH);
            
            // Preserve transparency for PNG/WebP (even if filling with white bg is safer for OG)
            $white = imagecolorallocate($newImage, 255, 255, 255);
            imagefill($newImage, 0, 0, $white);
            
            // Resize proportionally to cover or fit? Let's do fit to prevent cropping important parts
            $ratio = min($targetW / $width, $targetH / $height);
            $w = $width * $ratio;
            $h = $height * $ratio;
            $x = ($targetW - $w) / 2;
            $y = ($targetH - $h) / 2;
            
            imagecopyresampled($newImage, $image, $x, $y, 0, 0, $w, $h, $width, $height);
            
            $result = imagewebp($newImage, "{$destinationPath}/{$filename}", $quality);
            
            imagedestroy($image);
            imagedestroy($newImage);

            if ($result) {
                return "/uploads/{$directory}/{$filename}";
            }
            
            throw new \Exception('Failed to save WebP');

        } catch (\Throwable $e) {
            Log::error("ImageService OG Upload Error: " . $e->getMessage());
            // Fallback to normal upload if resize fails
            return self::uploadAndConvert($file, $directory);
        }
    }

    /**
     * Extract the most prominent color from an image.
     * Useful for syncing primary theme color with the site logo.
     */
    public static function getProminentColor($filePath)
    {
        if (!file_exists($filePath)) return null;

        try {
            $info = getimagesize($filePath);
            if (!$info) return null;

            $mime = $info['mime'];
            switch ($mime) {
                case 'image/jpeg': $image = imagecreatefromjpeg($filePath); break;
                case 'image/png': $image = imagecreatefrompng($filePath); break;
                case 'image/gif': $image = imagecreatefromgif($filePath); break;
                case 'image/webp': $image = imagecreatefromwebp($filePath); break;
                default: return null;
            }

            if (!$image) return null;

            // Resize to 20x20 and find dominant color
            $thumbW = 20;
            $thumbH = 20;
            $thumb = imagecreatetruecolor($thumbW, $thumbH);
            
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            
            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumbW, $thumbH, imagesx($image), imagesy($image));
            
            $colors = [];
            for ($x = 0; $x < $thumbW; $x++) {
                for ($y = 0; $y < $thumbH; $y++) {
                    $index = imagecolorat($thumb, $x, $y);
                    $rgba = imagecolorsforindex($thumb, $index);
                    
                    if ($rgba['alpha'] > 100) continue;
                    
                    $brightness = ($rgba['red'] * 299 + $rgba['green'] * 587 + $rgba['blue'] * 114) / 1000;
                    if ($brightness > 240 || $brightness < 15) continue;

                    $hex = sprintf("#%02x%02x%02x", $rgba['red'], $rgba['green'], $rgba['blue']);
                    $colors[$hex] = ($colors[$hex] ?? 0) + 1;
                }
            }

            imagedestroy($image);
            imagedestroy($thumb);

            if (empty($colors)) return null;

            arsort($colors);
            return array_key_first($colors);

        } catch (\Throwable $e) {
            \Log::error("Color Extraction Error: " . $e->getMessage());
            return null;
        }
    }
}
