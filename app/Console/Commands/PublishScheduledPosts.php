<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled posts whose scheduled_at timestamp has arrived';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $publishedCount = Post::autoPublishDuePosts();

        if ($publishedCount > 0) {
            $this->info("Successfully published {$publishedCount} scheduled post(s).");
        } else {
            $this->line('No scheduled posts due for publishing.');
        }

        return Command::SUCCESS;
    }
}
