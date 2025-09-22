<?php

use App\Enums\Permission as PermissionEnum;
use App\Filament\Mine\Resources\Inventory\Pages\CreateInventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\UniqueConstraintViolationException;
use Livewire\Livewire;

it('validates unique product and warehouse combination when creating inventory', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $warehouse = Warehouse::getDefault();

    Permission::findOrCreate(PermissionEnum::ManageInventory->value, 'web');
    $user->givePermissionTo(PermissionEnum::ManageInventory->value);
    $this->actingAs($user);

    $component = Livewire::test(CreateInventory::class)
        ->fillForm([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
            'qty' => 5,
            'reserved' => 0,
        ]);

    expect(fn () => $component->call('create'))
        ->not->toThrow(UniqueConstraintViolationException::class);

    $component->assertHasErrors(['data.product_id']);

    expect($product->stocks()->count())->toBe(1);
});
