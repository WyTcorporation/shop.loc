<?php

namespace App\Filament\Mine\Resources\Products\Pages;

use App\Filament\Mine\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('import')
                ->label(__('shop.admin.resources.products.imports.tabs.form'))
                ->icon('heroicon-o-arrow-up-tray')
                ->url(ProductResource::getUrl('import')),
            Action::make('export')
                ->label(__('shop.admin.resources.products.exports.tabs.form'))
                ->icon('heroicon-o-arrow-down-tray')
                ->url(ProductResource::getUrl('export')),
        ];
    }
}
