<?php

namespace App\Filament\Mine\Resources\Products\Pages;

use App\Filament\Mine\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($vendor = Auth::user()?->vendor) {
            $data['vendor_id'] = $this->record->vendor_id ?? $vendor->id;
        }

        return $data;
    }
}
