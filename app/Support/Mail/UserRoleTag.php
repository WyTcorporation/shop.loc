<?php

namespace App\Support\Mail;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Str;

class UserRoleTag
{
    public static function primaryRoleSlug(User $user): string
    {
        $roleName = $user->getRoleNames()->first();

        if (is_string($roleName) && $roleName !== '') {
            return Str::of($roleName)->slug('-');
        }

        return static::default();
    }

    public static function for(?User $user): string
    {
        if ($user instanceof User) {
            return static::primaryRoleSlug($user);
        }

        return static::default();
    }

    public static function default(): string
    {
        return Role::Buyer->value;
    }
}
