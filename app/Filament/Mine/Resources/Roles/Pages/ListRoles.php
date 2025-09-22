<?php

namespace App\Filament\Mine\Resources\Roles\Pages;

use App\Filament\Mine\Resources\Roles\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Spatie\Permission\Models\Role as SpatieRole;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()?->can('create', SpatieRole::class) ?? false),
        ];
    }
}
