<?php

use App\Enums\Permission as PermissionEnum;
use App\Filament\Mine\Resources\Categories\CategoryResource;
use App\Models\User;
use Spatie\Permission\Models\Permission;

$basePath = dirname(__DIR__, 5);
$envPath = $basePath.'/.env';

if (! file_exists($envPath)) {
    file_put_contents($envPath, '');
}

it('registers navigation for catalog viewers', function (): void {
    Permission::findOrCreate(PermissionEnum::ViewProducts->value, 'web');

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ViewProducts->value);

    $this->actingAs($user);

    expect(CategoryResource::shouldRegisterNavigation())->toBeTrue();
});

it('registers navigation for catalog managers', function (): void {
    Permission::findOrCreate(PermissionEnum::ManageProducts->value, 'web');

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ManageProducts->value);

    $this->actingAs($user);

    expect(CategoryResource::shouldRegisterNavigation())->toBeTrue();
});

it('does not register navigation without catalog permissions', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect(CategoryResource::shouldRegisterNavigation())->toBeFalse();
});
