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
        $user = Auth::user();

        if ($user?->vendor) {
            $data['user_id'] = $user->id;
        } elseif (! array_key_exists('user_id', $data) || ! $data['user_id']) {
            $data['user_id'] = $user?->id;
        }

        return $data;
    }
}
