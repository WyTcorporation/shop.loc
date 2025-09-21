<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
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
            Permission::ViewInvoices->value,
            Permission::ManageInvoices->value,
        ]) || $user->hasRole(Role::Accountant->value);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageInvoices->value)
            || $user->hasRole(Role::Accountant->value);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->create($user);
    }
}
