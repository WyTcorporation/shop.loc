<?php

namespace App\Filament\Mine\Resources\Permissions\Pages;

use App\Filament\Mine\Resources\Permissions\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Spatie\Permission\Models\Permission as SpatiePermission;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()?->can('create', SpatiePermission::class) ?? false),
        ];
    }
}
