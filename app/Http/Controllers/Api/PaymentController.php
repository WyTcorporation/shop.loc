<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    // Створення/оновлення PaymentIntent (Stripe Elements)
    public function intent(Request $r): JsonResponse
    {
        $number = (string) $r->input('number');
        $order  = Order::where('number', $number)->firstOrFail();

        $currency    = strtolower($order->currency ?: 'EUR');
        $amountMinor = (int) round(((float) $order->total) * 100);

        $stripe = new StripeClient(config('services.stripe.secret'));

        if ($order->payment_intent_id) {
            $pi = $stripe->paymentIntents->retrieve($order->payment_intent_id);
            if ((int) $pi->amount !== $amountMinor || strtolower($pi->currency) !== $currency) {
                $pi = $stripe->paymentIntents->update($pi->id, [
                    'amount'   => $amountMinor,
                    'currency' => $currency,
                ]);
            }
        } else {
            $pi = $stripe->paymentIntents->create([
                'amount'   => $amountMinor,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_number' => $order->number,
                    'order_id'     => (string) $order->id,
                ],
                'receipt_email' => $order->email,
            ]);

            $order->payment_intent_id = $pi->id;
            // НЕ чіпаємо $order->status — залишаємо new (enum)
            $order->payment_status = (string) ($pi->status ?? 'requires_payment_method');
            $order->save();
        }

        return response()->json([
            'clientSecret'   => $pi->client_secret,
            'publishableKey' => config('services.stripe.key'),
            'order' => [
                'number'         => $order->number,
                'payment_status' => $order->payment_status,
                'total'          => (float) $order->total,
                'currency'       => $order->currency,
            ],
        ]);
    }

    // Ручне оновлення статусу після редіректу/3DS
    public function refreshStatus(Request $r, string $number): JsonResponse
    {
        $order = Order::where('number', $number)->firstOrFail();
        $piId  = (string) ($r->input('payment_intent') ?: $order->payment_intent_id);

        if (!$piId) {
            return response()->json(['ok' => false, 'error' => __('shop.api.payments.missing_intent')], 400);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $pi     = $stripe->paymentIntents->retrieve($piId);

        $stripeStatus = (string) $pi->status; // succeeded|processing|requires_payment_method|canceled
        $map = [
            'succeeded'               => OrderStatus::Paid,
            'processing'              => OrderStatus::New,
            'requires_payment_method' => OrderStatus::New,
            'canceled'                => OrderStatus::Cancelled, // Stripe: one L, enum: two L
        ];
        $next = $map[$stripeStatus] ?? null;

        // зберігаємо raw-статус Stripe
        $order->payment_status    = $stripeStatus;
        $order->payment_intent_id = $pi->id;

        // доменний статус міняємо тільки в межах enum і без даунгрейдів
        if ($next instanceof OrderStatus) {
            if ($order->status !== OrderStatus::Paid && $order->status !== OrderStatus::Shipped) {
                if ($order->status !== $next) {
                    $order->status = $next;
                    if ($next === OrderStatus::Paid) {
                        $order->paid_at = now();
                    }
                }
            }
        }

        $order->save();

        return response()->json([
            'ok'             => true,
            'status'         => $order->status->value,
            'payment_status' => $order->payment_status,
        ]);
    }
}
