<?php


namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Throwable $e) {
            return response('Invalid', 400);
        }

        $object = $event->data->object ?? null;

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $piId = $object->id ?? null;
                if ($piId) {
                    $order = Order::where('payment_intent_id', $piId)->first()
                        ?: Order::where('number', $object->metadata->order_number ?? '')->first();
                    if ($order) {
                        $order->payment_status = 'succeeded';
                        $order->paid_at = now();
                        $order->save();
                    }
                }
                break;

            case 'payment_intent.payment_failed':
                if (!empty($object->id)) {
                    $order = Order::where('payment_intent_id', $object->id)->first();
                    if ($order) {
                        $order->payment_status = 'failed';
                        $order->save();
                    }
                }
                break;

            case 'payment_intent.canceled':
                if (!empty($object->id)) {
                    $order = Order::where('payment_intent_id', $object->id)->first();
                    if ($order) {
                        $order->payment_status = 'canceled';
                        $order->save();
                    }
                }
                break;
        }

        return response('ok', 200);
    }
}
