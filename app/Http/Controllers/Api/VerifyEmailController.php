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
            abort(404);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Недійсний підпис для підтвердження електронної адреси.');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->respond($request, 'Електронна адреса вже підтверджена.', true);
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        event(new Verified($user));

        return $this->respond($request, 'Електронну адресу підтверджено.');
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

        $path = $alreadyVerified ? '/email-verified?verified=already' : '/email-verified?verified=1';

        return redirect()->away($frontendUrl . $path);
    }
}
