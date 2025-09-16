<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(int $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);

        $reviews = $product->reviews()
            ->approved()
            ->with(['user:id,name'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $reviews,
            'average_rating' => $product->rating,
            'reviews_count' => $product->reviews_count,
        ]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);

        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'text' => $validated['text'] ?? null,
            'status' => Review::STATUS_PENDING,
        ]);

        return response()->json([
            'data' => $review->load('user:id,name'),
            'message' => 'Review submitted for moderation.',
        ], 201);
    }
}
