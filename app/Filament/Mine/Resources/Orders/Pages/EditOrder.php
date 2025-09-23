<?php

namespace App\Filament\Mine\Resources\Orders\Pages;

use App\Enums\ShipmentStatus;
use App\Filament\Mine\Resources\Orders\OrderResource;
use App\Models\Address;
use App\Models\Shipment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use App\Enums\OrderStatus;
use Filament\Actions;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected array $shipmentFormData = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->shipmentFormData = $this->extractShipmentFormData($data);

        $payload = $this->normalizeAddressPayload($data['shipping_address'] ?? []);
        $addressId = $this->record->shipping_address_id;

        if ($addressId && $this->record->shippingAddress) {
            $address = $this->record->shippingAddress;
            $address->fill($payload);
            if (array_key_exists('user_id', $data)) {
                $address->user_id = $data['user_id'];
            }
            $address->save();
            $addressId = $address->id;
        } elseif (empty(array_filter($payload, fn ($value) => !is_null($value) && $value !== ''))) {
            $addressId = null;
        } else {
            $address = Address::create(array_merge([
                'user_id' => $data['user_id'] ?? null,
            ], $payload));
            $addressId = $address->id;
        }

        $data['shipping_address_id'] = $addressId;
        $data['shipping_address'] = $payload;

        return $data;
    }

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
        $this->record->shipment()->updateOrCreate([], $this->buildShipmentPayload($this->record->shipment));
        $this->record->refresh();
        $this->data['total'] = (string) $this->record->total;
    }

    #[On('order-items-updated')]
    public function onOrderItemsUpdated(string $total = null): void
    {
        $this->record->refresh();
        $this->record->loadMissing('items');
        $this->data['total'] = (string) ($total ?? $this->record->total);
        $this->dispatch('$refresh');
    }

    protected function extractShipmentFormData(array &$data): array
    {
        $shipment = [
            'tracking_number' => $data['shipment_tracking_number'] ?? null,
            'status' => $data['shipment_status'] ?? null,
        ];

        unset($data['shipment_tracking_number'], $data['shipment_status']);

        return $shipment;
    }

    protected function normalizeAddressPayload(array $address): array
    {
        return [
            'name' => $address['name'] ?? null,
            'city' => $address['city'] ?? null,
            'addr' => $address['addr'] ?? null,
            'postal_code' => $address['postal_code'] ?? null,
            'phone' => $address['phone'] ?? null,
        ];
    }

    protected function buildShipmentPayload(?Shipment $shipment = null): array
    {
        $shipment ??= $this->record->shipment;
        $statusValue = $this->shipmentFormData['status']
            ?? ($shipment?->status instanceof ShipmentStatus ? $shipment->status->value : ($shipment?->status ?? ShipmentStatus::Pending->value));

        $status = $statusValue instanceof ShipmentStatus
            ? $statusValue
            : ShipmentStatus::from($statusValue);

        $payload = [
            'address_id' => $this->record->shipping_address_id,
            'tracking_number' => $this->shipmentFormData['tracking_number'] ?? $shipment?->tracking_number,
            'status' => $status,
        ];

        $shippedAt = $shipment?->shipped_at;
        $deliveredAt = $shipment?->delivered_at;

        return match ($status) {
            ShipmentStatus::Shipped => $payload + [
                'shipped_at' => $shippedAt ?? now(),
                'delivered_at' => null,
            ],
            ShipmentStatus::Delivered => $payload + [
                'shipped_at' => $shippedAt ?? now(),
                'delivered_at' => $deliveredAt ?? now(),
            ],
            default => $payload + [
                'shipped_at' => null,
                'delivered_at' => null,
            ],
        };
    }
}
