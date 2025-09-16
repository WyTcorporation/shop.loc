<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderMessageController extends Controller
{
    public function store(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user, 401);

        $this->authorize('createMessage', $order);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $order->messages()->create([
            'user_id' => $user->id,
            'body' => $data['body'],
        ])->load('user:id,name');

        return response()->json([
            'id' => $message->id,
            'order_id' => $message->order_id,
            'user_id' => $message->user_id,
            'body' => $message->body,
            'meta' => $message->meta,
            'created_at' => optional($message->created_at)->toISOString(),
            'updated_at' => optional($message->updated_at)->toISOString(),
            'user' => $message->user,
        ], 201);
    }
}
