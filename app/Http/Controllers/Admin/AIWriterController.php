<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AIWriterController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $recentPosts = Post::orderBy('created_at', 'desc')->take(10)->get();
        return view('admin.ai-writer.index', compact('categories', 'recentPosts'));
    }

    public function settings()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $defaultWriterPrompt = "Write a comprehensive, SEO-optimized, and highly engaging article about '{keyword}' in {language}. The article length should be approximately {article_length} words. The current year is {current_year}, ensure content is up-to-date.\nFollow Google's EEAT (Experience, Expertise, Authoritativeness, Trustworthiness) guidelines.\nFormat the output as a valid JSON object with four keys:\n- 'title': A catchy, SEO-friendly title without the year unless necessary. DO NOT use colons (:) in the title. DO NOT start the title with clichés like 'Unlocking', 'Discovering', 'The Secret', or 'Guide'.\n- 'meta_description': A 150-160 character meta description.\n- 'meta_keywords': A comma-separated string of 5-8 SEO keywords.\n- 'content': The main article content formatted in HTML (use <p> for paragraphs with logical spacing, <h2> for main sections, <h3> for sub-sections. Do NOT use <h1>). Do NOT start the content with an 'Introduction' heading. Start directly with the first paragraph and NO heading above it.\n{image_instruction}\nMake the content sound natural, human-written, and provide deep value to the reader. Do not sound like an AI robot.";
        
        $defaultNewsPrompt = "Rewrite the following news article to be highly engaging, professional, and unique. Write in the authoritative, objective, and gripping style of a top-tier news agency (like Reuters, AP News, or BBC). The current year is {current_year}, ensure context is up-to-date.\nFollow Google's EEAT guidelines.\nOriginal Title: {title}\nOriginal Context: {context}\n\nFormat the output as a valid JSON object with four keys:\n- 'title': A catchy, unique, journalistic SEO-friendly title without the year unless necessary.\n- 'meta_description': A 150-160 character meta description summarizing the news.\n- 'meta_keywords': A comma-separated string of 5-8 SEO keywords.\n- 'content': The main rewritten news article formatted in HTML (use <p>, <h2>, <h3>). Do NOT start with an 'Introduction' heading. Start the first paragraph directly with a strong journalistic hook (the lead). Add a small 'Source' link at the very bottom pointing to {link}. Do NOT include <h1> or ```html wrappers.\n{image_instruction}";

        return view('admin.ai-writer.settings', compact('settings', 'defaultWriterPrompt', 'defaultNewsPrompt'));
    }

    public function storeSettings(Request $request)
    {
        $data = $request->except(['_token']);
        
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('status', 'AI Settings updated successfully.');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'language' => 'required|string',
            'featured_image_source' => 'required|string',
            'in_content_images_count' => 'required|integer|min:0|max:5',
            'in_content_image_source' => 'required|string',
            'status' => 'required|string',
            'schedule_time' => 'nullable|date',
            'article_length' => 'nullable|integer',
        ]);

        $openaiKey = Setting::get('openai_api_key');
        if (empty($openaiKey)) {
            return response()->json(['success' => false, 'message' => 'OpenAI API Key is not set in settings.']);
        }

        try {
            $keyword = $request->keyword;
            $language = $request->language;
            $articleLength = $request->article_length ?? 800;
            
            // 1. Generate Content via OpenAI
            $contentData = $this->generateContentFromOpenAI($keyword, $language, $request->in_content_images_count, $articleLength, $openaiKey, $request->generate_title);
            if (!$contentData) {
                return response()->json(['success' => false, 'message' => 'Failed to generate content from OpenAI.']);
            }

            // 2. Handle Featured Image
            $featuredImageUrl = null;
            $featuredImageCredit = null;
            if ($request->featured_image_source !== 'none') {
                $imgData = $this->fetchImage($keyword, $request->featured_image_source);
                if ($imgData) {
                    $featuredImageUrl = $imgData['url'];
                    $featuredImageCredit = $imgData['credit'];
                }
            }

            // 3. Handle In-Content Images
            $content = $contentData['content'];
            if ($request->in_content_images_count > 0 && $request->in_content_image_source !== 'none') {
                for ($i = 0; $i < $request->in_content_images_count; $i++) {
                    $imgData = $this->fetchImage($keyword . ' part ' . ($i+1), $request->in_content_image_source);
                    if ($imgData) {
                        $imgUrl = $imgData['url'];
                        $credit = $imgData['credit'];
                        
                        $imageTag = '<figure class="my-6"><img src="' . $imgUrl . '" alt="' . htmlspecialchars($keyword) . '" class="w-full h-auto rounded-lg shadow-md"><figcaption class="text-center text-sm text-gray-500 mt-2">Image Source: ' . $credit . '</figcaption></figure>';
                        
                        if (strpos($content, '[IMAGE_PLACEHOLDER]') !== false) {
                            $content = preg_replace('/\[IMAGE_PLACEHOLDER\]/', $imageTag, $content, 1);
                        } else {
                            $paragraphs = explode('</p>', $content);
                            $insertPos = min($i + 1, count($paragraphs) - 1);
                            array_splice($paragraphs, $insertPos, 0, $imageTag);
                            $content = implode('</p>', $paragraphs);
                        }
                    }
                }
            }

            // Append Featured Image Credit if exists at the end of the post
            if ($featuredImageCredit) {
                $content .= '<p class="text-sm text-gray-500 mt-8 border-t pt-4"><em>Featured Image Source: ' . $featuredImageCredit . '</em></p>';
            }

            // Clean up remaining placeholders
            $content = str_replace('[IMAGE_PLACEHOLDER]', '', $content);
            
            // Aggressively remove any heading that contains "Introduction" or its translated equivalents
            $content = preg_replace('/<h[1-6][^>]*>.*?(Introduction|ভূমিকা|परिचय|Introducción|Overview|Background|সারসংক্ষেপ).*?<\/h[1-6]>/is', '', $content);
            
            // Additionally, ensure there is NO heading before the very first paragraph
            $content = preg_replace('/^\s*<h[1-6][^>]*>.*?<\/h[1-6]>\s*/is', '', $content);
            
            $content = trim($content);

            // 4. Save Post
            $cleanTitle = str_replace(['"', '\''], '', $contentData['title']);
            $slug = Str::slug($cleanTitle);
            $existing = Post::where('slug', $slug)->exists();
            if ($existing) {
                $slug = $slug . '-' . time();
            }

            $status = $request->status;
            $createdAt = now();
            if ($status === 'scheduled' && $request->schedule_time) {
                $status = 'published'; // Or 'pending' depending on logic, but typically scheduled implies it'll be published at that time. We'll set created_at to schedule_time.
                $createdAt = \Carbon\Carbon::parse($request->schedule_time);
            }

            $post = new Post();
            $post->user_id = auth()->id();
            $post->category_id = $request->category_id;
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

            return response()->json([
                'success' => true, 
                'message' => 'Successfully generated: ' . $contentData['title'],
                'post_id' => $post->id
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Writer Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function generateContentFromOpenAI($keyword, $language, $imageCount, $articleLength, $apiKey, $generateTitle = 'yes')
    {
        $imageInstruction = $imageCount > 0 ? "Also, insert the exact text '[IMAGE_PLACEHOLDER]' at appropriate places in the content $imageCount times." : "";

        $titleInstruction = $generateTitle === 'no' 
            ? "- 'title': MUST BE EXACTLY THE STRING '{keyword}' without any modifications."
            : "- 'title': A catchy, SEO-friendly title without the year unless necessary. DO NOT use colons (:) in the title. DO NOT start the title with clichés like 'Unlocking', 'Discovering', 'The Secret', or 'Guide'.";

        $defaultPrompt = "Write a comprehensive, SEO-optimized, and highly engaging article about '{keyword}' in {language}. The article length should be approximately {article_length} words. The current year is {current_year}, ensure content is up-to-date.\nFollow Google's EEAT (Experience, Expertise, Authoritativeness, Trustworthiness) guidelines.\nFormat the output as a valid JSON object with four keys:\n$titleInstruction\n- 'meta_description': A 150-160 character meta description.\n- 'meta_keywords': A comma-separated string of 5-8 SEO keywords.\n- 'content': The main article content formatted in HTML (use <p> for paragraphs with logical spacing, <h2> for main sections, <h3> for sub-sections. Do NOT use <h1>). Do NOT start the content with an 'Introduction' heading. Start directly with the first paragraph and NO heading above it.\n{image_instruction}\nMake the content sound natural, human-written, and provide deep value to the reader. Do not sound like an AI robot.";
        $promptTemplate = Setting::get('ai_writer_prompt', $defaultPrompt);

        // If using a custom prompt from DB but generateTitle is 'no', aggressively override the title rule
        if ($generateTitle === 'no') {
            $promptTemplate = preg_replace('/-\s*\'title\':[^\n]+/', "- 'title': MUST BE EXACTLY THE STRING '{keyword}' without any modifications.", $promptTemplate);
        }

        $prompt = str_replace(
            ['{keyword}', '{language}', '{article_length}', '{image_instruction}', '{current_year}'],
            [$keyword, $language, $articleLength, $imageInstruction, date('Y')],
            $promptTemplate
        );

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini', // or gpt-3.5-turbo if 4o-mini is not preferred, but 4o-mini is best cost/performance
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert SEO content writer and subject matter expert.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $contentStr = $result['choices'][0]['message']['content'] ?? '';
            return json_decode($contentStr, true);
        }

        return false;
    }

    private function fetchImage($query, $source)
    {
        if ($source === 'pexels') {
            $pexelsKey = Setting::get('pexels_api_key');
            if (!$pexelsKey) return null;

            $response = Http::withHeaders([
                'Authorization' => $pexelsKey
            ])->get("https://api.pexels.com/v1/search", [
                'query' => $query,
                'per_page' => 1,
                'orientation' => 'landscape'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['photos'][0]['src']['large'])) {
                    $photographer = $data['photos'][0]['photographer'] ?? 'Pexels';
                    $url = $data['photos'][0]['url'] ?? 'https://www.pexels.com';
                    return ['url' => $data['photos'][0]['src']['large'], 'credit' => "<a href='{$url}' target='_blank' rel='nofollow'>{$photographer} on Pexels</a>"];
                }
            }
        } elseif ($source === 'unsplash') {
            $unsplashKey = Setting::get('unsplash_api_key');
            if (!$unsplashKey) return null;

            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $unsplashKey
            ])->get("https://api.unsplash.com/search/photos", [
                'query' => $query,
                'per_page' => 1,
                'orientation' => 'landscape'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['results'][0]['urls']['regular'])) {
                    $photographer = $data['results'][0]['user']['name'] ?? 'Unsplash';
                    $url = $data['results'][0]['links']['html'] ?? 'https://unsplash.com';
                    return ['url' => $data['results'][0]['urls']['regular'], 'credit' => "<a href='{$url}' target='_blank' rel='nofollow'>{$photographer} on Unsplash</a>"];
                }
            }
        } elseif ($source === 'dalle') {
            $openaiKey = Setting::get('openai_api_key');
            if (!$openaiKey) return null;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openaiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/images/generations', [
                'model' => 'dall-e-3',
                'prompt' => 'A realistic, high-quality editorial blog image for the topic: ' . $query,
                'n' => 1,
                'size' => '1024x1024'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'][0]['url'])) {
                    // DALL-E returns a temporary URL. We should ideally download it.
                    // For now, downloading it to public/uploads/posts
                    $tempUrl = $data['data'][0]['url'];
                    $imageContent = file_get_contents($tempUrl);
                    if ($imageContent) {
                        $filename = 'ai_img_' . time() . '_' . uniqid() . '.jpg';
                        $path = public_path('uploads/posts/' . $filename);
                        if (!file_exists(public_path('uploads/posts'))) {
                            mkdir(public_path('uploads/posts'), 0777, true);
                        }
                        file_put_contents($path, $imageContent);
                        return ['url' => '/uploads/posts/' . $filename, 'credit' => "AI Generated by DALL-E 3"];
                    }
                }
            }
        }

        return null;
    }
}
