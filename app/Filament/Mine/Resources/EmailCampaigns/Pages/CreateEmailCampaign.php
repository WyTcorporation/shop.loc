<?php

namespace App\Filament\Mine\Resources\EmailCampaigns\Pages;

use App\Filament\Mine\Resources\EmailCampaigns\EmailCampaignResource;
use App\Models\MarketingCampaign;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailCampaign extends CreateRecord
{
    protected static string $resource = EmailCampaignResource::class;

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

        $data['type'] = MarketingCampaign::TYPE_EMAIL;

        return $data;
    }

    protected function afterCreate(): void
    {
        EmailCampaignResource::persistSchedule($this->record, $this->scheduleData);
    }
}
