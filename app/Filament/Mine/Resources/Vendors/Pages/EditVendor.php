<?php

namespace App\Filament\Mine\Resources\Vendors\Pages;

use App\Filament\Mine\Resources\Vendors\VendorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => ! auth()->user()?->vendor),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($user = Auth::user()) {
            if ($user->vendor && $this->record->user_id === $user->vendor->user_id) {
                $data['user_id'] = $user->id;
            }
        }

        return $data;
    }
}
