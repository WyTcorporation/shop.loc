<?php

namespace App\Filament\Mine\Resources\Tests\Pages;

use App\Filament\Mine\Resources\Tests\TestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTests extends ListRecords
{
    protected static string $resource = TestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
