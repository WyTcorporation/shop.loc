<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        return response()->json([
            'balance' => $user->loyaltyPointsBalance(),
            'transactions' => $transactions,
            'total_earned' => $totalEarned,
            'total_spent' => $totalSpent,
        ]);
    }
}
