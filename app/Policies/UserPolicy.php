<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        return $user->hasAnyPermission([
            Permission::ViewUsers->value,
            Permission::ManageUsers->value,
        ]);
    }

    public function view(User $user, User $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageUsers->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo(Permission::ManageUsers->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo(Permission::ManageUsers->value);
    }
}
