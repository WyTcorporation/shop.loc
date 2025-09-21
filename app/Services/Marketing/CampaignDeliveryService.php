<?php

namespace App\Services\Marketing;

use App\Models\CustomerSegment;
use App\Models\MarketingCampaign;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CampaignDeliveryService
{
    public function __construct(
        private readonly SegmentService $segmentService,
        private readonly TemplateService $templateService,
        private readonly CampaignStatisticsService $statisticsService,
    ) {
    }

    public function sendEmailCampaign(MarketingCampaign $campaign, ?CustomerSegment $segment = null): void
    {
        $recipients = $this->segmentService->resolveRecipients($campaign, $segment);

        foreach ($recipients as $recipient) {
            if (! $recipient->email) {
                continue;
            }

            $data = ['user' => $recipient, 'campaign' => $campaign];
            $subject = $this->templateService->renderSubject($campaign, data: $data);
            $content = $this->templateService->renderContent($campaign, data: $data);

            Mail::raw($content, function (Message $message) use ($recipient, $subject): void {
                $message->to($recipient->email, $recipient->name ?? null);
                $message->subject($subject);
            });

            $this->statisticsService->recordOpen($campaign);
        }
    }

    public function sendPushCampaign(MarketingCampaign $campaign, ?CustomerSegment $segment = null): void
    {
        $recipients = $this->segmentService->resolveRecipients($campaign, $segment);

        foreach ($recipients as $recipient) {
            Log::info('Dispatching push notification', [
                'campaign_id' => $campaign->getKey(),
                'user_id' => $recipient->getKey(),
            ]);

            $this->statisticsService->recordClick($campaign);
        }
    }
}
