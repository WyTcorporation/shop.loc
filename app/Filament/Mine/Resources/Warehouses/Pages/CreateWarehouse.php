<?php

namespace App\Filament\Mine\Resources\Warehouses\Pages;

use App\Filament\Mine\Resources\Warehouses\WarehouseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $primaryLocale = config('app.locale');

        if (blank($data['name_translations'][$primaryLocale] ?? null)) {
            $fallbackName = collect($data['name_translations'] ?? [])
                ->first(fn ($value) => filled($value));

            if (filled($fallbackName)) {
                $data['name_translations'][$primaryLocale] = $fallbackName;
            }
        }

        if (blank($data['name'] ?? null)) {
            $data['name'] = $data['name_translations'][$primaryLocale] ?? null;
        }

        if (array_key_exists('description_translations', $data) && blank($data['description_translations'][$primaryLocale] ?? null)) {
            $fallbackDescription = collect($data['description_translations'] ?? [])
                ->first(fn ($value) => filled($value));

            if (filled($fallbackDescription)) {
                $data['description_translations'][$primaryLocale] = $fallbackDescription;
            }
        }

        if (blank($data['description'] ?? null)) {
            $data['description'] = $data['description_translations'][$primaryLocale] ?? null;
        }

        return $data;
    }
}
