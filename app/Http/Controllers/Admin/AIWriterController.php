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
        return view('admin.ai-writer.index', compact('categories'));
    }

    public function settings()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $defaultWriterPrompt = "Write a comprehensive, SEO-optimized, and highly engaging article about '{keyword}' in {language}.\nFollow Google's EEAT (Experience, Expertise, Authoritativeness, Trustworthiness) guidelines.\nFormat the output as a valid JSON object with three keys:\n- 'title': A catchy, SEO-friendly title.\n- 'meta_description': A 150-160 character meta description.\n- 'content': The main article content formatted in HTML (use <h2>, <h3>, <p>, <ul>, <li> tags appropriately. Do NOT include <h1> or ```html wrappers).\n{image_instruction}\nMake the content sound natural, human-written, and provide deep value to the reader. Do not sound like an AI robot.";
        
        $defaultNewsPrompt = "Rewrite the following news article to be unique, SEO-friendly, and highly engaging.\nFollow Google's EEAT guidelines. Make it look like a human journalist wrote it.\nOriginal Title: {title}\nOriginal Context: {context}\n\nFormat the output as a valid JSON object with three keys:\n- 'title': A catchy, unique, SEO-friendly title.\n- 'meta_description': A 150-160 character meta description.\n- 'content': The main rewritten article formatted in HTML (use <h2>, <h3>, <p>, <ul>). Add a small 'Source' link at the very bottom pointing to {link}. Do NOT include <h1> or ```html wrappers.\n{image_instruction}";

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
        ]);

        $openaiKey = Setting::get('openai_api_key');
        if (empty($openaiKey)) {
            return response()->json(['success' => false, 'message' => 'OpenAI API Key is not set in settings.']);
        }

        try {
            $keyword = $request->keyword;
            $language = $request->language;
            
            // 1. Generate Content via OpenAI
            $contentData = $this->generateContentFromOpenAI($keyword, $language, $request->in_content_images_count, $openaiKey);
            if (!$contentData) {
                return response()->json(['success' => false, 'message' => 'Failed to generate content from OpenAI.']);
            }

            // 2. Handle Featured Image
            $featuredImageUrl = null;
            if ($request->featured_image_source !== 'none') {
                $featuredImageUrl = $this->fetchImage($keyword, $request->featured_image_source);
            }

            // 3. Handle In-Content Images
            $content = $contentData['content'];
            if ($request->in_content_images_count > 0 && $request->in_content_image_source !== 'none') {
                for ($i = 0; $i < $request->in_content_images_count; $i++) {
                    $imgUrl = $this->fetchImage($keyword . ' part ' . ($i+1), $request->in_content_image_source);
                    if ($imgUrl) {
                        // Replace placeholder or just append to random paragraphs
                        $imageTag = '<figure><img src="' . $imgUrl . '" alt="' . htmlspecialchars($keyword) . '" class="w-full h-auto rounded-lg shadow-md my-4"></figure>';
                        
                        // Try to replace placeholder if AI added it
                        if (strpos($content, '[IMAGE_PLACEHOLDER]') !== false) {
                            $content = preg_replace('/\[IMAGE_PLACEHOLDER\]/', $imageTag, $content, 1);
                        } else {
                            // Inject after the first or second paragraph
                            $paragraphs = explode('</p>', $content);
                            $insertPos = min($i + 1, count($paragraphs) - 1);
                            array_splice($paragraphs, $insertPos, 0, $imageTag);
                            $content = implode('</p>', $paragraphs);
                        }
                    }
                }
            }

            // Clean up remaining placeholders
            $content = str_replace('[IMAGE_PLACEHOLDER]', '', $content);

            // 4. Save Post
            $slug = Str::slug($contentData['title']);
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
            $post->summary = $contentData['meta_description'];
            $post->content = $content;
            $post->featured_image = $featuredImageUrl;
            $post->status = $status === 'scheduled' ? 'published' : $status; 
            $post->meta_title = $contentData['title'];
            $post->meta_description = $contentData['meta_description'];
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

    private function generateContentFromOpenAI($keyword, $language, $imageCount, $apiKey)
    {
        $imageInstruction = $imageCount > 0 ? "Also, insert the exact text '[IMAGE_PLACEHOLDER]' at appropriate places in the content $imageCount times." : "";

        $defaultPrompt = "Write a comprehensive, SEO-optimized, and highly engaging article about '{keyword}' in {language}.\nFollow Google's EEAT (Experience, Expertise, Authoritativeness, Trustworthiness) guidelines.\nFormat the output as a valid JSON object with three keys:\n- 'title': A catchy, SEO-friendly title.\n- 'meta_description': A 150-160 character meta description.\n- 'content': The main article content formatted in HTML (use <h2>, <h3>, <p>, <ul>, <li> tags appropriately. Do NOT include <h1> or ```html wrappers).\n{image_instruction}\nMake the content sound natural, human-written, and provide deep value to the reader. Do not sound like an AI robot.";
        $promptTemplate = Setting::get('ai_writer_prompt', $defaultPrompt);

        $prompt = str_replace(
            ['{keyword}', '{language}', '{image_instruction}'],
            [$keyword, $language, $imageInstruction],
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
                    return $data['photos'][0]['src']['large'];
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
                    return $data['results'][0]['urls']['regular'];
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
                        return '/uploads/posts/' . $filename;
                    }
                }
            }
        }

        return null;
    }
}
