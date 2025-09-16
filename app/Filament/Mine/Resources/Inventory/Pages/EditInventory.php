<?php

namespace App\Filament\Mine\Resources\Inventory\Pages;

use App\Filament\Mine\Resources\Inventory\InventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
