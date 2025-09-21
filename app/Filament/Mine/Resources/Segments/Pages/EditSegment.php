<?php

namespace App\Filament\Mine\Resources\Segments\Pages;

use App\Filament\Mine\Resources\Segments\SegmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSegment extends EditRecord
{
    protected static string $resource = SegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
