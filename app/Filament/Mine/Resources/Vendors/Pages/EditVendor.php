<?php

namespace App\Filament\Mine\Resources\Vendors\Pages;

use App\Filament\Mine\Resources\Vendors\Pages\Concerns\NormalizesVendorTranslations;
use App\Filament\Mine\Resources\Vendors\VendorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditVendor extends EditRecord
{
    use NormalizesVendorTranslations;

    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => ! auth()->user()?->vendor),
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
        $data = $this->normalizeVendorFormTranslations($data);

        if ($user = Auth::user()) {
            if ($user->vendor && $this->record->user_id === $user->vendor->user_id) {
                $data['user_id'] = $user->id;
            }
        }

        return $data;
    }
}
