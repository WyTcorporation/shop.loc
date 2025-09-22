<?php

namespace Tests\Feature\Mail;

use App\Mail\OrderPaidMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderEmailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_paid_mail_contains_key_information(): void
    {
        $order = Order::factory()
            ->has(OrderItem::factory()->count(2), 'items')
            ->create([
                'currency' => 'UAH',
                'total' => 1250.75,
                'paid_at' => now(),
            ]);

        $mailable = new OrderPaidMail($order->fresh());
        $html = $mailable->render();

        $this->assertStringContainsString('Оплату отримано', $html);
        $this->assertStringContainsString($order->number, $html);
        $this->assertStringContainsString('UAH', $html);
    }

    public function test_order_shipped_mail_highlights_shipment_status(): void
    {
        $order = Order::factory()->create([
            'currency' => 'UAH',
            'total' => 980.15,
            'shipped_at' => now(),
        ]);

        $mailable = new OrderShippedMail($order);
        $html = $mailable->render();

        $this->assertStringContainsString('Замовлення в дорозі', $html);
        $this->assertStringContainsString('Відправлено', $html);
        $this->assertStringContainsString($order->number, $html);
    }
}
