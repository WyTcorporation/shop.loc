<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Services\Marketing\MarketingAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCampaignAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $campaignId)
    {
    }

    public function handle(MarketingAnalyticsService $analyticsService): void
    {
        $campaign = MarketingCampaign::find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $analyticsService->syncCampaign($campaign);
    }
}
