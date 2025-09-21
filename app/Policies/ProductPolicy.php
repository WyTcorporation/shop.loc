<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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

    public function view(User $user, Product $product): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return $this->canAccessProduct($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageProducts->value);
    }

    public function update(User $user, Product $product): bool
    {
        if (! $user->hasPermissionTo(Permission::ManageProducts->value)) {
            return false;
        }

        return $this->canAccessProduct($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        if (! $user->hasPermissionTo(Permission::ManageProducts->value)) {
            return false;
        }

        return $this->canAccessProduct($user, $product);
    }

    protected function canAccessProduct(User $user, Product $product): bool
    {
        $vendor = $user->vendor;

        if ($vendor && (int) ($product->vendor_id ?? 0) !== $vendor->id) {
            return false;
        }

        $permittedCategoryIds = $user->permittedCategoryIds();

        if ($permittedCategoryIds->isEmpty()) {
            return true;
        }

        $categoryId = $product->category_id;

        if ($categoryId === null) {
            return false;
        }

        return $permittedCategoryIds->contains((int) $categoryId);
    }
}
