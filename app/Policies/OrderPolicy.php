<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
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
            Permission::ViewOrders->value,
            Permission::ManageOrders->value,
        ]);
    }

    public function view(User $user, Order $order): bool
    {
        if ($order->user_id === $user->id) {
            return true;
        }

        if (! $this->viewAny($user)) {
            return false;
        }

        return $this->managesOrder($user, $order);
    }

    public function update(User $user, Order $order): bool
    {
        if (! $user->hasPermissionTo(Permission::ManageOrders->value)) {
            return false;
        }

        return $this->managesOrder($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        if (! $user->hasPermissionTo(Permission::ManageOrders->value)) {
            return false;
        }

        return $this->managesOrder($user, $order);
    }

    public function createMessage(User $user, Order $order): bool
    {
        if ($order->user_id === $user->id) {
            return true;
        }

        if (! $user->hasPermissionTo(Permission::ManageOrders->value)) {
            return false;
        }

        return $this->managesOrder($user, $order);
    }

    protected function managesOrder(User $user, Order $order): bool
    {
        $vendor = $user->vendor;

        if (! $vendor) {
            return true;
        }

        if ($order->user_id && $order->user_id === $user->id) {
            return true;
        }

        return $order->involvesVendor($vendor->id);
    }
}
