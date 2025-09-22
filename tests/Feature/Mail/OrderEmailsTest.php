<?php

namespace Tests\Feature\Mail;

use App\Jobs\SendOrderStatusUpdate;
use App\Mail\OrderPaidMail;
use App\Mail\OrderShippedMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
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

    public function test_order_status_update_mail_respects_locale(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'email' => 'customer@example.com',
            'currency' => 'USD',
            'subtotal' => 150,
            'discount_total' => 30,
            'coupon_code' => 'SAVE20',
            'loyalty_points_used' => 100,
            'loyalty_points_value' => 5,
            'total' => 120,
        ]);

        $previousLocale = 'uk';
        app()->setLocale($previousLocale);

        app()->setLocale('en');
        $job = new SendOrderStatusUpdate($order, 'Очікується', 'Відправлено');
        app()->setLocale($previousLocale);

        $job->handle();

        Mail::assertSent(OrderStatusUpdatedMail::class, function (OrderStatusUpdatedMail $mail) use ($order) {
            $this->assertSame('en', $mail->locale);

            $subjectExpectation = Lang::get('shop.orders.status_updated.subject_line', ['number' => $order->number], 'en');
            $headingExpectation = Lang::get('shop.orders.status_updated.heading', [], 'en');
            $orderIntroExpectation = Lang::get('shop.orders.status_updated.order_intro', ['number' => $order->number], 'en');
            $totalLabelExpectation = Lang::get('shop.orders.status_updated.labels.total', [], 'en');
            $signatureExpectation = Lang::get('shop.orders.status_updated.team_signature', ['app' => config('app.name')], 'en');

            $html = $mail->render();

            $this->assertStringContainsString($headingExpectation, $html);
            $this->assertStringContainsString($orderIntroExpectation, $html);
            $this->assertStringContainsString($totalLabelExpectation, $html);
            $this->assertStringContainsString($signatureExpectation, $html);

            return str_contains($mail->subject, $subjectExpectation);
        });

        $this->assertSame($previousLocale, app()->getLocale());
    }
}
