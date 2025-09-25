<?php

namespace App\Filament\Mine\Resources\SoftExportLogs\Pages;

use App\Filament\Mine\Resources\SoftExportLogs\SoftExportLogResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSaftExportLogs extends ListRecords
{
    protected static string $resource = SoftExportLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(__('shop.admin.resources.saft_exports.actions.export'))
                ->icon('heroicon-o-arrow-down-on-square')
                ->url(fn () => SoftExportLogResource::getUrl('export')),
        ];
    }
}
