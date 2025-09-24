<?php

namespace App\Filament\Mine\Resources\Orders\Pages;

use App\Enums\ShipmentStatus;
use App\Filament\Mine\Resources\Orders\OrderResource;
use App\Models\Address;
use App\Models\Order;
use App\Support\Phone;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected array $shipmentFormData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->shipmentFormData = $this->extractShipmentFormData($data);

        $data['shipping_address_id'] = $this->createShippingAddress($data);
        $data['shipping_address'] = $this->normalizeAddressPayload($data['shipping_address'] ?? []);
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

        $this->record->shipment()->updateOrCreate([], $this->buildShipmentPayload());
    }

    protected function extractShipmentFormData(array &$data): array
    {
        $shipment = [
            'tracking_number' => $data['shipment_tracking_number'] ?? null,
            'status' => $data['shipment_status'] ?? ShipmentStatus::Pending->value,
            'delivery_method' => $data['shipment_delivery_method'] ?? null,
        ];

        unset($data['shipment_tracking_number'], $data['shipment_status'], $data['shipment_delivery_method']);

        return $shipment;
    }

    protected function createShippingAddress(array $data): ?int
    {
        $payload = $this->normalizeAddressPayload($data['shipping_address'] ?? []);

        if (empty(array_filter($payload, fn ($value) => !is_null($value) && $value !== ''))) {
            return null;
        }

        $address = Address::create(array_merge([
            'user_id' => $data['user_id'] ?? null,
        ], $payload));

        return $address->id;
    }

    protected function normalizeAddressPayload(array $address): array
    {
        return [
            'name' => $address['name'] ?? null,
            'city' => $address['city'] ?? null,
            'addr' => $address['addr'] ?? null,
            'postal_code' => $address['postal_code'] ?? null,
            'phone' => Phone::normalize($address['phone'] ?? null),
        ];
    }

    protected function buildShipmentPayload(): array
    {
        $statusValue = $this->shipmentFormData['status'] ?? ShipmentStatus::Pending->value;
        $status = $statusValue instanceof ShipmentStatus
            ? $statusValue
            : ShipmentStatus::from($statusValue);

        $payload = [
            'address_id' => $this->record->shipping_address_id,
            'tracking_number' => $this->shipmentFormData['tracking_number'] ?? null,
            'delivery_method' => $this->shipmentFormData['delivery_method'] ?? null,
            'status' => $status,
        ];

        return match ($status) {
            ShipmentStatus::Shipped => $payload + [
                'shipped_at' => now(),
                'delivered_at' => null,
            ],
            ShipmentStatus::Delivered => $payload + [
                'shipped_at' => now(),
                'delivered_at' => now(),
            ],
            default => $payload + [
                'shipped_at' => null,
                'delivered_at' => null,
            ],
        };
    }
}
