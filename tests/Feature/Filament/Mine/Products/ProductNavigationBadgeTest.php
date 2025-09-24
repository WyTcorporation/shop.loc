<?php

use App\Enums\Permission as PermissionEnum;
use App\Filament\Mine\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Spatie\Permission\Models\Permission;

it('counts only products visible to the current vendor user in the navigation badge', function (): void {
    Permission::findOrCreate(PermissionEnum::ViewProducts->value, 'web');
    Permission::findOrCreate(PermissionEnum::ManageProducts->value, 'web');

    $user = User::factory()->create();
    $user->givePermissionTo([
        PermissionEnum::ViewProducts->value,
        PermissionEnum::ManageProducts->value,
    ]);

    $vendor = Vendor::factory()->for($user)->create();

    $visibleProduct = Product::factory()->for($vendor)->create();
    Product::factory()->create();

    $this->actingAs($user);

    expect(ProductResource::getNavigationBadge())->toBe('1');

    $table = $visibleProduct->getTable();

    $visibleIds = ProductResource::getEloquentQuery()->pluck("{$table}.id")->all();

    expect($visibleIds)->toBe([$visibleProduct->getKey()]);
});
