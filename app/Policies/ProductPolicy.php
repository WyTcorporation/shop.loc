<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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

    public function view(User $user, Product $product): bool
    {
        return $this->ownsProduct($user, $product);
    }

    public function create(User $user): bool
    {
        return (bool) $user->vendor;
    }

    public function update(User $user, Product $product): bool
    {
        return $this->ownsProduct($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->ownsProduct($user, $product);
    }

    protected function ownsProduct(User $user, Product $product): bool
    {
        $vendor = $user->vendor;

        if (! $vendor) {
            return true;
        }

        return (int) ($product->vendor_id ?? 0) === $vendor->id;
    }
}
