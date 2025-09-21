<?php

namespace App\Filament\Mine\Resources\Segments\Pages;

use App\Filament\Mine\Resources\Segments\SegmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSegments extends ListRecords
{
    protected static string $resource = SegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
