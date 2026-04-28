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
    protected $signature = 'news:fetch-auto {source_id?}';

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
        $sourceId = $this->argument('source_id');
        $isManual = $sourceId !== null;

        if ($isManual) {
            $sources = AutoNewsSource::where('id', $sourceId)->get();
        } else {
            $sources = AutoNewsSource::where('is_active', true)->get();
        }

        foreach ($sources as $source) {
            // Auto-expire: deactivate if expires_at has passed
            if (!$isManual && $source->expires_at && $source->expires_at->isPast()) {
                $source->is_active = false;
                $source->save();
                $this->info("Source '{$source->name}' has expired and has been deactivated.");
                continue;
            }

            // Check if it's time to run (skip if manual)
            if (!$isManual && $source->last_run_at) {
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
            // Strip tracking/UTM parameters from the article link before any use
            $article['link'] = $this->cleanUrl($article['link']);

            // First, fetch the article content
            $response = Http::timeout(15)->get($article['link']);
            if (!$response->successful()) return false;

            $html = $response->body();
            
            // Extract original featured image (og:image)
            $originalImage = null;
            if (preg_match('/<meta\s+(?:property|name)=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches) || 
                preg_match('/<meta\s+content=["\']([^"\']+)["\']\s+(?:property|name)=["\']og:image["\']/i', $html, $matches)) {
                $originalImage = $matches[1];
            } elseif (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
                $originalImage = $matches[1];
            }

            // Basic extraction
            $text = strip_tags(preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html));
            $text = preg_replace('/\s+/', ' ', $text);
            $text = substr($text, 0, 5000); // Limit context for OpenAI

            $openaiKey = Setting::get('openai_api_key');
            if (!$openaiKey) return false;

            $imageCount = $source->in_content_images_count;
            $imageInstruction = $imageCount > 0 ? "Also, insert the exact text '[IMAGE_PLACEHOLDER]' at appropriate places in the content $imageCount times." : "";

            $defaultPrompt = "Rewrite the following news article to be highly engaging, professional, and unique. Write in the authoritative, objective, and gripping style of a top-tier news agency (like Reuters, AP News, or BBC). The current year is {current_year}, ensure context is up-to-date.\nFollow Google's EEAT guidelines.\nOriginal Title: {title}\nOriginal Context: {context}\n\nFormat the output as a valid JSON object with four keys:\n- 'title': A catchy, unique, journalistic SEO-friendly title without the year unless necessary.\n- 'meta_description': A 150-160 character meta description summarizing the news.\n- 'meta_keywords': A comma-separated string of 5-8 SEO keywords.\n- 'content': The main rewritten news article formatted in HTML (use <p>, <h2>, <h3>). Do NOT start with an 'Introduction' heading. Start the first paragraph directly with a strong journalistic hook (the lead). STRICTLY FORBIDDEN: Do NOT add any 'Source', 'Source:', 'Read more', or attribution links anywhere in the content. Do NOT include <h1> or ```html wrappers.\n{image_instruction}";
            
            $promptTemplate = Setting::get('ai_news_prompt', $defaultPrompt);
            
            $prompt = str_replace(
                ['{title}', '{context}', '{link}', '{image_instruction}', '{current_year}'],
                [$article['title'], $text, $article['link'], $imageInstruction, date('Y')],
                $promptTemplate
            );

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
                $featuredImageCredit = null;
                if ($source->featured_image_source !== 'none') {
                    $imgData = $this->fetchImage($keyword, $source->featured_image_source);
                    if ($imgData) {
                        $featuredImageUrl = $imgData['url'];
                        $featuredImageCredit = $imgData['credit'];
                    }
                } else {
                    $featuredImageUrl = $originalImage;
                }

                // In-Content Images
                $content = $contentData['content'];
                if ($imageCount > 0 && $source->in_content_image_source !== 'none') {
                    for ($i = 0; $i < $imageCount; $i++) {
                        $imgData = $this->fetchImage($keyword . ' part ' . ($i+1), $source->in_content_image_source);
                        if ($imgData) {
                            $imgUrl = $imgData['url'];
                            $credit = $imgData['credit'];
                            if (!empty($credit)) {
                                $imageTag = '<figure class="my-8 overflow-hidden rounded-xl bg-gray-50 border border-gray-100 shadow-sm p-2"><img src="' . $imgUrl . '" alt="' . htmlspecialchars($keyword) . '" class="w-full h-auto rounded-lg"><figcaption class="flex items-center justify-center space-x-2 text-sm text-gray-500 mt-3 pb-1"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg><span>Image Credit: ' . $credit . '</span></figcaption></figure>';
                            } else {
                                $imageTag = '<figure class="my-8 overflow-hidden rounded-xl bg-gray-50 border border-gray-100 shadow-sm p-2"><img src="' . $imgUrl . '" alt="' . htmlspecialchars($keyword) . '" class="w-full h-auto rounded-lg"></figure>';
                            }
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

                // Append a simple direct source link
                $content .= '<p class="text-sm text-gray-500 mt-6 border-t pt-4">Source: <a href="' . $article['link'] . '" target="_blank" rel="nofollow noopener" class="text-blue-600 hover:underline">' . htmlspecialchars($source->name ?? $article['link']) . '</a></p>';


                $content = str_replace('[IMAGE_PLACEHOLDER]', '', $content);

                // Remove any plain AI-generated "Source" links (e.g. <a href="...">Source</a> or <p>Source: <a...)</p>)
                $content = preg_replace('/<p[^>]*>\s*<a[^>]*>\s*Source\s*<\/a>\s*<\/p>/i', '', $content);
                $content = preg_replace('/<a[^>]*>\s*Source\s*<\/a>/i', '', $content);
                $content = preg_replace('/<p[^>]*>\s*Source[:\s]*<\/p>/i', '', $content);
                $content = preg_replace('/\[Source:[^\]]*\]/i', '', $content);
                $content = preg_replace('/\(Source:[^)]*\)/i', '', $content);

                // Strip known intro headings anywhere in the content
                $content = preg_replace('/<h[1-6][^>]*>.*?(Introduction|ভূমিকা|परिचय|Introducción|Overview|Background|সারসংক্ষেপ).*?<\/h[1-6]>/is', '', $content);

                // Strip ALL headings before the very first <p> tag
                $firstPPos = stripos($content, '<p');
                if ($firstPPos !== false) {
                    $before = substr($content, 0, $firstPPos);
                    $after  = substr($content, $firstPPos);
                    $before = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/is', '', $before);
                    $content = trim($before) . $after;
                } else {
                    $content = preg_replace('/^\s*(<h[1-6][^>]*>.*?<\/h[1-6]>\s*)+/is', '', $content);
                }

                $content = trim($content);

                $cleanTitle = str_replace(['"', '\''], '', $contentData['title']);
                $slug = Str::slug($cleanTitle);
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
                $post->summary = $contentData['meta_description'] ?? '';
                $post->content = $content;
                $post->featured_image = $featuredImageUrl;
                $post->status = 'published';
                $post->meta_title = $contentData['title'];
                $post->meta_description = $contentData['meta_description'] ?? '';
                $post->meta_keywords = $contentData['meta_keywords'] ?? '';
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
                    $photographer = $data['photos'][0]['photographer'] ?? 'Pexels';
                    $url = $data['photos'][0]['url'] ?? 'https://www.pexels.com';
                    return ['url' => $data['photos'][0]['src']['large'], 'credit' => "<a href='{$url}' target='_blank' rel='nofollow'>{$photographer} on Pexels</a>"];
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
                    $photographer = $data['results'][0]['user']['name'] ?? 'Unsplash';
                    $url = $data['results'][0]['links']['html'] ?? 'https://unsplash.com';
                    return ['url' => $data['results'][0]['urls']['regular'], 'credit' => "<a href='{$url}' target='_blank' rel='nofollow'>{$photographer} on Unsplash</a>"];
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
                'prompt' => 'A highly realistic, photographic, editorial style news image for: ' . $query . '. Do NOT include any text, words, letters, signatures, or typography in the image. Keep it purely visual and high quality.',
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
                        return ['url' => '/uploads/posts/' . $filename, 'credit' => ""];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Strip tracking/UTM query parameters from a URL, keeping only the clean canonical path.
     * Removes: utm_*, at_medium, at_campaign, at_format, fbclid, gclid, ref, source, etc.
     */
    private function cleanUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return $url;
        }

        $trackingParams = [
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
            'at_medium', 'at_campaign', 'at_format', 'at_custom1', 'at_custom2',
            'fbclid', 'gclid', 'msclkid', 'ref', 'source', 'mc_cid', 'mc_eid',
            'yclid', 'twclid', '_ga', 'igshid',
        ];

        $query = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $query);
            foreach ($trackingParams as $param) {
                unset($query[$param]);
            }
        }

        $clean  = ($parsed['scheme'] ?? 'https') . '://' . $parsed['host'];
        $clean .= $parsed['path'] ?? '/';
        if (!empty($query)) {
            $clean .= '?' . http_build_query($query);
        }
        if (!empty($parsed['fragment'])) {
            $clean .= '#' . $parsed['fragment'];
        }

        return $clean;
    }
}
