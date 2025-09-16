<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): bool|null
    {
        if (! $user->vendor) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $this->managesOrder($user, $order);
    }

    public function update(User $user, Order $order): bool
    {
        return $this->managesOrder($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $this->managesOrder($user, $order);
    }

    public function createMessage(User $user, Order $order): bool
    {
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
