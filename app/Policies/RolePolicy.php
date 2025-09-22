<?php

namespace App\Policies;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole(RoleEnum::Administrator->value)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::ManageUsers->value);
    }

    public function view(User $user, Role $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Role $model): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Role $model): bool
    {
        return $this->viewAny($user);
    }
}
