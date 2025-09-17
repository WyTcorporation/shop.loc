<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
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
        return false;
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return false;
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return false;
    }
}
