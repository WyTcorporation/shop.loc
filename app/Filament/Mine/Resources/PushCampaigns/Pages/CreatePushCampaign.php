<?php

namespace App\Filament\Mine\Resources\PushCampaigns\Pages;

use App\Filament\Mine\Resources\PushCampaigns\PushCampaignResource;
use App\Models\MarketingCampaign;
use Filament\Resources\Pages\CreateRecord;

class CreatePushCampaign extends CreateRecord
{
    protected static string $resource = PushCampaignResource::class;

    protected array $scheduleData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->scheduleData = [
            'cron_expression' => $data['schedule_cron_expression'] ?? null,
            'timezone' => $data['schedule_timezone'] ?? null,
            'starts_at' => $data['schedule_starts_at'] ?? null,
            'ends_at' => $data['schedule_ends_at'] ?? null,
        ];

        unset($data['schedule_cron_expression'], $data['schedule_timezone'], $data['schedule_starts_at'], $data['schedule_ends_at']);

        $data['type'] = MarketingCampaign::TYPE_PUSH;

        return $data;
    }

    protected function afterCreate(): void
    {
        PushCampaignResource::persistSchedule($this->record, $this->scheduleData);
    }
}
