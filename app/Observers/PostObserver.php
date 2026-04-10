<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PostObserver
{
    /**
     * Handle the Post "saved" event.
     */
    public function saved(Post $post): void
    {
        // Check if the Post just became published
        if ($post->isDirty('status') && $post->status === 'published') {
            $this->pingGoogleIndexingAPI($post);
        }
    }

    /**
     * Simulated Google Indexing API logic
     */
    protected function pingGoogleIndexingAPI(Post $post)
    {
        // In a real application, you'd use Google Client Library with the JSON key.
        // For standard SEO setup, pinging directly or utilizing a queue is common.
        Log::info("Google Indexing API: Re-crawling requested for: " . route('frontend.post', $post->slug));
        
        // Example structure for actual usage:
        /*
        $endpoint = "https://indexing.googleapis.com/v3/urlNotifications:publish";
        Http::withToken($googleAccessToken)->post($endpoint, [
            'url' => route('frontend.post', $post->slug),
            'type' => 'URL_UPDATED'
        ]);
        */
    }
}
