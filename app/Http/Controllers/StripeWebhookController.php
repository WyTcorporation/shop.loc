<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload    = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature');
        $secret     = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature error: '.$e->getMessage());
            return response()->json(['error' => __('shop.api.payments.invalid_signature')], 400);
        }

        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                case 'payment_intent.processing':
                case 'payment_intent.payment_failed':
                case 'payment_intent.canceled':
                    $pi = $event->data->object;

                    $order = Order::where('payment_intent_id', $pi->id)->first();
                    if (!$order) {
                        $orderNumber = $pi->metadata->order_number ?? null;
                        if ($orderNumber) {
                            $order = Order::where('number', $orderNumber)->first();
                        }
                    }
                    if (!$order) break;

                    $stripeStatus = (string) $pi->status;
                    $map = [
                        'succeeded'               => OrderStatus::Paid,
                        'processing'              => OrderStatus::New,
                        'requires_payment_method' => OrderStatus::New,
                        'canceled'                => OrderStatus::Cancelled,
                    ];
                    $next = $map[$stripeStatus] ?? null;

                    $order->payment_status    = $stripeStatus;
                    $order->payment_intent_id = $pi->id;

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
                    break;

                default:
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handling error: '.$e->getMessage(), ['event' => $event->type ?? 'unknown']);
        }

        return response()->json(['ok' => true]);
    }
}
