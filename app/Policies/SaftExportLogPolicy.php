<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\SaftExportLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaftExportLogPolicy
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
            Permission::ViewSaftExports->value,
            Permission::ManageSaftExports->value,
        ]) || $user->hasRole(Role::Accountant->value);
    }

    public function view(User $user, SaftExportLog $saftExportLog): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ManageSaftExports->value)
            || $user->hasRole(Role::Accountant->value);
    }
}
