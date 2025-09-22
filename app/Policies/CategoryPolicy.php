<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
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
            Permission::ViewProducts->value,
            Permission::ManageProducts->value,
        ]);
    }

    public function view(User $user, Category $category): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageProducts->value);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::ManageProducts->value);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::ManageProducts->value);
    }
}
