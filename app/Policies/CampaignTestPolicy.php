<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\CampaignTest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignTestPolicy
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
            Permission::ViewMarketing->value,
            Permission::ManageMarketing->value,
            Permission::ViewCampaignTests->value,
            Permission::ManageCampaignTests->value,
        ]);
    }

    public function view(User $user, CampaignTest $test): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::ManageMarketing->value,
            Permission::ManageCampaignTests->value,
        ]);
    }

    public function update(User $user, CampaignTest $test): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, CampaignTest $test): bool
    {
        return $this->create($user);
    }
}

