<?php

use App\Enums\Permission as PermissionEnum;
use App\Filament\Mine\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\ValidationException;
use Spatie\Invade\Invader;

it('restricts product categories to the permitted set for catalog managers', function (): void {
    Permission::findOrCreate(PermissionEnum::ManageProducts->value, 'web');

    $permittedCategory = Category::factory()->create();
    $restrictedCategory = Category::factory()->create();
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ManageProducts->value);
    $user->categories()->attach($permittedCategory);

    $this->actingAs($user);

    if (! file_exists(base_path('.env'))) {
        file_put_contents(base_path('.env'), '');
    }

    $component = Livewire::test(CreateProduct::class);

    $options = $component->instance()->form->getComponent('category_id')->getOptions();

    expect($options)->toHaveKey($permittedCategory->getKey());
    expect($options)->not->toHaveKey($restrictedCategory->getKey());

    (new Invader($component->instance()))->ensureCategoryIsPermitted([
        'category_id' => (string) $permittedCategory->getKey(),
    ]);

    $restrictedComponent = Livewire::test(CreateProduct::class);

    expect(fn () => (new Invader($restrictedComponent->instance()))->ensureCategoryIsPermitted([
        'category_id' => (string) $restrictedCategory->getKey(),
    ]))->toThrow(ValidationException::class);
});
