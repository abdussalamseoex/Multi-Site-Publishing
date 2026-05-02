<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AIContentService
{
    // ============================================================
    // MAIN ENTRY POINT
    // ============================================================
    public function generatePost($keyword, $settings, $userId = null)
    {
        $openaiKey = Setting::get('openai_api_key');
        if (empty($openaiKey)) {
            throw new \Exception('OpenAI API Key is not set. Please configure it in Settings.');
        }

        // --- Extract settings ---
        $language        = $settings['language']               ?? 'English';
        $articleLength   = (int)($settings['article_length']   ?? 1000);
        $generateTitle   = $settings['generate_title']         ?? 'yes';
        $imageCount      = (int)($settings['in_content_images_count'] ?? 1);
        $aiModel         = $settings['ai_model']               ?? 'gpt-4o-mini';
        $featuredSources = $settings['featured_image_sources'] ?? [];
        $inContentSources= $settings['in_content_image_sources'] ?? [];
        $enableOutbound  = filter_var($settings['enable_outbound_links'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $outboundCount   = (int)($settings['outbound_links_count'] ?? 1);
        $status          = $settings['status'] ?? 'published';

        \Log::info("=== AI GENERATION START ===");
        \Log::info("Keyword: $keyword | Language: $language | Length: $articleLength | Model: $aiModel");
        \Log::info("Outbound: " . ($enableOutbound ? 'YES ('.$outboundCount.')' : 'NO'));
        \Log::info("Featured Sources: " . json_encode($featuredSources));
        \Log::info("InContent Sources: " . json_encode($inContentSources) . " | Count: $imageCount");

        // ============================================================
        // STEP 1: GENERATE ARTICLE CONTENT FROM OPENAI
        // ============================================================
        $contentData = $this->callOpenAI($keyword, $language, $articleLength, $generateTitle, $aiModel, $openaiKey);
        if (!$contentData || empty($contentData['content'])) {
            throw new \Exception('OpenAI did not return valid content. Check API key or try again.');
        }

        $title   = !empty($contentData['title']) ? $contentData['title'] : $keyword;
        $content = $contentData['content'];

        \Log::info("Content generated. Word count: " . str_word_count(strip_tags($content)));

        // ============================================================
        // STEP 2: INJECT OUTBOUND LINKS (Post-process)
        // ============================================================
        if ($enableOutbound && $outboundCount > 0) {
            $content = $this->injectOutboundLinks($content, $keyword, $outboundCount);
        }

        // ============================================================
        // STEP 3: FEATURED IMAGE
        // ============================================================
        $featuredImageUrl = null;
        if (!empty($featuredSources)) {
            $sources = is_array($featuredSources) ? $featuredSources : [$featuredSources];
            $sources = array_filter($sources, fn($s) => !empty($s) && $s !== 'none');
            if (!empty($sources)) {
                $imgData = $this->fetchImageFromSource($keyword, $sources[0], $openaiKey);
                if ($imgData) {
                    $featuredImageUrl = $imgData['url'];
                    \Log::info("Featured image fetched from: " . $sources[0]);
                }
            }
        }

        // ============================================================
        // STEP 4: IN-CONTENT IMAGES (Post-process injection)
        // ============================================================
        if ($imageCount > 0 && !empty($inContentSources)) {
            $sources = is_array($inContentSources) ? $inContentSources : [$inContentSources];
            $sources = array_filter($sources, fn($s) => !empty($s) && $s !== 'none');

            if (!empty($sources)) {
                $content = $this->injectInContentImages($content, $keyword, $sources, $imageCount, $openaiKey);
            }
        }

        // ============================================================
        // STEP 5: CLEANUP
        // ============================================================
        $content = str_replace('[IMAGE_PLACEHOLDER]', '', $content);
        $content = $this->stripIntroductionHeadings($content);
        $content = trim($content);

        // ============================================================
        // STEP 6: SAVE POST
        // ============================================================
        $cleanTitle = strip_tags(trim($title, ' "\''));
        $slug = Str::slug($cleanTitle);
        if (empty($slug)) $slug = Str::slug($keyword);
        if (Post::where('slug', $slug)->exists()) $slug .= '-' . time();

        $post = new Post();
        $post->user_id         = $userId ?? auth()->id() ?? 1;
        $post->category_id     = $settings['category_id'];
        $post->title           = $cleanTitle ?: $keyword;
        $post->slug            = $slug;
        $post->summary         = $contentData['meta_description'] ?? '';
        $post->content         = $content;
        $post->featured_image  = $featuredImageUrl;
        $post->status          = ($status === 'scheduled') ? 'published' : $status;
        $post->meta_title      = $cleanTitle ?: $keyword;
        $post->meta_description= $contentData['meta_description'] ?? '';
        $post->meta_keywords   = $contentData['meta_keywords'] ?? '';
        $post->created_at      = now();
        $post->updated_at      = now();
        $post->save();

        \Log::info("Post saved. ID: {$post->id} | Title: {$post->title}");
        \Log::info("=== AI GENERATION COMPLETE ===");

        return $post;
    }

    // ============================================================
    // OPENAI CONTENT GENERATION
    // ============================================================
    private function callOpenAI($keyword, $language, $articleLength, $generateTitle, $aiModel, $apiKey)
    {
        if ($generateTitle === 'no') {
            $titleRule = "- \"title\": MUST be exactly this string: \"{$keyword}\" — do not change it at all.";
        } else {
            $titleRule = "- \"title\": A compelling, SEO-friendly headline about \"{$keyword}\". No colons. No clichés like 'Unlocking' or 'Discovering'.";
        }

        $prompt = <<<PROMPT
You are a professional journalist and SEO content writer. Write a detailed, engaging article about the following topic:

TOPIC: "{$keyword}"
LANGUAGE: {$language}
REQUIRED WORD COUNT: approximately {$articleLength} words (this is mandatory — do not write less)

WRITING RULES:
- The entire article must be written in {$language}
- Write in a professional, authoritative, and human tone
- Use <h2> and <h3> tags for subheadings (never <h1>)
- Use <p> tags for every paragraph
- Do NOT use <hr> tags or horizontal lines anywhere
- Do NOT start the article with a heading — start directly with a paragraph
- Do NOT add an "Introduction" or "Conclusion" heading
- Provide deep analysis, real-world examples, and expert insights
- The content must be 100% unique and valuable

RESPONSE FORMAT:
Return a valid JSON object with EXACTLY these four keys:
{$titleRule}
- "meta_description": A 150-160 character SEO meta description about "{$keyword}"
- "meta_keywords": 6-8 relevant SEO keywords as a comma-separated string
- "content": The full article HTML content (must be approximately {$articleLength} words)

CRITICAL: Return ONLY the JSON object. No markdown. No explanation. No extra text.
PROMPT;

        \Log::info("Calling OpenAI model: $aiModel for keyword: $keyword");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(180)->post('https://api.openai.com/v1/chat/completions', [
            'model'           => $aiModel,
            'messages'        => [
                ['role' => 'system', 'content' => 'You are a professional SEO content writer and journalist. You always output valid JSON only.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.7,
            'max_tokens'      => 16000,
        ]);

        if (!$response->successful()) {
            \Log::error("OpenAI API Error: " . $response->body());
            throw new \Exception('OpenAI API Error: ' . $response->status());
        }

        $raw  = $response->json()['choices'][0]['message']['content'] ?? '';
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error("JSON Parse Error. Raw: " . substr($raw, 0, 500));
            throw new \Exception('OpenAI returned invalid JSON.');
        }

        return $data;
    }

    // ============================================================
    // OUTBOUND LINK INJECTION
    // ============================================================
    private function injectOutboundLinks($content, $keyword, $count)
    {
        \Log::info("Injecting $count outbound links for: $keyword");

        $allLinks = [];

        // First: try Google Custom Search for REAL article URLs
        $search1 = $this->fetchFromGoogle($keyword, 'web');
        if ($search1 && !empty($search1['url'])) $allLinks[] = $search1;

        if (count($allLinks) < $count) {
            $search2 = $this->fetchFromGoogle($keyword . ' guide explained', 'web');
            if ($search2 && !empty($search2['url'])) $allLinks[] = $search2;
        }

        // Fallback: real Wikipedia article URL (not search)
        if (count($allLinks) < $count) {
            $wikiSlug = urlencode(str_replace(' ', '_', ucwords($keyword)));
            $allLinks[] = [
                'url'   => "https://en.wikipedia.org/wiki/{$wikiSlug}",
                'title' => $keyword . ' - Wikipedia',
            ];
        }

        // Additional fallbacks with REAL article-level URLs
        $fallbacks = [
            ['url' => 'https://www.investopedia.com/terms/' . strtolower(substr($keyword, 0, 1)) . '/' . Str::slug($keyword) . '.asp', 'title' => $keyword . ' Definition - Investopedia'],
            ['url' => 'https://www.statista.com/topics/' . Str::slug($keyword) . '/', 'title' => $keyword . ' Statistics - Statista'],
        ];
        foreach ($fallbacks as $fb) {
            if (count($allLinks) >= $count) break;
            $allLinks[] = $fb;
        }

        $allLinks = array_slice($allLinks, 0, $count);

        // Inject into content
        $paragraphs = explode('</p>', $content);
        $total      = count($paragraphs);

        if ($total < 3) return $content;

        foreach ($allLinks as $index => $link) {
            $pos    = (int) floor($total / (count($allLinks) + 1)) * ($index + 1);
            $pos    = max(1, min($pos, $total - 2));
            $anchor = htmlspecialchars($link['title'] ?? $keyword);
            $url    = htmlspecialchars($link['url']);
            // Append link before closing </p>
            $paragraphs[$pos] = rtrim($paragraphs[$pos])
                . " <a href=\"{$url}\" target=\"_blank\" rel=\"noopener nofollow\" style=\"color:#4f46e5;font-weight:700;\">{$anchor}</a>";
        }

        \Log::info("Outbound links injected: " . count($allLinks));
        return implode('</p>', $paragraphs);
    }

    // ============================================================
    // IN-CONTENT IMAGE INJECTION
    // ============================================================
    private function injectInContentImages($content, $keyword, $sources, $count, $openaiKey)
    {
        \Log::info("Injecting $count in-content images from sources: " . implode(',', $sources));

        $paragraphs = explode('</p>', $content);
        $total      = count($paragraphs);

        if ($total < 3) return $content;

        for ($i = 0; $i < $count; $i++) {
            $source  = $sources[$i % count($sources)];
            $imgData = $this->fetchImageFromSource($keyword, $source, $openaiKey);

            if (!$imgData) {
                \Log::warning("Image fetch returned null for source: $source");
                continue;
            }

            $imgUrl  = $imgData['url'];
            $credit  = $imgData['credit'] ?? '';

            $figureHtml  = '<figure style="margin:2.5rem 0;overflow:hidden;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 4px 24px rgba(0,0,0,0.08);">';
            $figureHtml .= '<img src="' . $imgUrl . '" alt="' . htmlspecialchars($keyword) . '" style="width:100%;height:auto;display:block;">';
            if (!empty($credit)) {
                $figureHtml .= '<figcaption style="padding:8px 16px;font-size:11px;color:#94a3b8;text-align:center;font-style:italic;border-top:1px solid #f1f5f9;">Image: ' . $credit . '</figcaption>';
            }
            $figureHtml .= '</figure>';

            // Determine insertion position
            $pos = (int) floor($total / ($count + 1)) * ($i + 1);
            $pos = max(1, min($pos, $total - 2));

            array_splice($paragraphs, $pos, 0, [$figureHtml]);
            $total = count($paragraphs); // update after splice

            \Log::info("Image injected at position $pos from source: $source");
        }

        return implode('</p>', $paragraphs);
    }

    // ============================================================
    // FETCH IMAGE FROM A SPECIFIC SOURCE
    // ============================================================
    private function fetchImageFromSource($keyword, $source, $openaiKey = null)
    {
        \Log::info("Fetching image from source: $source for: $keyword");

        switch ($source) {
            case 'pexels':
                return $this->fetchFromPexels($keyword);
            case 'unsplash':
                return $this->fetchFromUnsplash($keyword);
            case 'google':
                return $this->fetchFromGoogle($keyword, 'image');
            case 'dalle':
                $key = $openaiKey ?? Setting::get('openai_api_key');
                return $this->fetchFromDalle($keyword, $key);
            default:
                \Log::warning("Unknown image source: $source");
                return null;
        }
    }

    // ============================================================
    // IMAGE SOURCES
    // ============================================================
    private function fetchFromPexels($query)
    {
        $key = Setting::get('pexels_api_key');
        if (!$key) { \Log::warning("Pexels API key not set."); return null; }

        $response = Http::withHeaders(['Authorization' => $key])
            ->timeout(15)
            ->get('https://api.pexels.com/v1/search', [
                'query'       => $query,
                'per_page'    => 5,
                'orientation' => 'landscape',
            ]);

        if ($response->successful()) {
            $photo = $response->json()['photos'][0] ?? null;
            if ($photo) {
                return [
                    'url'    => $photo['src']['large'],
                    'credit' => '<a href="' . $photo['url'] . '" target="_blank" rel="nofollow">' . $photo['photographer'] . ' on Pexels</a>',
                ];
            }
        }
        \Log::warning("Pexels returned no results for: $query");
        return null;
    }

    private function fetchFromUnsplash($query)
    {
        $key = Setting::get('unsplash_api_key');
        if (!$key) { \Log::warning("Unsplash API key not set."); return null; }

        $response = Http::withHeaders(['Authorization' => 'Client-ID ' . $key])
            ->timeout(15)
            ->get('https://api.unsplash.com/search/photos', [
                'query'       => $query,
                'per_page'    => 5,
                'orientation' => 'landscape',
            ]);

        if ($response->successful()) {
            $result = $response->json()['results'][0] ?? null;
            if ($result) {
                return [
                    'url'    => $result['urls']['regular'],
                    'credit' => '<a href="' . $result['links']['html'] . '" target="_blank" rel="nofollow">' . $result['user']['name'] . ' on Unsplash</a>',
                ];
            }
        }
        \Log::warning("Unsplash returned no results for: $query");
        return null;
    }

    private function fetchFromGoogle($query, $type = 'image')
    {
        $apiKey = Setting::get('google_search_api_key');
        $cx     = Setting::get('google_search_engine_id');

        if (!$apiKey || !$cx) {
            \Log::warning("Google Search API key or CX not configured.");
            return null;
        }

        $params = ['key' => $apiKey, 'cx' => $cx, 'q' => $query, 'num' => 5];

        if ($type === 'image') {
            $params['searchType'] = 'image';
            $params['imgSize']    = 'large';
            $params['imgType']    = 'photo';
        }

        $response = Http::timeout(15)->get('https://www.googleapis.com/customsearch/v1', $params);

        if ($response->successful()) {
            $items = $response->json()['items'] ?? [];
            if (!empty($items)) {
                $item = $items[0];
                if ($type === 'image') {
                    return [
                        'url'    => $item['link'],
                        'credit' => '<a href="' . ($item['image']['contextLink'] ?? '#') . '" target="_blank" rel="nofollow">Source via Google Images</a>',
                    ];
                } else {
                    return ['url' => $item['link'], 'title' => $item['title']];
                }
            }
        }
        \Log::warning("Google Search returned no results. Error: " . substr($response->body(), 0, 200));
        return null;
    }

    private function fetchFromDalle($query, $apiKey)
    {
        if (!$apiKey) return null;

        $dallePrompt = "A professional, realistic editorial photograph for a news article about: \"{$query}\". "
            . "High-quality journalism photography style. Natural lighting. Sharp focus. No text, no watermarks, no logos.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/images/generations', [
            'model'  => 'dall-e-3',
            'prompt' => $dallePrompt,
            'n'      => 1,
            'size'   => '1792x1024',
        ]);

        if ($response->successful()) {
            $tempUrl = $response->json()['data'][0]['url'] ?? null;
            if ($tempUrl) {
                $imageContent = @file_get_contents($tempUrl);
                if ($imageContent) {
                    $dir      = public_path('uploads/posts');
                    $filename = 'dalle_' . time() . '_' . uniqid() . '.jpg';
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    file_put_contents($dir . '/' . $filename, $imageContent);
                    return ['url' => '/uploads/posts/' . $filename, 'credit' => 'AI Generated'];
                }
            }
        }
        \Log::error("DALL-E Error: " . substr($response->body(), 0, 200));
        return null;
    }

    // ============================================================
    // HELPERS
    // ============================================================
    private function stripIntroductionHeadings($content)
    {
        // Remove any heading that comes before the first <p>
        $firstP = stripos($content, '<p');
        if ($firstP === false) return $content;
        $before = substr($content, 0, $firstP);
        $after  = substr($content, $firstP);
        $before = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/is', '', $before);
        return trim($before) . $after;
    }

    // Legacy method kept for backward compatibility
    private function fetchImage($query, $sources, $count = 1)
    {
        $images  = [];
        $sources = is_array($sources) ? $sources : [$sources];
        $sources = array_filter($sources);
        if (empty($sources)) return $count === 1 ? null : [];

        $openaiKey = Setting::get('openai_api_key');
        for ($i = 0; $i < $count; $i++) {
            $source = $sources[$i % count($sources)];
            $img    = $this->fetchImageFromSource($query, $source, $openaiKey);
            if ($img) $images[] = $img;
        }

        return $count === 1 ? ($images[0] ?? null) : $images;
    }
}
