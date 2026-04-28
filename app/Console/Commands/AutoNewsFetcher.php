<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AutoNewsSource;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AutoNewsFetcher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch-auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from active auto news sources and generate AI articles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sources = AutoNewsSource::where('is_active', true)->get();

        foreach ($sources as $source) {
            // Check if it's time to run
            if ($source->last_run_at) {
                $hoursSinceLastRun = $source->last_run_at->diffInHours(now());
                if ($hoursSinceLastRun < $source->fetch_interval_hours) {
                    continue; // Skip, not time yet
                }
            }

            $this->info("Processing source: " . $source->name);

            // Fetch RSS or HTML
            $articlesToProcess = $this->extractLinksFromSource($source->source_url, $source->posts_per_run);
            
            if (empty($articlesToProcess)) {
                $this->error("No articles found for source: " . $source->name);
                continue;
            }

            $successCount = 0;
            foreach ($articlesToProcess as $article) {
                $success = $this->processArticle($article, $source);
                if ($success) {
                    $successCount++;
                }
            }

            $source->last_run_at = now();
            $source->save();

            $this->info("Completed {$source->name}: generated {$successCount} posts.");
        }
    }

    private function extractLinksFromSource($url, $limit)
    {
        try {
            $response = Http::timeout(15)->get($url);
            if (!$response->successful()) return [];

            $content = $response->body();
            $articles = [];

            // Check if RSS
            if (strpos($content, '<rss') !== false || strpos($content, '<feed') !== false) {
                $xml = @simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
                if ($xml) {
                    // RSS 2.0
                    if (isset($xml->channel->item)) {
                        foreach ($xml->channel->item as $item) {
                            if (count($articles) >= $limit) break;
                            $articles[] = [
                                'title' => (string)$item->title,
                                'link' => (string)$item->link,
                            ];
                        }
                    } 
                    // Atom
                    elseif (isset($xml->entry)) {
                        foreach ($xml->entry as $entry) {
                            if (count($articles) >= $limit) break;
                            $link = '';
                            foreach ($entry->link as $l) {
                                if ((string)$l['rel'] == 'alternate' || !(string)$l['rel']) {
                                    $link = (string)$l['href'];
                                    break;
                                }
                            }
                            if (!$link) $link = (string)$entry->link['href'];

                            $articles[] = [
                                'title' => (string)$entry->title,
                                'link' => $link,
                            ];
                        }
                    }
                }
            } else {
                // Not an RSS feed, attempt to find links using regex (very basic)
                preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si', $content, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $idx => $link) {
                        if (count($articles) >= $limit) break;
                        $title = strip_tags($matches[2][$idx]);
                        // Basic filter for valid article links
                        if (strlen($title) > 20 && filter_var($link, FILTER_VALIDATE_URL)) {
                            $articles[] = [
                                'title' => trim($title),
                                'link' => $link
                            ];
                        }
                    }
                }
            }

            return $articles;
        } catch (\Exception $e) {
            \Log::error("AutoNewsFetcher extractLinksFromSource Error: " . $e->getMessage());
            return [];
        }
    }

    private function processArticle($article, $source)
    {
        try {
            // First, fetch the article content
            $response = Http::timeout(15)->get($article['link']);
            if (!$response->successful()) return false;

            $html = $response->body();
            // Basic extraction (can be improved with DOMDocument or Readability)
            $text = strip_tags(preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html));
            $text = preg_replace('/\s+/', ' ', $text);
            $text = substr($text, 0, 5000); // Limit context for OpenAI

            $openaiKey = Setting::get('openai_api_key');
            if (!$openaiKey) return false;

            $imageCount = $source->in_content_images_count;
            $imageInstruction = $imageCount > 0 ? "Also, insert the exact text '[IMAGE_PLACEHOLDER]' at appropriate places in the content $imageCount times." : "";

            $prompt = "Rewrite the following news article to be unique, SEO-friendly, and highly engaging. 
Follow Google's EEAT guidelines. Make it look like a human journalist wrote it.
Original Title: {$article['title']}
Original Context: {$text}

Format the output as a valid JSON object with three keys:
- 'title': A catchy, unique, SEO-friendly title.
- 'meta_description': A 150-160 character meta description.
- 'content': The main rewritten article formatted in HTML (use <h2>, <h3>, <p>, <ul>). Add a small 'Source' link at the very bottom pointing to {$article['link']}. Do NOT include <h1> or ```html wrappers.
$imageInstruction";

            $aiResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openaiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert news journalist and SEO content writer.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($aiResponse->successful()) {
                $result = $aiResponse->json();
                $contentStr = $result['choices'][0]['message']['content'] ?? '';
                $contentData = json_decode($contentStr, true);

                if (!$contentData) return false;

                $keyword = $contentData['title'];
                
                // Featured Image
                $featuredImageUrl = null;
                if ($source->featured_image_source !== 'none') {
                    $featuredImageUrl = $this->fetchImage($keyword, $source->featured_image_source);
                }

                // In-Content Images
                $content = $contentData['content'];
                if ($imageCount > 0 && $source->in_content_image_source !== 'none') {
                    for ($i = 0; $i < $imageCount; $i++) {
                        $imgUrl = $this->fetchImage($keyword . ' part ' . ($i+1), $source->in_content_image_source);
                        if ($imgUrl) {
                            $imageTag = '<figure><img src="' . $imgUrl . '" alt="' . htmlspecialchars($keyword) . '" class="w-full h-auto rounded-lg shadow-md my-4"></figure>';
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

                $content = str_replace('[IMAGE_PLACEHOLDER]', '', $content);

                $slug = Str::slug($contentData['title']);
                if (Post::where('slug', $slug)->exists()) {
                    $slug = $slug . '-' . time();
                }

                $post = new Post();
                // Find admin user or use first user
                $admin = \App\Models\User::where('role', 'admin')->first();
                $post->user_id = $admin ? $admin->id : 1;
                $post->category_id = $source->category_id;
                $post->title = $contentData['title'];
                $post->slug = $slug;
                $post->summary = $contentData['meta_description'];
                $post->content = $content;
                $post->featured_image = $featuredImageUrl;
                $post->status = 'published';
                $post->meta_title = $contentData['title'];
                $post->meta_description = $contentData['meta_description'];
                $post->save();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("AutoNewsFetcher processArticle Error: " . $e->getMessage());
            return false;
        }
    }

    private function fetchImage($query, $sourceName)
    {
        if ($sourceName === 'pexels') {
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
        } elseif ($sourceName === 'unsplash') {
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
        } elseif ($sourceName === 'dalle') {
            $openaiKey = Setting::get('openai_api_key');
            if (!$openaiKey) return null;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openaiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/images/generations', [
                'model' => 'dall-e-3',
                'prompt' => 'A realistic, high-quality editorial news image for: ' . $query,
                'n' => 1,
                'size' => '1024x1024'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'][0]['url'])) {
                    $tempUrl = $data['data'][0]['url'];
                    $imageContent = file_get_contents($tempUrl);
                    if ($imageContent) {
                        $filename = 'auto_img_' . time() . '_' . uniqid() . '.jpg';
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
