<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductStockPolicy
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
        return $user->hasPermissionTo(Permission::ManageInventory->value);
    }

    public function view(User $user, ProductStock $stock): bool
    {
        return $user->hasPermissionTo(Permission::ManageInventory->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageInventory->value);
    }

    public function update(User $user, ProductStock $stock): bool
    {
        return $user->hasPermissionTo(Permission::ManageInventory->value);
    }

    public function delete(User $user, ProductStock $stock): bool
    {
        return $user->hasPermissionTo(Permission::ManageInventory->value);
    }
}
