<?php

namespace App\Filament\Mine\Resources\Orders\Pages;

use App\Filament\Mine\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use App\Enums\OrderStatus;
use Filament\Actions;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('markPaid')
                ->label('Mark paid')
                ->icon('heroicon-o-banknotes')
                ->visible(fn () => $this->record->status === OrderStatus::New)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->markPaid();
                    $this->record->refresh();
                    $this->data['status'] = (string) $this->record->status->value;
                    $this->data['total']  = (string) $this->record->total;
                }),

            Actions\Action::make('markShipped')
                ->label('Mark shipped')
                ->icon('heroicon-o-truck')
                ->visible(fn () => $this->record->status === OrderStatus::Paid)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->markShipped();
                    $this->record->refresh();
                    $this->data['status'] = (string) $this->record->status->value;
                }),

            Actions\Action::make('cancel')
                ->label('Cancel')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => in_array($this->record->status, [OrderStatus::New, OrderStatus::Paid], true))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->cancel();
                    $this->record->refresh();
                    $this->data['status'] = (string) $this->record->status->value;
                    $this->data['total']  = (string) $this->record->total;
                }),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->recalculateTotal();
        $this->record->refresh();
        $this->data['total'] = (string) $this->record->total;
    }

    #[On('order-items-updated')]
    public function onOrderItemsUpdated(string $total = null): void
    {
        $this->record->refresh();
        $this->data['total'] = (string) ($total ?? $this->record->total);
    }
}
