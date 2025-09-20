<?php

namespace App\Filament\Mine\Resources\Warehouses\Pages;

use App\Filament\Mine\Resources\Warehouses\WarehouseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWarehouse extends EditRecord
{
    protected static string $resource = WarehouseResource::class;

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

        if (array_key_exists('description_translations', $data) && blank($data['description_translations'][$primaryLocale] ?? null)) {
            $rawDescription = $this->record?->getRawOriginal('description');

            if (filled($rawDescription)) {
                $data['description_translations'][$primaryLocale] = $rawDescription;
            }
        }

        return $data;
    }
}
