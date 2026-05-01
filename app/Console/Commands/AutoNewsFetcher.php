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
                $interval = $source->fetch_interval_hours;
                $limit    = $source->posts_per_run;

                if ($source->use_smart_schedule && $source->daily_post_limit > 0) {
                    $interval = 24 / $source->daily_post_limit;
                    $limit    = 1; // In smart mode, we usually fetch one at a time to spread them out
                }

                $minutesSinceLastRun = $source->last_run_at->diffInMinutes(now());
                if ($minutesSinceLastRun < ($interval * 60)) {
                    continue; // Skip, not time yet
                }
            }

            $this->info("Processing source: " . $source->name);

            // Fetch RSS or HTML
            $limit = $source->posts_per_run;
            if ($source->use_smart_schedule && $source->daily_post_limit > 0) {
                $limit = 1;
            }
            $articlesToProcess = $this->extractLinksFromSource($source->source_url, $limit);
            
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

            if ($successCount == 0) {
                $this->warn("Completed {$source->name}: ran successfully but generated 0 new posts (could be duplicates or errors). Check laravel.log.");
            } else {
                $this->info("Completed {$source->name}: generated {$successCount} posts.");
            }
        }
    }

    private function extractLinksFromSource($url, $limit)
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(20)->get($url);
            
            if (!$response->successful()) {
                \Log::warning("AutoNewsFetcher: Failed to fetch source URL {$url}. Status: " . $response->status());
                return [];
            }

            $content = $response->body();
            $articles = [];

            \Log::info("AutoNewsFetcher: Fetched " . strlen($content) . " bytes from " . $url);

            // --- 1. Sitemap Support ---
            if (str_contains($url, 'sitemap') || stripos($content, '<urlset') !== false) {
                $xml = @simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
                if ($xml && (isset($xml->url) || isset($xml->sitemap))) {
                    $nodes = isset($xml->url) ? $xml->url : $xml->sitemap;
                    foreach ($nodes as $node) {
                        if (count($articles) >= $limit) break;
                        $loc = (string)$node->loc;
                        if (filter_var($loc, FILTER_VALIDATE_URL) && !str_ends_with($loc, '.xml')) {
                            $slug = basename($loc);
                            if (strpos($slug, '.') !== false) $slug = substr($slug, 0, strrpos($slug, '.'));
                            $title = ucwords(str_replace(['-', '_'], ' ', $slug));
                            
                            $articles[] = [
                                'title'       => $title,
                                'link'        => $loc,
                                'description' => '',
                                'image'       => null,
                            ];
                        }
                    }
                    return $articles;
                }
            }

            // --- 2. RSS/Atom Support ---
            $isRss = (stripos($content, '<rss') !== false || stripos($content, '<feed') !== false);
            if ($isRss) {
                $xml = @simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
                if ($xml) {
                    $namespaces = $xml->getNamespaces(true);
                    if (isset($xml->channel->item)) {
                        foreach ($xml->channel->item as $item) {
                            if (count($articles) >= $limit) break;
                            $pubDateStr = (string)$item->pubDate;
                            if (!empty($pubDateStr)) {
                                try {
                                    $pubDate = \Carbon\Carbon::parse($pubDateStr);
                                    if ($pubDate->diffInDays(now()) > 30) continue;
                                } catch (\Exception $e) { }
                            }
                            $link = trim((string)$item->link);
                            if (empty($link) || !filter_var($link, FILTER_VALIDATE_URL)) continue;
                            
                            $desc = '';
                            if (isset($namespaces['content'])) {
                                $contentNs = $item->children($namespaces['content']);
                                $desc = isset($contentNs->encoded) ? (string)$contentNs->encoded : '';
                            }
                            if (empty($desc)) $desc = (string)$item->description;

                            $image = null;
                            if (isset($namespaces['media'])) {
                                $mediaNs = $item->children($namespaces['media']);
                                foreach (['content', 'thumbnail'] as $tag) {
                                    if (isset($mediaNs->$tag)) {
                                        foreach ($mediaNs->$tag as $node) {
                                            $imgUrl = (string)($node['url'] ?? '');
                                            if (!empty($imgUrl)) { $image = $imgUrl; break 2; }
                                        }
                                    }
                                }
                            }
                            if (!$image && isset($item->enclosure)) {
                                foreach ($item->enclosure as $enclosure) {
                                    $imgUrl = (string)($enclosure['url'] ?? '');
                                    if (!empty($imgUrl)) { $image = $imgUrl; break; }
                                }
                            }

                            $articles[] = [
                                'title'       => (string)$item->title,
                                'link'        => $link,
                                'description' => strip_tags($desc),
                                'image'       => $image,
                                'pub_date'    => $pubDateStr,
                            ];
                        }
                    } elseif (isset($xml->entry)) {
                        foreach ($xml->entry as $entry) {
                            if (count($articles) >= $limit) break;
                            $pubDateStr = isset($entry->published) ? (string)$entry->published : (isset($entry->updated) ? (string)$entry->updated : '');
                            if (!empty($pubDateStr)) {
                                try {
                                    $pubDate = \Carbon\Carbon::parse($pubDateStr);
                                    if ($pubDate->diffInDays(now()) > 30) continue;
                                } catch (\Exception $e) { }
                            }
                            $link = '';
                            foreach ($entry->link as $l) {
                                if ((string)$l['rel'] == 'alternate' || !(string)$l['rel']) {
                                    $link = (string)$l['href']; break;
                                }
                            }
                            if (!$link) $link = (string)$entry->link['href'];
                            if (empty($link) || !filter_var($link, FILTER_VALIDATE_URL)) continue;

                            $desc = '';
                            if (isset($namespaces['content'])) {
                                $contentNs = $entry->children($namespaces['content']);
                                $desc = isset($contentNs->encoded) ? (string)$contentNs->encoded : '';
                            }
                            if (empty($desc)) $desc = isset($entry->summary) ? (string)$entry->summary : '';

                            $articles[] = [
                                'title'       => (string)$entry->title,
                                'link'        => $link,
                                'description' => strip_tags($desc),
                                'image'       => null,
                                'pub_date'    => $pubDateStr,
                            ];
                        }
                    }
                }
                return $articles;
            }

            // --- 3. HTML/DOM Support (Robust for complex sites) ---
            $dom = new \DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $xpath = new \DOMXPath($dom);
            $links = $xpath->query('//a[@href]');
            
            \Log::info("AutoNewsFetcher: Found " . $links->length . " links in HTML for " . $url);
            
            $parsedUrl = parse_url($url);
            $baseUrl   = ($parsedUrl['scheme'] ?? 'http') . '://' . ($parsedUrl['host'] ?? '');

            foreach ($links as $linkNode) {
                if (count($articles) >= $limit) break;

                $href  = trim($linkNode->getAttribute('href'));
                $title = trim($linkNode->textContent);

                if (strlen($title) < 10) {
                    $title = $linkNode->getAttribute('title') ?: $linkNode->getAttribute('aria-label');
                }
                if (strlen($title) < 10) {
                    $imgs = $linkNode->getElementsByTagName('img');
                    if ($imgs->length > 0) $title = $imgs->item(0)->getAttribute('alt');
                }

                if (empty($href) || strlen($title) < 15) continue;

                // Resolve Relative URLs
                if (strpos($href, 'http') !== 0) {
                    if (strpos($href, '//') === 0) {
                        $href = ($parsedUrl['scheme'] ?? 'http') . ':' . $href;
                    } elseif (strpos($href, '/') === 0) {
                        $href = rtrim($baseUrl, '/') . '/' . ltrim($href, '/');
                    } else {
                        $href = rtrim($url, '/') . '/' . ltrim($href, '/');
                    }
                }

                // Filtering for news articles
                $isArticle = (str_contains($href, '/202') || str_contains($href, '/article/') || str_contains($href, '/news/') || str_contains($href, '/entertainment/'));
                if ($isArticle && filter_var($href, FILTER_VALIDATE_URL) && !in_array($href, array_column($articles, 'link'))) {
                    $articles[] = [
                        'title'       => trim($title),
                        'link'        => $href,
                        'description' => '',
                        'image'       => null,
                    ];
                }
            }

            if (empty($articles)) {
                \Log::info("AutoNewsFetcher: No valid articles found for " . $url);
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

            // Duplicate Check: Check if we already posted this exact article link
            if (Post::where('content', 'LIKE', '%' . $article['link'] . '%')->exists()) {
                \Log::info("AutoNewsFetcher: Skipping duplicate article. Already posted: " . $article['link']);
                return false;
            }

            // -----------------------------------------------------------------------
            // STRATEGY: Always attempt to fetch the article page with browser-like
            // headers (Google referrer tricks many paywalls). Extract og:image and
            // og:description from <head> (available even on paywalled pages).
            // Combine ALL available text: og:description + RSS desc + body text.
            // -----------------------------------------------------------------------
            $rssDescription = trim($article['description'] ?? '');
            $originalImage   = $article['image'] ?? null; // Start with RSS media image
            $html            = '';
            $text            = '';

            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Referer'         => 'https://www.google.com/',
                    'DNT'             => '1',
                    'Connection'      => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                ])->timeout(20)->get($article['link']);

                if ($response->successful()) {
                    $html = $response->body();

                    // --- Extract og:image (works even on paywalled pages) ---
                    if (!$originalImage) {
                        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m) ||
                            preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $m) ||
                            preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m) ||
                            preg_match('/<meta[^>]+property=["\']og:image:url["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m) ||
                            preg_match('/<meta[^>]+name=["\']image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m) ||
                            preg_match('/<meta[^>]+itemprop=["\']image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m) ||
                            preg_match('/<link[^>]+rel=["\']image_src["\'][^>]+href=["\']([^"\']+)["\']/i', $html, $m)) {
                            $originalImage = $m[1];
                        }
                    }

                    // --- Extract og:description (richer than RSS description) ---
                    $ogDescription = '';
                    if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m) ||
                        preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:description["\']/i', $html, $m) ||
                        preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                        $ogDescription = trim($m[1]);
                    }

                    // --- Extract body text (strips scripts, styles, nav) ---
                    $bodyText = $html;
                    $bodyText = preg_replace('#<(script|style|nav|header|footer|aside|form|iframe)[^>]*>.*?</\1>#is', '', $bodyText);
                    $bodyText = strip_tags($bodyText);
                    $bodyText = preg_replace('/\s+/', ' ', $bodyText);
                    $bodyText = trim($bodyText);

                    // Build best possible context: og:description + RSS desc + body
                    $contextParts = array_filter([
                        $ogDescription,
                        $rssDescription,
                        strlen($bodyText) > 200 ? $bodyText : '',
                    ]);
                    $text = implode(' ', $contextParts);
                    $text = preg_replace('/\s+/', ' ', $text);

                } else {
                    \Log::info("AutoNewsFetcher: Page fetch returned {$response->status()} for {$article['link']}, using RSS description as fallback.");
                    $text = $rssDescription;
                }
            } catch (\Exception $fetchEx) {
                \Log::info("AutoNewsFetcher: Page fetch exception for {$article['link']}: " . $fetchEx->getMessage() . ". Using RSS description.");
                $text = $rssDescription;
            }

            // Final fallback
            if (empty(trim($text))) {
                $text = $article['title'];
            }

            $text = substr($text, 0, 6000);

            $openaiKey = Setting::get('openai_api_key');
            if (!$openaiKey) {
                \Log::error("AutoNewsFetcher: OpenAI API Key is missing.");
                return false;
            }

            $imageCount = $source->in_content_images_count;
            $imageInstruction = $imageCount > 0 ? "Also, insert the exact text '[IMAGE_PLACEHOLDER]' at appropriate places in the content $imageCount times." : "";

            $defaultPrompt = "You are an expert news journalist. Rewrite the following news article in a professional, engaging, and detailed style (like Reuters or BBC). The current year is {current_year}.\n\nIMPORTANT RULES:\n1. TITLE: Keep the title faithful to the original news. You may improve it slightly for SEO clarity but NEVER change the topic or meaning. Do NOT invent a new unrelated title.\n2. CONTENT: The article content MUST be based strictly on the facts and information from the original article. Do NOT add unrelated information or make up facts. Expand naturally using the provided context.\n3. Write in a journalistic style with a strong opening paragraph (the lead), followed by detailed body paragraphs.\n4. Follow Google's EEAT guidelines.\n\nOriginal Title: {title}\nOriginal Article Context: {context}\n\nFormat the output as a valid JSON object with four keys:\n- 'title': The article title — keep it faithful to the original topic (minor SEO improvements allowed).\n- 'meta_description': A 150-160 character meta description summarizing the news.\n- 'meta_keywords': A comma-separated string of 5-8 SEO keywords relevant to this specific article.\n- 'content': The full rewritten article in HTML (use <p>, <h2>, <h3> tags). Start directly with a strong lead paragraph. Do NOT use an 'Introduction' heading. Do NOT add 'Source:', 'Read more', or any attribution links. Do NOT include <h1> tags or ```html wrappers.\n{image_instruction}";
            
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
                
                // Clean up any potential markdown formatting in case the model ignored response_format
                $contentStr = preg_replace('/^```json\s*/', '', $contentStr);
                $contentStr = preg_replace('/```$/', '', trim($contentStr));
                
                $contentData = json_decode($contentStr, true);

                if (!$contentData) {
                    \Log::error("AutoNewsFetcher: Failed to decode OpenAI JSON response. Raw output: " . $contentStr);
                    return false;
                }

                if (empty($contentData['title']) || empty($contentData['content'])) {
                    \Log::error("AutoNewsFetcher: OpenAI response missing 'title' or 'content'. Data: " . json_encode($contentData));
                    return false;
                }

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
                }

                // Fallback to original image if stock fetch failed or was 'none'
                if (!$featuredImageUrl) {
                    $featuredImageUrl = $originalImage;
                }

                // In-Content Images
                $content = $contentData['content'];
                if ($imageCount > 0 && $source->in_content_image_source !== 'none') {
                    $batchImages = $this->fetchImage($keyword, $source->in_content_image_source, $imageCount);
                    
                    if (!empty($batchImages) && is_array($batchImages)) {
                        foreach ($batchImages as $i => $imgData) {
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
                if ($source->user_id) {
                    $post->user_id = $source->user_id;
                } else {
                    // Find admin user or use first user
                    $admin = \App\Models\User::where('role', 'admin')->first();
                    $post->user_id = $admin ? $admin->id : 1;
                }
                $post->category_id = $source->category_id;
                $post->auto_news_source_id = $source->id;
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
            } else {
                \Log::error("AutoNewsFetcher: OpenAI API Request Failed. Status: " . $aiResponse->status() . " Body: " . $aiResponse->body());
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("AutoNewsFetcher processArticle Error: " . $e->getMessage());
            return false;
        }
    }

    private function fetchImage($query, $sourceName, $count = 1)
    {
        $images = [];

        if ($sourceName === 'pexels') {
            $pexelsKey = Setting::get('pexels_api_key');
            if (!$pexelsKey) return null;

            $response = Http::withHeaders([
                'Authorization' => $pexelsKey
            ])->get("https://api.pexels.com/v1/search", [
                'query' => $query,
                'per_page' => $count,
                'orientation' => 'landscape'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                foreach ($data['photos'] ?? [] as $photo) {
                    $photographer = $photo['photographer'] ?? 'Pexels';
                    $url = $photo['url'] ?? 'https://www.pexels.com';
                    $images[] = ['url' => $photo['src']['large'], 'credit' => "<a href='{$url}' target='_blank' rel='nofollow'>{$photographer} on Pexels</a>"];
                }
            }
        } elseif ($sourceName === 'unsplash') {
            $unsplashKey = Setting::get('unsplash_api_key');
            if (!$unsplashKey) return null;

            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $unsplashKey
            ])->get("https://api.unsplash.com/search/photos", [
                'query' => $query,
                'per_page' => $count,
                'orientation' => 'landscape'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                foreach ($data['results'] ?? [] as $result) {
                    $photographer = $result['user']['name'] ?? 'Unsplash';
                    $url = $result['links']['html'] ?? 'https://unsplash.com';
                    $images[] = ['url' => $result['urls']['regular'], 'credit' => "<a href='{$url}' target='_blank' rel='nofollow'>{$photographer} on Unsplash</a>"];
                }
            }
        } elseif ($sourceName === 'dalle') {
            for ($i = 0; $i < $count; $i++) {
                $openaiKey = Setting::get('openai_api_key');
                if (!$openaiKey) break;

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
                        $imageContent = @file_get_contents($tempUrl);
                        if ($imageContent) {
                            $filename = 'auto_img_' . time() . '_' . uniqid() . '.jpg';
                            $path = public_path('uploads/posts/' . $filename);
                            if (!file_exists(public_path('uploads/posts'))) {
                                mkdir(public_path('uploads/posts'), 0777, true);
                            }
                            file_put_contents($path, $imageContent);
                            $images[] = ['url' => '/uploads/posts/' . $filename, 'credit' => ""];
                        }
                    }
                }
            }
        }

        return $count === 1 ? ($images[0] ?? null) : $images;
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
