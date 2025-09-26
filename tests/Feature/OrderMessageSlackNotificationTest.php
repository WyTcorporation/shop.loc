<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderMessageSlackNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_slack_notification_is_sent_when_order_message_is_created(): void
    {
        Http::fake();

        config([
            'services.slack.order_messages' => [
                'webhook_url' => 'https://hooks.slack.test/services/T000/B000/XXXX',
                'channel' => '#support',
                'thread_ts' => '1234567890.123456',
            ],
        ]);

        $user = User::factory()->create(['name' => 'Test Manager']);
        $order = Order::factory()->for($user)->create();

        $service = app(OrderMessageService::class);

        $message = $service->create($order, $user->getKey(), 'Привіт, Slack!');

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) use ($order, $message, $user) {
            $data = $request->data();

            return $request->url() === 'https://hooks.slack.test/services/T000/B000/XXXX'
                && ($data['channel'] ?? null) === '#support'
                && ($data['thread_ts'] ?? null) === '1234567890.123456'
                && str_contains($data['text'] ?? '', $order->number)
                && str_contains($data['blocks'][0]['text']['text'] ?? '', $message->body)
                && str_contains($data['blocks'][1]['text']['text'] ?? '', (string) $order->getKey())
                && str_contains($data['blocks'][0]['text']['text'] ?? '', $user->name);
        });
    }

    public function test_slack_notification_is_not_sent_for_admin_messages(): void
    {
        Http::fake();

        config([
            'services.slack.order_messages' => [
                'webhook_url' => 'https://hooks.slack.test/services/T000/B000/XXXX',
            ],
        ]);

        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create();

        $admin = User::factory()->create();

        $service = app(OrderMessageService::class);

        $service->create($order, $admin->getKey(), 'Адмінське повідомлення.');

        Http::assertSentCount(0);
    }
}
