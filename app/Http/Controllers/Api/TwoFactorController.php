<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('twoFactorSecret');
        $secret = $user->twoFactorSecret;

        return response()->json([
            'enabled' => (bool) $secret?->isConfirmed(),
            'pending' => $secret ? ! $secret->isConfirmed() : false,
            'confirmed_at' => $secret?->confirmed_at,
        ]);
    }

    public function store(Request $request, TwoFactorService $service): JsonResponse
    {
        $user = $request->user();
        $secret = $service->generateSecret();

        $user->twoFactorSecret()->updateOrCreate([], [
            'secret' => $secret,
            'confirmed_at' => null,
        ]);

        return response()->json([
            'secret' => $secret,
            'otpauth_url' => $service->makeOtpAuthUrl($user, $secret),
        ], 201);
    }

    public function confirm(Request $request, TwoFactorService $service): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user()->loadMissing('twoFactorSecret');
        $secret = $user->twoFactorSecret;

        if (! $secret) {
            throw ValidationException::withMessages([
                'code' => [__('shop.security.two_factor.not_initialized')],
            ]);
        }

        if (! $service->verify($secret->secret, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => [__('shop.security.two_factor.invalid_code')],
            ]);
        }

        $secret->forceFill([
            'confirmed_at' => Carbon::now(),
        ])->save();

        return response()->json([
            'message' => __('shop.security.two_factor.enabled'),
            'confirmed_at' => $secret->confirmed_at,
        ]);
    }

    public function destroy(Request $request): Response
    {
        $user = $request->user()->loadMissing('twoFactorSecret');
        $secret = $user->twoFactorSecret;

        if ($secret) {
            $secret->delete();
        }

        return response()->noContent();
    }
}
