<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Services\Marketing\CampaignStatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCampaignStatistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $campaignId)
    {
    }

    public function handle(CampaignStatisticsService $statisticsService): void
    {
        $campaign = MarketingCampaign::with('tests')->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $statisticsService->refresh($campaign);
    }
}
