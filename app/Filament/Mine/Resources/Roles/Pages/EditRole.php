<?php

namespace App\Filament\Mine\Resources\Roles\Pages;

use App\Filament\Mine\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => auth()->user()?->can('delete', $this->record) ?? false),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['guard_name'] = $this->record->guard_name ?? 'web';

        return $data;
    }
}
