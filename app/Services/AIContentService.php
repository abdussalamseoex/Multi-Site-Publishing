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
        $aiModel = $settings['ai_model'] ?? 'gpt-4o-mini';
        
        $featuredSources = $settings['featured_image_sources'] ?? ($settings['featured_image_source'] ?? []);
        $inContentSources = $settings['in_content_image_sources'] ?? ($settings['in_content_image_source'] ?? []);
        
        $enableOutbound = $settings['enable_outbound_links'] ?? false;
        $outboundCount = $settings['outbound_links_count'] ?? 1;

        // --- STEP 1: CONTENT GENERATION (Text Only Focus) ---
        $contentData = $this->generateContentFromOpenAI($keyword, $language, $imageCount, $articleLength, $openaiKey, $generateTitle, $aiModel);
        if (!$contentData) {
            throw new \Exception('Failed to generate content from OpenAI.');
        }

        $content = $contentData['content'];
        $title = $contentData['title'];

        // --- STEP 2: REAL OUTBOUND LINKS INJECTION (Manual) ---
        if ($enableOutbound) {
            \Log::info("Injecting real outbound links...");
            $realLinks = [];
            for ($i = 0; $i < $outboundCount; $i++) {
                $linkQuery = $keyword . " authoritative source " . ($i + 1);
                $link = $this->fetchFromGoogle($linkQuery, 'web');
                if ($link) $realLinks[] = $link;
            }

            if (!empty($realLinks)) {
                $paragraphs = explode('</p>', $content);
                foreach ($realLinks as $index => $link) {
                    $pos = floor(count($paragraphs) / (count($realLinks) + 1)) * ($index + 1);
                    $pos = max(1, min($pos, count($paragraphs) - 1));
                    $linkHtml = " <a href='{$link['url']}' target='_blank' rel='noopener nofollow' class='text-indigo-600 font-bold hover:underline'>{$link['title']}</a>";
                    // Inject into the end of the paragraph
                    $paragraphs[$pos] .= $linkHtml;
                }
                $content = implode('</p>', $paragraphs);
            }
        }

        // --- STEP 3: FEATURED IMAGE ---
        $featuredImageUrl = null;
        if (!empty($featuredSources) && $featuredSources !== 'none') {
            $imgData = $this->fetchImage($keyword, $featuredSources);
            if ($imgData) $featuredImageUrl = $imgData['url'];
        }

        // --- STEP 4: IN-CONTENT IMAGES INJECTION (Manual) ---
        if ($imageCount > 0 && !empty($inContentSources) && $inContentSources !== 'none') {
            $batchImages = $this->fetchImage($keyword, $inContentSources, $imageCount);
            if (!empty($batchImages) && is_array($batchImages)) {
                $paragraphs = explode('</p>', $content);
                foreach ($batchImages as $i => $imgData) {
                    $imgUrl = $imgData['url'];
                    $credit = $imgData['credit'];
                    
                    $imageTag = '<figure class="my-12 overflow-hidden rounded-3xl bg-slate-50 border border-slate-200 shadow-2xl p-4 transition-all duration-700 hover:shadow-indigo-100">';
                    $imageTag .= '<img src="' . $imgUrl . '" alt="' . htmlspecialchars($keyword) . '" class="w-full h-auto rounded-2xl shadow-sm hover:scale-[1.01] transition-transform duration-500">';
                    if (!empty($credit)) {
                        $imageTag .= '<figcaption class="flex items-center justify-center gap-2 text-[10px] uppercase tracking-widest text-slate-400 mt-5 font-bold italic"><span class="bg-indigo-600 w-1 h-1 rounded-full"></span> IMAGE SOURCE: ' . $credit . '</figcaption>';
                    }
                    $imageTag .= '</figure>';
                    
                    $pos = floor(count($paragraphs) / ($imageCount + 1)) * ($i + 1);
                    $pos = max(1, min($pos, count($paragraphs) - 1));
                    array_splice($paragraphs, $pos, 0, $imageTag);
                }
                $content = implode('</p>', $paragraphs);
            }
        }

        // Final Cleanup
        $content = str_replace('[IMAGE_PLACEHOLDER]', '', $content);
        $content = $this->stripHeadingsBeforeFirstParagraph($content);
        $content = trim($content);

        // --- STEP 5: SAVE POST ---
        $cleanTitle = str_replace(['"', '\''], '', $title);
        $slug = Str::slug($cleanTitle);
        if (Post::where('slug', $slug)->exists()) $slug .= '-' . time();

        $status = $settings['status'] ?? 'published';
        $createdAt = now();

        $post = new Post();
        $post->user_id = $userId ?? auth()->id() ?? 1;
        $post->category_id = $settings['category_id'];
        $post->title = $title;
        $post->slug = $slug;
        $post->summary = $contentData['meta_description'] ?? '';
        $post->content = $content;
        $post->featured_image = $featuredImageUrl;
        $post->status = $status === 'scheduled' ? 'published' : $status; 
        $post->meta_title = $title;
        $post->meta_description = $contentData['meta_description'] ?? '';
        $post->meta_keywords = $contentData['meta_keywords'] ?? '';
        $post->created_at = $createdAt;
        $post->updated_at = $createdAt;
        $post->save();

        return $post;
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
    private function generateContentFromOpenAI($keyword, $language, $imageCount, $articleLength, $apiKey, $generateTitle = 'yes', $aiModel = 'gpt-4o-mini')
    {
        $titleInstruction = $generateTitle === 'no' 
            ? "- 'title': MUST BE EXACTLY THE STRING '{keyword}' without any modifications."
            : "- 'title': A catchy, SEO-friendly title without the year unless necessary. DO NOT use colons (:) in the title. DO NOT start the title with clichés like 'Unlocking', 'Discovering', 'The Secret', or 'Guide'.";

        $defaultPrompt = "Write a VERY DETAILED, professional, SEO-optimized article about '{keyword}' in {language}.\n\nSTRICT REQUIREMENT: The article MUST be approximately {article_length} words long. Use multiple subheadings (h2, h3), detailed paragraphs, and deep analysis to reach this length. DO NOT summarize.\n\nSTRUCTURE:\n- Use <p> for paragraphs.\n- Use <h2> and <h3> for headings.\n- DO NOT use <h1> or horizontal lines (<hr>).\n- DO NOT start with an 'Introduction' heading.\n\nFORMAT:\nReturn ONLY a valid JSON object with these keys:\n$titleInstruction\n- 'meta_description': 150-160 characters.\n- 'meta_keywords': 5-8 SEO keywords.\n- 'content': Full HTML article content.\n\nEEAT Guidelines: Ensure expertise, trust, and a professional journalistic tone. Content should sound like it was written by a human expert, not an AI.";
        
        $promptTemplate = Setting::get('ai_writer_prompt', $defaultPrompt);

        $prompt = str_replace(
            ['{keyword}', '{language}', '{article_length}', '{current_year}'],
            [$keyword, $language, $articleLength, date('Y')],
            $promptTemplate
        );

        // Remove placeholders if they exist in the template since we handle them manually now
        $prompt = str_replace(['{image_instruction}', '{outbound_instruction}'], '', $prompt);

        $prompt .= "\n\nCRITICAL: The 'content' field MUST contain at least $articleLength words of detailed text. This is a hard requirement. Go deep into the topic.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(180)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $aiModel,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a senior SEO editor. You output ONLY valid JSON.'],
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
