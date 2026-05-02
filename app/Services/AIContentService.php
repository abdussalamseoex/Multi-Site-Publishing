<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AIContentService
{
    public function generatePost($keyword, $settings, $userId = null)
    {
        $openaiKey = Setting::get('openai_api_key');
        if (empty($openaiKey)) {
            throw new \Exception('OpenAI API Key is not set.');
        }

        $language = $settings['language'] ?? 'English';
        $articleLength = $settings['article_length'] ?? 800;
        $generateTitle = $settings['generate_title'] ?? 'yes';
        $imageCount = $settings['in_content_images_count'] ?? 1;
        
        $featuredSources = $settings['featured_image_sources'] ?? ($settings['featured_image_source'] ?? []);
        $inContentSources = $settings['in_content_image_sources'] ?? ($settings['in_content_image_source'] ?? []);
        
        $enableOutbound = $settings['enable_outbound_links'] ?? false;
        $outboundCount = $settings['outbound_links_count'] ?? 1;

        // 1. Fetch REAL Outbound Links if enabled
        $realLinks = [];
        if ($enableOutbound) {
            \Log::info("Searching for real outbound links for: " . $keyword);
            for ($i = 0; $i < $outboundCount; $i++) {
                $searchQuery = $keyword . " official news source " . ($i + 1);
                $linkData = $this->fetchFromGoogle($searchQuery, 'web');
                if ($linkData) {
                    $realLinks[] = $linkData;
                }
            }
        }

        // 2. Generate Content with REAL links
        $contentData = $this->generateContentFromOpenAI($keyword, $language, $imageCount, $articleLength, $openaiKey, $generateTitle, $enableOutbound, $realLinks);
        if (!$contentData) {
            throw new \Exception('Failed to generate content from OpenAI.');
        }

        // Sanitize Title
        if (!empty($contentData['title']) && $generateTitle !== 'no') {
            $contentData['title'] = $this->sanitizeTitle($contentData['title']);
        }

        // 3. Handle Featured Image
        $featuredImageUrl = null;
        if (!empty($featuredSources) && $featuredSources !== 'none') {
            $imgData = $this->fetchImage($keyword, $featuredSources);
            if ($imgData) {
                $featuredImageUrl = $imgData['url'];
            }
        }

        // 4. Handle In-Content Images
        $content = $contentData['content'];
        if ($imageCount > 0 && !empty($inContentSources) && $inContentSources !== 'none') {
            $batchImages = $this->fetchImage($keyword, $inContentSources, $imageCount);
            
            if (!empty($batchImages) && is_array($batchImages)) {
                foreach ($batchImages as $i => $imgData) {
                    $imgUrl = $imgData['url'];
                    $credit = $imgData['credit'];
                    
                    $imageTag = '<figure class="my-10 overflow-hidden rounded-2xl bg-gray-50 border border-gray-100 shadow-lg p-3">';
                    $imageTag .= '<img src="' . $imgUrl . '" alt="' . htmlspecialchars($keyword) . '" class="w-full h-auto rounded-xl transition-transform duration-500 hover:scale-[1.02]">';
                    if (!empty($credit)) {
                        $imageTag .= '<figcaption class="flex items-center justify-center space-x-2 text-xs text-gray-400 mt-4 italic font-medium tracking-tight uppercase"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg><span>Image: ' . $credit . '</span></figcaption>';
                    }
                    $imageTag .= '</figure>';
                    
                    if (strpos($content, '[IMAGE_PLACEHOLDER]') !== false) {
                        $content = preg_replace('/\[IMAGE_PLACEHOLDER\]/', $imageTag, $content, 1);
                    } else {
                        $paragraphs = explode('</p>', $content);
                        $insertPos = floor(count($paragraphs) / ($imageCount + 1)) * ($i + 1);
                        $insertPos = max(1, min($insertPos, count($paragraphs) - 1));
                        array_splice($paragraphs, $insertPos, 0, $imageTag);
                        $content = implode('</p>', $paragraphs);
                    }
                }
            }
        }

        $content = str_replace('[IMAGE_PLACEHOLDER]', '', $content);
        $content = preg_replace('/<h[1-6][^>]*>.*?(Introduction|ভূমিকা|পরিचय|Introducción|Overview|Background|সারসংক্ষেপ).*?<\/h[1-6]>/is', '', $content);
        $content = $this->stripHeadingsBeforeFirstParagraph($content);
        $content = trim($content);

        // 4. Save Post
        $cleanTitle = str_replace(['"', '\''], '', $contentData['title']);
        $slug = Str::slug($cleanTitle);
        $existing = Post::where('slug', $slug)->exists();
        if ($existing) {
            $slug = $slug . '-' . time();
        }

        $status = $settings['status'] ?? 'published';
        $createdAt = now();
        if ($status === 'scheduled' && isset($settings['schedule_time'])) {
            $status = 'published';
            $createdAt = Carbon::parse($settings['schedule_time']);
        }

        $post = new Post();
        $post->user_id = $userId ?? auth()->id() ?? 1;
        $post->category_id = $settings['category_id'];
        $post->title = $contentData['title'];
        $post->slug = $slug;
        $post->summary = $contentData['meta_description'] ?? '';
        $post->content = $content;
        $post->featured_image = $featuredImageUrl;
        $post->status = $status === 'scheduled' ? 'published' : $status; 
        $post->meta_title = $contentData['title'];
        $post->meta_description = $contentData['meta_description'] ?? '';
        $post->meta_keywords = $contentData['meta_keywords'] ?? '';
        $post->created_at = $createdAt;
        $post->updated_at = $createdAt;
        $post->save();

        return $post;
    }

    private function generateContentFromOpenAI($keyword, $language, $imageCount, $articleLength, $apiKey, $generateTitle = 'yes', $enableOutbound = false, $realLinks = [])
    {
        $imageInstruction = $imageCount > 0 ? "IMPORTANT: You MUST insert the exact text '[IMAGE_PLACEHOLDER]' at logical breaks between major sections exactly $imageCount times. Spread them out logically." : "";

        $outboundInstruction = "";
        if ($enableOutbound && !empty($realLinks)) {
            $linksHtml = "SEO REQUIREMENT: You MUST naturally integrate the following REAL outbound links into the article content using descriptive anchor text:\n";
            foreach ($realLinks as $link) {
                $linksHtml .= "- <a href='{$link['url']}'>{$link['title']}</a>\n";
            }
            $outboundInstruction = $linksHtml . "Ensure these links flow naturally within the paragraphs.";
        }

        $titleInstruction = $generateTitle === 'no' 
            ? "- 'title': MUST BE EXACTLY THE STRING '{keyword}' without any modifications."
            : "- 'title': A catchy, SEO-friendly title without the year unless necessary. DO NOT use colons (:) in the title. DO NOT start the title with clichés like 'Unlocking', 'Discovering', 'The Secret', or 'Guide'.";

        $defaultPrompt = "Write an EXTENSIVE, high-quality, SEO-optimized article about '{keyword}' in {language}.\n\nSTRICT REQUIREMENT: The article MUST be approximately {article_length} words long. Provide deep analysis, detailed explanations, and sub-sections to achieve this length.\n\nSTRUCTURE:\n- Use <p> for paragraphs.\n- Use <h2> and <h3> for subheadings.\n- DO NOT use <h1> or horizontal lines (<hr>).\n- DO NOT start with an 'Introduction' heading.\n\nFORMAT:\nReturn ONLY a valid JSON object with these keys:\n$titleInstruction\n- 'meta_description': A 150-160 character meta description.\n- 'meta_keywords': A comma-separated string of 5-8 SEO keywords.\n- 'content': Full HTML content.\n\n{image_instruction}\n{outbound_instruction}\n\nEEAT Guidelines: Ensure expertise and trust. Content should sound human, not AI-generated.";
        $promptTemplate = Setting::get('ai_writer_prompt', $defaultPrompt);

        // Fallback: If template doesn't contain placeholders, append them
        if (strpos($promptTemplate, '{image_instruction}') === false) {
            $promptTemplate .= "\n{image_instruction}";
        }
        if (strpos($promptTemplate, '{outbound_instruction}') === false) {
            $promptTemplate .= "\n{outbound_instruction}";
        }

        if ($generateTitle === 'no') {
            $promptTemplate = preg_replace('/-\s*\'title\':[^\n]+/', "- 'title': MUST BE EXACTLY THE STRING '{keyword}' without any modifications.", $promptTemplate);
        }

        $prompt = str_replace(
            ['{keyword}', '{language}', '{article_length}', '{image_instruction}', '{outbound_instruction}', '{current_year}'],
            [$keyword, $language, $articleLength, $imageInstruction, $outboundInstruction, date('Y')],
            $promptTemplate
        );

        // Final enforcement for gpt-4o-mini
        $prompt .= "\n\nCRITICAL: The 'content' MUST be very long, extremely detailed, and reach the {article_length} word goal. Provide multiple sections, in-depth analysis, and comprehensive coverage. DO NOT summarize.";

        $aiModel = $settings['ai_model'] ?? 'gpt-4o-mini';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $aiModel,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional SEO news journalist and senior editor. You output ONLY valid JSON.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $contentStr = $result['choices'][0]['message']['content'] ?? '';
            $data = json_decode($contentStr, true);
            
            // Log word count for debugging
            if ($data && isset($data['content'])) {
                $wordCount = str_word_count(strip_tags($data['content']));
                \Log::info("Generated content word count: $wordCount");
            }
            
            return $data;
        }

        \Log::error("OpenAI Error: " . $response->body());
        return false;
    }

    private function fetchImage($query, $sources, $count = 1)
    {
        $images = [];
        if (empty($sources)) return $count === 1 ? null : [];
        
        // Ensure sources is an array
        $sources = is_array($sources) ? $sources : [$sources];
        
        for ($i = 0; $i < $count; $i++) {
            // Round-robin through sources
            $source = $sources[$i % count($sources)];
            
            $img = null;
            if ($source === 'pexels') {
                $img = $this->fetchFromPexels($query);
            } elseif ($source === 'unsplash') {
                $img = $this->fetchFromUnsplash($query);
            } elseif ($source === 'google') {
                $img = $this->fetchFromGoogle($query, 'image');
            } elseif ($source === 'dalle') {
                $img = $this->fetchFromDalle($query);
            }
            
            if ($img) {
                $images[] = $img;
            }
        }

        return $count === 1 ? ($images[0] ?? null) : $images;
    }

    private function fetchFromPexels($query)
    {
        $pexelsKey = Setting::get('pexels_api_key');
        if (!$pexelsKey) return null;

        $response = Http::withHeaders(['Authorization' => $pexelsKey])->get("https://api.pexels.com/v1/search", [
            'query' => $query,
            'per_page' => 1,
            'orientation' => 'landscape'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $photo = $data['photos'][0] ?? null;
            if ($photo) {
                return [
                    'url' => $photo['src']['large'], 
                    'credit' => "<a href='{$photo['url']}' target='_blank' rel='nofollow'>{$photo['photographer']} on Pexels</a>"
                ];
            }
        }
        return null;
    }

    private function fetchFromUnsplash($query)
    {
        $unsplashKey = Setting::get('unsplash_api_key');
        if (!$unsplashKey) return null;

        $response = Http::withHeaders(['Authorization' => 'Client-ID ' . $unsplashKey])->get("https://api.unsplash.com/search/photos", [
            'query' => $query,
            'per_page' => 1,
            'orientation' => 'landscape'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $result = $data['results'][0] ?? null;
            if ($result) {
                return [
                    'url' => $result['urls']['regular'], 
                    'credit' => "<a href='{$result['links']['html']}' target='_blank' rel='nofollow'>{$result['user']['name']} on Unsplash</a>"
                ];
            }
        }
        return null;
    }

    private function fetchFromGoogle($query, $type = 'image')
    {
        $apiKey = Setting::get('google_search_api_key');
        $cx = Setting::get('google_search_engine_id');
        
        if (!$apiKey || !$cx) {
            \Log::warning("Google Search API Key or CX ID is missing.");
            return null;
        }

        $params = [
            'key' => $apiKey,
            'cx' => $cx,
            'q' => $query,
            'num' => 3 // Fetch a few to increase chances
        ];

        if ($type === 'image') {
            $params['searchType'] = 'image';
            $params['imgSize'] = 'large';
            $params['imgType'] = 'photo';
            $params['rights'] = '(cc_publicdomain|cc_attribute|cc_sharealike|cc_noncommercial|cc_noncom_sharealike)'; 
        }

        \Log::info("Fetching from Google ($type): " . $query);
        $response = Http::get("https://www.googleapis.com/customsearch/v1", $params);

        if ($response->successful()) {
            $data = $response->json();
            $items = $data['items'] ?? [];
            
            if (empty($items)) {
                \Log::warning("Google returned no results for: " . $query);
                return null;
            }

            $item = $items[0]; // Pick the first one
            
            if ($type === 'image') {
                \Log::info("Found Google Image: " . $item['link']);
                return [
                    'url' => $item['link'],
                    'credit' => "<a href='{$item['image']['contextLink']}' target='_blank' rel='nofollow'>Source via Google Images</a>"
                ];
            } else {
                return ['url' => $item['link'], 'title' => $item['title']];
            }
        } else {
            \Log::error("Google API Error: " . $response->body());
        }
        
        return null;
    }

    private function fetchFromDalle($query)
    {
        $openaiKey = Setting::get('openai_api_key');
        if (!$openaiKey) return null;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $openaiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/images/generations', [
            'model' => 'dall-e-3',
            'prompt' => "A high-end, professional, realistic editorial photograph for a news article about: " . $query . ". The image should look like it was shot with a high-end DSLR camera (like a Canon EOS or Nikon D850), featuring natural lighting, sharp focus, and a professional journalistic aesthetic. DO NOT include any text, typography, words, watermarks, signatures, or logos. Purely visual and realistic.",
            'n' => 1,
            'size' => '1024x1024'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $tempUrl = $data['data'][0]['url'] ?? null;
            if ($tempUrl) {
                $imageContent = @file_get_contents($tempUrl);
                if ($imageContent) {
                    $filename = 'ai_img_' . time() . '_' . uniqid() . '.jpg';
                    $path = public_path('uploads/posts/' . $filename);
                    if (!file_exists(public_path('uploads/posts'))) mkdir(public_path('uploads/posts'), 0777, true);
                    file_put_contents($path, $imageContent);
                    return ['url' => '/uploads/posts/' . $filename, 'credit' => "AI Generated"];
                }
            }
        }
        return null;
    }

    private function stripHeadingsBeforeFirstParagraph(string $content): string
    {
        $firstPPos = stripos($content, '<p');
        if ($firstPPos === false) return preg_replace('/^\s*(<h[1-6][^>]*>.*?<\/h[1-6]>\s*)+/is', '', $content);
        $before = substr($content, 0, $firstPPos);
        $after  = substr($content, $firstPPos);
        $before = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/is', '', $before);
        return trim($before) . $after;
    }

    private function sanitizeTitle(string $title): string
    {
        $title = trim($title, ' "\'');
        if (strpos($title, ':') !== false) {
            $parts = explode(':', $title, 2);
            $title = strlen(trim($parts[1] ?? '')) >= strlen(trim($parts[0])) ? trim($parts[1]) : trim($parts[0]);
        }
        $cliches = ['Unlocking', 'Discovering', 'The Secret to', 'The Ultimate Guide to'];
        foreach ($cliches as $cliche) if (stripos($title, $cliche) === 0) $title = trim(substr($title, strlen($cliche)));
        return ucfirst(trim($title, ' ,;'));
    }
}
