<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole(Role::Administrator->value)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageSettings->value);
    }

    public function view(User $user, Currency $currency): bool
    {
        return $user->hasPermissionTo(Permission::ManageSettings->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageSettings->value);
    }

    public function update(User $user, Currency $currency): bool
    {
        return $user->hasPermissionTo(Permission::ManageSettings->value);
    }

    public function delete(User $user, Currency $currency): bool
    {
        return $user->hasPermissionTo(Permission::ManageSettings->value);
    }
}
