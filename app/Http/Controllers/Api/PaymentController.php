<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    public function intent(Request $r): JsonResponse
    {
        $number = (string)$r->input('number');
        $order = Order::where('number', $number)->firstOrFail();

        $currency = strtolower($order->currency ?: 'EUR');
        // Stripe приймає amount у "мінімальних одиницях" (євроценти)
        $amountMinor = (int)round(((float)$order->total) * 100);

        $stripe = new StripeClient(config('services.stripe.secret'));

        if ($order->payment_intent_id) {
            $pi = $stripe->paymentIntents->retrieve($order->payment_intent_id);
            if ($pi->amount !== $amountMinor || strtolower($pi->currency) !== $currency) {
                $pi = $stripe->paymentIntents->update($pi->id, [
                    'amount' => $amountMinor,
                    'currency' => $currency,
                ]);
            }
        } else {
            $pi = $stripe->paymentIntents->create([
                'amount' => $amountMinor,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'order_number' => $order->number,
                    'order_id' => (string)$order->id,
                ],
                'receipt_email' => $order->email,
            ]);

            $order->payment_intent_id = $pi->id;
            $order->payment_status = 'pending';
            $order->save();
        }

        return response()->json([
            'clientSecret' => $pi->client_secret,
            'publishableKey' => config('services.stripe.key'),
            'order' => [
                'number' => $order->number,
                'payment_status' => $order->payment_status,
                'total' => (float)$order->total,
                'currency' => $order->currency,
            ],
        ]);
    }
}
