<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\CampaignTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignTemplatePolicy
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
            Permission::ViewCampaignTemplates->value,
            Permission::ManageCampaignTemplates->value,
        ]);
    }

    public function view(User $user, CampaignTemplate $template): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::ManageMarketing->value,
            Permission::ManageCampaignTemplates->value,
        ]);
    }

    public function update(User $user, CampaignTemplate $template): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, CampaignTemplate $template): bool
    {
        return $this->create($user);
    }
}

