<?php

namespace App\Services\Marketing;

use App\Jobs\SyncCampaignAnalytics;
use App\Jobs\UpdateCampaignStatistics;
use App\Models\CustomerSegment;
use App\Models\MarketingCampaign;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Date;

class CampaignService
{
    public function __construct(
        private readonly CampaignDeliveryService $deliveryService,
        private readonly CampaignScheduleService $scheduleService,
        private readonly CampaignStatisticsService $statisticsService,
    ) {
    }

    public function dispatch(MarketingCampaign $campaign, ?CustomerSegment $segment = null): void
    {
        if ($campaign->type === MarketingCampaign::TYPE_EMAIL) {
            $this->deliveryService->sendEmailCampaign($campaign, $segment);
        } else {
            $this->deliveryService->sendPushCampaign($campaign, $segment);
        }

        $campaign->last_dispatched_at = Date::now();
        $campaign->save();

        $this->scheduleService->markDispatched($campaign);

        Bus::chain([
            new UpdateCampaignStatistics($campaign->getKey()),
            new SyncCampaignAnalytics($campaign->getKey()),
        ])->dispatch();
    }

    public function schedule(MarketingCampaign $campaign): void
    {
        $this->scheduleService->ensureNextRun($campaign);
    }

    public function recordMetric(MarketingCampaign $campaign, string $metric, int $value = 1): void
    {
        match ($metric) {
            'open' => $this->statisticsService->recordOpen($campaign, $value),
            'click' => $this->statisticsService->recordClick($campaign, $value),
            'conversion' => $this->statisticsService->recordConversion($campaign, $value),
            default => null,
        };
    }
}
