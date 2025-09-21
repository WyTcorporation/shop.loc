<?php

namespace App\Filament\Mine\Resources\SaftExportLogs\Pages;

use App\Filament\Mine\Resources\SaftExportLogs\SaftExportLogResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSaftExportLogs extends ListRecords
{
    protected static string $resource = SaftExportLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(__('shop.admin.resources.saft_exports.actions.export'))
                ->icon('heroicon-o-arrow-down-on-square')
                ->url(fn () => SaftExportLogResource::getUrl('export')),
        ];
    }
}
