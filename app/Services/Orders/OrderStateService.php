<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use DomainException;
use App\Services\Invoices\CreateInvoiceFromOrder;

class OrderStateService
{
    public function markPaid(Order $order): void
    {
        $currentStatus = $this->currentStatus($order);

        if ($currentStatus !== OrderStatus::New) {
            throw new DomainException(__('shop.orders.errors.only_new_can_be_marked_paid', [
                'required' => $this->localizedStatus(OrderStatus::New),
                'status' => $this->localizedStatus($currentStatus),
                'number' => $this->orderNumber($order),
            ]));
        }

        DB::transaction(function () use ($order) {
            $order->reserveInventory();
            $order->update(['status' => OrderStatus::Paid]);

            app(CreateInvoiceFromOrder::class)->handle($order->fresh());
        });
    }

    public function markShipped(Order $order): void
    {
        $currentStatus = $this->currentStatus($order);

        if ($currentStatus !== OrderStatus::Paid) {
            throw new DomainException(__('shop.orders.errors.only_paid_can_be_marked_shipped', [
                'required' => $this->localizedStatus(OrderStatus::Paid),
                'status' => $this->localizedStatus($currentStatus),
                'number' => $this->orderNumber($order),
            ]));
        }

        DB::transaction(function () use ($order) {
            $order->commitReservedInventory();
            $order->update(['status' => OrderStatus::Shipped]);
        });
    }

    public function cancel(Order $order, ?string $reason = null): void
    {
        $currentStatus = $this->currentStatus($order);

        if (! in_array($currentStatus, [OrderStatus::New, OrderStatus::Paid], true)) {
            throw new DomainException(__('shop.orders.errors.only_new_or_paid_can_be_cancelled', [
                'allowed' => $this->localizedAllowedCancellationStatuses(),
                'status' => $this->localizedStatus($currentStatus),
                'number' => $this->orderNumber($order),
            ]));
        }

        DB::transaction(function () use ($order) {
            $order->releaseInventory();

            $order->update(['status' => OrderStatus::Cancelled]);
        });
    }

    private function currentStatus(Order $order): OrderStatus
    {
        return $order->status instanceof OrderStatus
            ? $order->status
            : OrderStatus::from((string) $order->status);
    }

    private function localizedStatus(OrderStatus $status): string
    {
        return __('shop.orders.statuses.' . $status->value);
    }

    private function localizedAllowedCancellationStatuses(): string
    {
        return implode(', ', [
            $this->localizedStatus(OrderStatus::New),
            $this->localizedStatus(OrderStatus::Paid),
        ]);
    }

    private function orderNumber(Order $order): string
    {
        return (string) ($order->number ?? $order->id);
    }
}
