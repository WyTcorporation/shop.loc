<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPointTransaction;
use Illuminate\Http\JsonResponse;

class ProfilePointsController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $transactions = $user->loyaltyPointTransactions()
            ->latest()
            ->get();

        $totalEarned = (int) $transactions->sum(fn ($transaction) => max(0, (int) $transaction->points));
        $totalSpent = (int) $transactions->sum(fn ($transaction) => $transaction->points < 0 ? abs((int) $transaction->points) : 0);

        $mappedTransactions = $transactions->map(function (LoyaltyPointTransaction $transaction) {
            $meta = $transaction->meta;

            return [
                'id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'type' => $transaction->type,
                'points' => (int) $transaction->points,
                'amount' => $transaction->amount,
                'description' => $transaction->localized_description,
                'meta' => $meta,
                'created_at' => optional($transaction->created_at)?->toIso8601String(),
                'updated_at' => optional($transaction->updated_at)?->toIso8601String(),
            ];
        });

        return response()->json([
            'balance' => $user->loyaltyPointsBalance(),
            'transactions' => $mappedTransactions->values()->all(),
            'total_earned' => $totalEarned,
            'total_spent' => $totalSpent,
        ]);
    }
}
