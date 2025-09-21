<?php

namespace App\Filament\Mine\Resources\Users\Pages;

use App\Filament\Mine\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->can('update', $this->getRecord()) ?? false),
        ];
    }
}
