<?php

namespace App\Filament\Mine\Resources\PushCampaigns\Pages;

use App\Filament\Mine\Resources\PushCampaigns\PushCampaignResource;
use App\Models\MarketingCampaign;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPushCampaign extends EditRecord
{
    protected static string $resource = PushCampaignResource::class;

    protected array $scheduleData = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $schedule = $this->record->schedule;

        $data['schedule_cron_expression'] = $schedule?->cron_expression;
        $data['schedule_timezone'] = $schedule?->timezone ?? config('app.timezone', 'UTC');
        $data['schedule_starts_at'] = $schedule?->starts_at?->toDateTimeString();
        $data['schedule_ends_at'] = $schedule?->ends_at?->toDateTimeString();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function afterSave(): void
    {
        parent::afterSave();

        PushCampaignResource::persistSchedule($this->record, $this->scheduleData);
    }
}
