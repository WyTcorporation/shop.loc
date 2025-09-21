<?php

namespace App\Jobs;

use App\Models\CustomerSegment;
use App\Models\MarketingCampaign;
use App\Services\Marketing\CampaignService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public int $campaignId, public ?int $segmentId = null)
    {
    }

    public function handle(CampaignService $campaignService): void
    {
        $campaign = MarketingCampaign::with(['template', 'segments', 'schedule', 'tests'])
            ->email()
            ->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $segment = $this->segmentId ? CustomerSegment::find($this->segmentId) : null;

        $campaignService->dispatch($campaign, $segment);
    }
}
