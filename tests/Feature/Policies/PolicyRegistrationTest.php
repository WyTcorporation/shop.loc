<?php

namespace Tests\Feature\Policies;

use App\Enums\Permission as PermissionEnum;
use App\Models\Coupon;
use App\Models\ProductStock;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PolicyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_coupon_resource_view_any_permission(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->check('viewAny', Coupon::class));

        $this->givePermission($user, PermissionEnum::ViewMarketing->value);

        $this->assertTrue(Gate::forUser($user)->check('viewAny', Coupon::class));
    }

    public function test_inventory_resource_view_any_permission(): void
    {
        $user = User::factory()->create();

        SpatiePermission::findOrCreate(PermissionEnum::ManageInventory->value, 'web');

        $this->assertFalse(Gate::forUser($user)->check('viewAny', ProductStock::class));

        $this->givePermission($user, PermissionEnum::ManageInventory->value);

        $this->assertTrue(Gate::forUser($user)->check('viewAny', ProductStock::class));
    }

    public function test_reviews_resource_view_any_permission(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->check('viewAny', Review::class));

        $this->givePermission($user, PermissionEnum::ViewProducts->value);

        $this->assertTrue(Gate::forUser($user)->check('viewAny', Review::class));
    }

    public function test_vendors_resource_view_any_permission(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->check('viewAny', Vendor::class));

        $this->givePermission($user, PermissionEnum::ViewProducts->value);

        $this->assertTrue(Gate::forUser($user)->check('viewAny', Vendor::class));
    }

    private function givePermission(User $user, string $permission): void
    {
        SpatiePermission::findOrCreate($permission, 'web');

        $user->givePermissionTo($permission);
    }
}
