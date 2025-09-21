<?php

namespace App\Filament\Mine\Resources\PushCampaigns\Pages;

use App\Filament\Mine\Resources\PushCampaigns\PushCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPushCampaigns extends ListRecords
{
    protected static string $resource = PushCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
