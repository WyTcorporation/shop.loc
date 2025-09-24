<?php

use App\Enums\Role as RoleEnum;
use App\Filament\Mine\Resources\Vendors\Pages\CreateVendor;
use App\Filament\Mine\Resources\Vendors\Pages\EditVendor;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Config::set('app.locale', 'uk');
    Config::set('app.fallback_locale', 'en');
    Config::set('app.supported_locales', ['uk', 'en']);

    Role::findOrCreate(RoleEnum::Administrator->value, 'web');
});

function createVendorManager(): User
{
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::Administrator->value);

    return $user;
}

it('creates vendors when translations are keyed by locale', function (): void {
    $user = createVendorManager();

    $component = Livewire::actingAs($user)
        ->test(CreateVendor::class);

    $component->set('data.user_id', $user->id)
        ->set('data.slug', 'locale-vendor')
        ->set('data.contact_email', 'vendor@example.test')
        ->set('data.contact_phone', '+380950000000')
        ->set('data.name_translations.uk', 'Постачальник')
        ->set('data.name_translations.en', 'Vendor')
        ->set('data.description_translations.uk', 'Опис українською')
        ->set('data.description_translations.en', 'Description')
        ->set('data.uk.name', 'Постачальник')
        ->set('data.uk.description', 'Опис українською')
        ->set('data.en.name', 'Vendor')
        ->set('data.en.description', 'Description')
        ->call('create')
        ->assertHasNoFormErrors();

    $vendor = Vendor::first();

    expect($vendor)->not()->toBeNull();
    expect($vendor->name_translations)
        ->toMatchArray([
            'en' => 'Vendor',
            'uk' => 'Постачальник',
        ]);
    expect($vendor->description_translations)
        ->toMatchArray([
            'en' => 'Description',
            'uk' => 'Опис українською',
        ]);
    expect($vendor->name)->toBe('Постачальник');
    expect($vendor->description)->toBe('Опис українською');
});

it('updates vendors when translations are keyed by locale', function (): void {
    $user = createVendorManager();

    $vendor = Vendor::factory()
        ->for($user)
        ->create([
            'slug' => 'existing-vendor',
            'contact_email' => 'existing@example.test',
            'contact_phone' => '+380950000001',
        ]);

    $component = Livewire::actingAs($user)
        ->test(EditVendor::class, ['record' => $vendor->getKey()]);

    $component->set('data.user_id', $user->id)
        ->set('data.slug', $vendor->slug)
        ->set('data.contact_email', $vendor->contact_email)
        ->set('data.contact_phone', '+380950000002')
        ->set('data.name_translations.uk', 'Оновлений постачальник')
        ->set('data.name_translations.en', 'Updated Vendor')
        ->set('data.description_translations.uk', 'Оновлений опис')
        ->set('data.description_translations.en', 'Updated description')
        ->set('data.uk.name', 'Оновлений постачальник')
        ->set('data.uk.description', 'Оновлений опис')
        ->set('data.en.name', 'Updated Vendor')
        ->set('data.en.description', 'Updated description')
        ->call('save')
        ->assertHasNoFormErrors();

    $vendor->refresh();

    expect($vendor->name_translations)
        ->toMatchArray([
            'en' => 'Updated Vendor',
            'uk' => 'Оновлений постачальник',
        ]);
    expect($vendor->description_translations)
        ->toMatchArray([
            'en' => 'Updated description',
            'uk' => 'Оновлений опис',
        ]);
    expect($vendor->name)->toBe('Оновлений постачальник');
    expect($vendor->description)->toBe('Оновлений опис');
});
