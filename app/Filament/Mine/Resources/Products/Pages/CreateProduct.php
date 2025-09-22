<?php

namespace App\Filament\Mine\Resources\Products\Pages;

use App\Filament\Mine\Resources\Products\Pages\Concerns\ValidatesCategoryAccess;
use App\Filament\Mine\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProduct extends CreateRecord
{
    use ValidatesCategoryAccess;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->ensureCategoryIsPermitted($data);

        if ($vendor = Auth::user()?->vendor) {
            $data['vendor_id'] = $vendor->id;
        }

        return $data;
    }
}
