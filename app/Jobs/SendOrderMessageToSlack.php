<?php

namespace App\Jobs;

use App\Data\Slack\SlackThreadSettings;
use App\Events\OrderMessageCreated;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SendOrderMessageToSlack
{
    public function handle(OrderMessageCreated $event): void
    {
        $settings = SlackThreadSettings::fromConfig(config('services.slack.order_messages', []));

        if ($settings === null) {
            Log::info('Skipping Slack notification for order message: webhook is not configured.');

            return;
        }

        $message = $event->message->loadMissing('order.user');
        $order = $message->order;

        if ($order === null) {
            Log::warning('Cannot send Slack notification: message order is missing.', [
                'message_id' => $message->getKey(),
            ]);

            return;
        }

        $orderNumber = $order->number ?? ('#' . $order->getKey());
        $customer = $order->user?->name
            ?? data_get($order->shipping_address, 'name')
            ?? $order->email
            ?? 'Невідомий клієнт';

        $adminUrl = $this->adminOrderUrl($order->getKey());
        $bodyPreview = Str::of($message->body)->trim()->limit(500);

        $payload = [
            'text' => "Нове повідомлення у замовленні {$orderNumber} від {$customer}",
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Нове повідомлення у замовленні {$orderNumber}*\nВід: {$customer}\n> {$bodyPreview}",
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "<{$adminUrl}|Переглянути в адмінці>",
                    ],
                ],
            ],
        ];

        $payload = $settings->applyToPayload($payload);

        try {
            $response = Http::timeout(5)
                ->asJson()
                ->post($settings->webhookUrl, $payload);

            if ($response->failed()) {
                Log::error('Slack webhook call for order message failed.', [
                    'message_id' => $message->getKey(),
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Slack webhook call for order message threw an exception.', [
                'message_id' => $message->getKey(),
                'exception' => $exception,
            ]);
        }
    }

    private function adminOrderUrl(int|string $orderKey): string
    {
        $baseUrl = rtrim((string) config('app.url', ''), '/');

        if ($baseUrl === '') {
            return url("/admin/orders/{$orderKey}");
        }

        return "{$baseUrl}/admin/orders/{$orderKey}";
    }
}
