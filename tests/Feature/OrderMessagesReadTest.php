<?php

use App\Enums\Permission;
use App\Filament\Mine\Resources\Orders\Pages\OrderMessages;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission as PermissionModel;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    PermissionModel::findOrCreate(Permission::ManageOrders->value, 'web');
});

it('creates messages with read_at set to null', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $service = app(OrderMessageService::class);
    $message = $service->create($order, $user->getKey(), 'Привіт');

    expect($message->read_at)->toBeNull()
        ->and($message->read_by)->toBeNull();
});

it('marks customer messages as read when manager opens the conversation', function () {
    $customer = User::factory()->create();
    $manager = User::factory()->create();
    $manager->givePermissionTo(Permission::ManageOrders->value);
    $order = Order::factory()->for($customer)->create();

    $message = Message::factory()
        ->for($order)
        ->for($customer)
        ->create(['read_at' => null, 'read_by' => null]);

    Auth::login($manager);

    $page = app(OrderMessages::class);
    $page->mount($order->getKey());

    $message->refresh();

    expect($message->read_at)->not->toBeNull()
        ->and($message->read_by)->toBe($manager->getKey());
});

it('marks staff responses as read when the customer fetches messages', function () {
    $customer = User::factory()->create();
    $manager = User::factory()->create();
    $order = Order::factory()->for($customer)->create();

    $message = Message::factory()
        ->for($order)
        ->for($manager)
        ->create(['read_at' => null, 'read_by' => null]);

    $this->actingAs($customer, 'sanctum');

    $response = $this->getJson("/api/orders/{$order->getKey()}/messages")
        ->assertOk()
        ->json('data');

    expect($response)->toHaveCount(1)
        ->and($response[0]['is_read'])->toBeTrue();

    $message->refresh();

    expect($message->read_at)->not->toBeNull()
        ->and($message->read_by)->toBe($customer->getKey());
});
