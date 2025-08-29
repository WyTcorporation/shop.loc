<?php

namespace App\Filament\Mine\Resources\Orders\Pages;

use App\Filament\Mine\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['total']  = $data['total'] ?? 0;
        $data['number'] = $data['number'] ?? app(Order::class)->makeOrderNumber();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        $this->record->recalculateTotal();
    }
}
