<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, int $id, string $hash): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = User::query()->find($id);

        if (! $user) {
            abort(404, __('shop.api.common.not_found'));
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, __('shop.api.verify_email.invalid_signature'));
        }

        if ($user->hasVerifiedEmail()) {
            return $this->respond($request, __('shop.api.verify_email.already_verified'), true);
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        event(new Verified($user));

        return $this->respond($request, __('shop.api.verify_email.verified'));
    }

    private function respond(Request $request, string $message, bool $alreadyVerified = false): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        $frontendUrl = rtrim(
            config('app.frontend_url', env('FRONTEND_URL', config('app.url'))),
            '/'
        );

        $path = $alreadyVerified
            ? config('app.frontend_verified_already_path', '/profile?email_verified=already')
            : config('app.frontend_verified_path', '/profile?email_verified=1');

        return redirect()->away($frontendUrl . $path);
    }
}
