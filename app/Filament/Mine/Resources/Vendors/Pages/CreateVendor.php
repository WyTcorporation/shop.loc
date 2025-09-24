<?php

namespace App\Filament\Mine\Resources\Vendors\Pages;

use App\Filament\Mine\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateVendor extends CreateRecord
{
    protected static string $resource = VendorResource::class;

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

        $user = Auth::user();

        if ($user?->vendor) {
            $data['user_id'] = $user->id;
        } elseif (! array_key_exists('user_id', $data) || ! $data['user_id']) {
            $data['user_id'] = $user?->id;
        }

        return $data;
    }
}
