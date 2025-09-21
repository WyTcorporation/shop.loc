<?php

namespace App\Services\Documents;

use App\Models\Order;
use App\Models\SaftExportLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SaftExporter
{
    public function export(array $filters, string $format): SaftExportLog
    {
        $log = SaftExportLog::create([
            'user_id' => Auth::id(),
            'format' => strtolower($format),
            'status' => 'processing',
            'filters' => $filters,
        ]);

        try {
            $orders = $this->resolveOrders($filters);

            $payload = [
                'generated_at' => now()->toIso8601String(),
                'format' => $format,
                'filters' => $filters,
                'orders' => $orders->map(fn (Order $order) => [
                    'id' => $order->id,
                    'number' => $order->number,
                    'status' => $order->status,
                    'total' => (string) $order->total,
                    'currency' => $order->currency,
                    'customer' => [
                        'name' => $order->user?->name,
                        'email' => $order->email,
                    ],
                    'items' => $order->items->map(fn ($item) => [
                        'sku' => $item->sku,
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'price' => (string) $item->price,
                        'total' => (string) $item->total,
                    ])->all(),
                ])->all(),
            ];

            $content = DocumentExporter::toString($payload, $format);
            $path = sprintf('exports/saft-%s.%s', now()->format('YmdHis'), strtolower($format));

            Storage::disk('local')->put($path, $content);

            $log->update([
                'status' => 'completed',
                'file_path' => $path,
                'exported_at' => now(),
                'message' => trans_choice('shop.admin.resources.saft_exports.messages.completed', $orders->count(), ['count' => $orders->count()]),
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $log;
    }

    protected function resolveOrders(array $filters)
    {
        $query = Order::query()->with(['user', 'items']);

        if (! empty($filters['order_id'])) {
            $query->whereKey($filters['order_id']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->get();
    }
}
