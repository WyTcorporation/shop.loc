<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
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

    public function view(User $user, Review $review): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return $this->canAccessReview($user, $review);
    }

    public function update(User $user, Review $review): bool
    {
        if (! $user->hasPermissionTo(Permission::ManageProducts->value)) {
            return false;
        }

        return $this->canAccessReview($user, $review);
    }

    public function delete(User $user, Review $review): bool
    {
        if (! $user->hasPermissionTo(Permission::ManageProducts->value)) {
            return false;
        }

        return $this->canAccessReview($user, $review);
    }

    protected function canAccessReview(User $user, Review $review): bool
    {
        $vendor = $user->vendor;

        if (! $vendor) {
            return true;
        }

        $product = $review->product;

        if ($product === null) {
            return false;
        }

        return (int) ($product->vendor_id ?? 0) === $vendor->id;
    }
}
