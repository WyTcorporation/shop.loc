<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Access\HandlesAuthorization;

class VendorPolicy
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

    public function view(User $user, Vendor $vendor): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return $this->canAccessVendor($user, $vendor);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageProducts->value);
    }

    public function update(User $user, Vendor $vendor): bool
    {
        if (! $user->hasPermissionTo(Permission::ManageProducts->value)) {
            return false;
        }

        return $this->canAccessVendor($user, $vendor);
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        if (! $user->hasPermissionTo(Permission::ManageProducts->value)) {
            return false;
        }

        return $this->canAccessVendor($user, $vendor);
    }

    protected function canAccessVendor(User $user, Vendor $vendor): bool
    {
        $userVendor = $user->vendor;

        if (! $userVendor) {
            return true;
        }

        return $userVendor->is($vendor);
    }
}
