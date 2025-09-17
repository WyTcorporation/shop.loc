<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->loadMissing('twoFactorSecret');

        $verificationUrl = $this->queueEmailVerification($user);

        Mail::to($user)->queue(new WelcomeMail($user, $verificationUrl));

        $token = $user->createToken('shop')->plainTextToken;

        event(new Login('sanctum', $user, false));

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function resendEmailVerification(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            $this->queueEmailVerification($user);
        }

        return response()->json([
            'message' => 'Verification link sent.',
        ], 202);
    }

    public function login(Request $request, TwoFactorService $twoFactorService): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'otp' => ['nullable', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user->loadMissing('twoFactorSecret');

        $twoFactor = $user->twoFactorSecret;

        if ($twoFactor && $twoFactor->isConfirmed()) {
            if (empty($data['otp'])) {
                return response()->json([
                    'message' => 'Потрібен код двофакторної автентифікації.',
                    'two_factor_required' => true,
                ], 409);
            }

            if (! $twoFactorService->verify($twoFactor->secret, $data['otp'])) {
                throw ValidationException::withMessages([
                    'otp' => ['Невірний код двофакторної автентифікації.'],
                ]);
            }
        }

        $token = $user->createToken('shop')->plainTextToken;

        event(new Login('sanctum', $user, false));

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user->loadMissing('twoFactorSecret');

        return response()->json($this->formatUser($user));
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }

        if (array_key_exists('email', $data) && $data['email'] !== $user->email) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->refresh();
        $user->loadMissing('twoFactorSecret');

        return response()->json($this->formatUser($user));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->noContent();
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'two_factor_enabled' => $user->two_factor_enabled,
            'two_factor_confirmed_at' => $user->two_factor_confirmed_at,
        ];
    }

    private function queueEmailVerification(User $user): string
    {
        $verificationUrl = URL::temporarySignedRoute(
            'api.email.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        Mail::to($user)->queue(new VerifyEmailMail($user, $verificationUrl));

        return $verificationUrl;
    }
}
