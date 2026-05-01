<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiBulkCampaign;
use App\Services\AIContentService;
use Carbon\Carbon;

class AiBulkProcessor extends Command
{
    protected $signature = 'ai:bulk-process';
    protected $description = 'Process pending AI bulk writing campaigns';

    public function handle(AIContentService $aiService)
    {
        // Get campaigns that are pending or processing and are due for the next run
        $campaigns = AiBulkCampaign::whereIn('status', ['pending', 'processing'])
            ->where('next_run_at', '<=', now())
            ->get();

        foreach ($campaigns as $campaign) {
            $this->processCampaign($campaign, $aiService);
        }
    }

    private function processCampaign(AiBulkCampaign $campaign, AIContentService $aiService)
    {
        $keywords = $campaign->keywords;
        $processedCount = $campaign->processed_count;

        if ($processedCount >= count($keywords)) {
            $campaign->update(['status' => 'completed']);
            return;
        }

        $keyword = $keywords[$processedCount];
        
        $campaign->update(['status' => 'processing', 'last_run_at' => now()]);

        try {
            $this->info("Processing keyword: {$keyword} for campaign ID: {$campaign->id}");
            
            $aiService->generatePost($keyword, $campaign->settings, $campaign->user_id);

            $newCount = $processedCount + 1;
            $status = ($newCount >= count($keywords)) ? 'completed' : 'processing';
            
            $campaign->update([
                'processed_count' => $newCount,
                'status' => $status,
                'next_run_at' => ($status === 'processing') ? now()->addMinutes($campaign->interval_minutes) : null,
            ]);

            $this->info("Successfully processed: {$keyword}");

        } catch (\Exception $e) {
            $this->error("Failed to process keyword: {$keyword}. Error: " . $e->getMessage());
            
            $errorLog = $campaign->error_log ? $campaign->error_log . "\n" : "";
            $errorLog .= "[" . now()->toDateTimeString() . "] Keyword: {$keyword} - Error: " . $e->getMessage();

            $campaign->update([
                'error_log' => $errorLog,
                'status' => 'failed', // Stop the campaign on failure? Or just skip? 
                // Let's stop it for safety, user can resume later if we add that logic.
            ]);
        }
    }
}
