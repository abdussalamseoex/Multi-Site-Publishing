<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Models\Setting;
use App\Services\SeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('seo_post_slug_code', 'off');
        // Disable redirecting 404 to home during tests to verify direct responses
        Setting::set('redirect_404_to_home', '0');
    }

    public function test_sitemap_index_endpoint()
    {
        // Create user first to satisfy foreign key constraint
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create sample data
        $category = Category::create(['name' => 'Tech', 'slug' => 'tech']);
        Page::create(['title' => 'About', 'slug' => 'about', 'content' => 'content']);
        Post::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Hello World',
            'slug' => 'hello-world',
            'content' => 'Lorem Ipsum',
            'status' => 'published'
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringStartsWith('text/xml', $response->headers->get('Content-Type'));
        
        $content = $response->getContent();
        $this->assertStringContainsString('<sitemapindex', $content);
        $this->assertStringContainsString('/post-sitemap.xml', $content);
        $this->assertStringContainsString('/page-sitemap.xml', $content);
        $this->assertStringContainsString('/category-sitemap.xml', $content);
    }

    public function test_post_sitemap_renders_posts_and_featured_image()
    {
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create(['name' => 'Tech', 'slug' => 'tech']);
        Post::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Sample Post',
            'slug' => 'sample-post',
            'content' => 'My content',
            'featured_image' => 'https://images.unsplash.com/photo-123',
            'status' => 'published'
        ]);

        $response = $this->get('/post-sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringStartsWith('text/xml', $response->headers->get('Content-Type'));
        
        $content = $response->getContent();
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('/sample-post', $content);
        $this->assertStringContainsString('https://images.unsplash.com/photo-123', $content);
    }

    public function test_page_sitemap_renders_homepage_and_pages()
    {
        Page::create(['title' => 'Contact Us', 'slug' => 'contact-us', 'content' => 'Form']);

        $response = $this->get('/page-sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringStartsWith('text/xml', $response->headers->get('Content-Type'));
        
        $content = $response->getContent();
        $this->assertStringContainsString(url('/'), $content); // Homepage
        $this->assertStringContainsString('/contact-us', $content);
    }

    public function test_custom_sitemap_returns_nodes_when_configured()
    {
        // Returns 404 when not configured
        $this->get('/custom-sitemap.xml')->assertStatus(404);

        // Set custom sitemap nodes
        Setting::set('custom_sitemap_xml', '<url><loc>https://example.com/custom</loc></url>');

        $response = $this->get('/custom-sitemap.xml');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('https://example.com/custom', $content);

        // Main sitemap should list it now
        $responseIndex = $this->get('/sitemap.xml');
        $responseIndex->assertStatus(200);
        $this->assertStringContainsString('/custom-sitemap.xml', $responseIndex->getContent());
    }

    public function test_indexnow_key_verification_endpoint()
    {
        $key = SeoService::getOrCreateIndexNowKey();

        $response = $this->get('/' . $key . '.txt');
        $response->assertStatus(200);
        $this->assertStringStartsWith('text/plain', $response->headers->get('Content-Type'));
        $response->assertSeeText($key);

        // Random keys should fail with 404
        $this->get('/invalidkey.txt')->assertStatus(404);
    }

    public function test_publishing_post_triggers_indexnow_and_sitemap_pings()
    {
        Http::fake();

        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create(['name' => 'News', 'slug' => 'news']);

        Post::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Breaking News',
            'slug' => 'breaking-news',
            'content' => 'Break content',
            'status' => 'published'
        ]);

        // Verify IndexNow ping was sent
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'indexnow') &&
                   $request->method() === 'POST' &&
                   str_contains($request['urlList'][0], '/breaking-news');
        });

        // Verify sitemap ping was sent to Google and Bing
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'google.com/ping') &&
                   str_contains($request->url(), 'sitemap.xml');
        });
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'bing.com/ping') &&
                   str_contains($request->url(), 'sitemap.xml');
        });
    }
}
