<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Act;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::Administrator->value)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::ViewActs->value,
            Permission::ManageActs->value,
        ]) || $user->hasRole(Role::Accountant->value);
    }

    public function view(User $user, Act $act): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageActs->value)
            || $user->hasRole(Role::Accountant->value);
    }

    public function update(User $user, Act $act): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Act $act): bool
    {
        return $this->create($user);
    }
}
