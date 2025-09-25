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

    protected function mutateFormDataBeforeSave(array $data): array
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
            $data['name'] = $data['name_translations'][$primaryLocale]
                ?? $this->record?->getRawOriginal('name');
        }

        if (array_key_exists('description_translations', $data) && blank($data['description_translations'][$primaryLocale] ?? null)) {
            $fallbackDescription = collect($data['description_translations'] ?? [])
                ->first(fn ($value) => filled($value));

            if (filled($fallbackDescription)) {
                $data['description_translations'][$primaryLocale] = $fallbackDescription;
            }
        }

        if (blank($data['description'] ?? null)) {
            $data['description'] = $data['description_translations'][$primaryLocale]
                ?? $this->record?->getRawOriginal('description');
        }

        return $data;
    }
}
