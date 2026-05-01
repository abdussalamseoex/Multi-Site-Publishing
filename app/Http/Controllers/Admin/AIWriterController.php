<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\AiBulkCampaign;
use App\Models\User;
use App\Services\AIContentService;
use Illuminate\Support\Str;

class AIWriterController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $users = User::all();
        $recentPosts = Post::orderBy('created_at', 'desc')->take(10)->get();
        return view('admin.ai-writer.index', compact('categories', 'recentPosts', 'users'));
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

    public function generate(Request $request, AIContentService $aiService)
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
            'generate_title' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $post = $aiService->generatePost($request->keyword, $request->all(), $request->user_id);

            return response()->json([
                'success' => true, 
                'message' => 'Successfully generated: ' . $post->title,
                'post_id' => $post->id
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Writer Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function startBulkCampaign(Request $request)
    {
        $request->validate([
            'keywords' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'language' => 'required|string',
            'featured_image_source' => 'required|string',
            'in_content_images_count' => 'required|integer|min:0|max:5',
            'in_content_image_source' => 'required|string',
            'status' => 'required|string',
            'schedule_interval' => 'required|integer|min:1',
            'article_length' => 'nullable|integer',
            'generate_title' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $keywords = array_filter(array_map('trim', explode("\n", $request->keywords)));
        
        if (empty($keywords)) {
            return response()->json(['success' => false, 'message' => 'No valid keywords provided.']);
        }

        $campaign = AiBulkCampaign::create([
            'user_id' => $request->user_id,
            'category_id' => $request->category_id,
            'name' => 'Bulk: ' . $keywords[0] . (count($keywords) > 1 ? ' + ' . (count($keywords) - 1) . ' others' : ''),
            'keywords' => $keywords,
            'total_count' => count($keywords),
            'processed_count' => 0,
            'interval_minutes' => $request->schedule_interval,
            'status' => 'pending',
            'next_run_at' => now(), // Start immediately
            'settings' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bulk campaign started successfully. You can monitor progress below.',
            'campaign_id' => $campaign->id
        ]);
    }

    public function campaigns()
    {
        $campaigns = AiBulkCampaign::with(['category', 'user'])->latest()->paginate(10);
        return response()->json($campaigns);
    }
}
