<?php

namespace App\Filament\Mine\Resources\DeliveryNotes\Pages;

use App\Filament\Mine\Resources\DeliveryNotes\DeliveryNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryNotes extends ListRecords
{
    protected static string $resource = DeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn () => auth()->user()?->can('create', DeliveryNoteResource::getModel())),
        ];
    }
}
