<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Services\Orders\OrderMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderMessageController extends Controller
{
    public function __construct(private readonly OrderMessageService $orderMessageService)
    {
    }

    public function index(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user, 401);

        $this->authorize('view', $order);

        $messages = $order->messages()
            ->with('user:id,name')
            ->oldest('created_at')
            ->get()
            ->map(fn (Message $message) => [
                'id' => $message->id,
                'order_id' => $message->order_id,
                'user_id' => $message->user_id,
                'body' => $message->body,
                'meta' => $message->meta,
                'created_at' => optional($message->created_at)->toISOString(),
                'updated_at' => optional($message->updated_at)->toISOString(),
                'user' => $message->user,
                'is_author' => $message->user_id === $user->id,
            ])
            ->values();

        return response()->json([
            'data' => $messages,
        ]);
    }

    public function store(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user, 401);

        $this->authorize('createMessage', $order);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->orderMessageService
            ->create($order, $user->id, $data['body'])
            ->load('user:id,name');

        return response()->json([
            'id' => $message->id,
            'order_id' => $message->order_id,
            'user_id' => $message->user_id,
            'body' => $message->body,
            'meta' => $message->meta,
            'created_at' => optional($message->created_at)->toISOString(),
            'updated_at' => optional($message->updated_at)->toISOString(),
            'user' => $message->user,
            'is_author' => true,
        ], 201);
    }
}
