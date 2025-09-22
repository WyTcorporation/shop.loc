<?php

namespace App\Filament\Mine\Resources\Products\Pages;

use App\Filament\Mine\Resources\Products\Pages\Concerns\ValidatesCategoryAccess;
use App\Filament\Mine\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProduct extends EditRecord
{
    use ValidatesCategoryAccess;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $primaryLocale = config('app.locale');

        if (blank($data['name_translations'][$primaryLocale] ?? null)) {
            $rawName = $this->record?->getRawOriginal('name');

            if (filled($rawName)) {
                $data['name_translations'][$primaryLocale] = $rawName;
            }
        }

        if (blank($data['description_translations'][$primaryLocale] ?? null)) {
            $rawDescription = $this->record?->getRawOriginal('description');

            if (filled($rawDescription)) {
                $data['description_translations'][$primaryLocale] = $rawDescription;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->ensureCategoryIsPermitted($data);

        if ($vendor = Auth::user()?->vendor) {
            $data['vendor_id'] = $this->record->vendor_id ?? $vendor->id;
        }

        return $data;
    }
}
