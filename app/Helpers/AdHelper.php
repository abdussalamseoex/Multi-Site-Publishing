<?php

namespace App\Helpers;

use App\Models\Setting;

class AdHelper
{
    /**
     * Parse HTML content and inject an ad code every X paragraphs.
     */
    public static function injectInArticleAds($content)
    {
        $selectedUnitKey = Setting::get('ad_placement_in_article');
        
        // If disabled or no unit selected, return original content
        if (empty($selectedUnitKey)) {
            return $content;
        }

        $adHtml = Setting::get($selectedUnitKey);
        
        if (empty(trim($adHtml))) {
            return $content;
        }

        $frequency = (int) Setting::get('ad_in_article_frequency', 3);
        if ($frequency < 1) {
            $frequency = 3;
        }

        // Add wrapper to ad
        $wrappedAd = '<div class="ad-container ad-in-article my-8 flex justify-center overflow-hidden w-full">' . $adHtml . '</div>';

        // Split content by </p> tags
        $paragraphs = explode('</p>', $content);
        $newContent = '';

        foreach ($paragraphs as $index => $paragraph) {
            if (trim($paragraph) !== '') {
                $newContent .= $paragraph . '</p>';
                // Inject after every $frequency paragraph, but not after the last one
                if (($index + 1) % $frequency == 0 && $index < count($paragraphs) - 2) {
                    $newContent .= $wrappedAd;
                }
            }
        }

        return $newContent;
    }
}
